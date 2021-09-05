<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Classes\Dwarf;

use Battle\Action\ActionCollection;
use Battle\Action\HealAction;
use Battle\Command\CommandException;
use Battle\Command\CommandFactory;
use Battle\Command\CommandInterface;
use Battle\Unit\Ability\Effect\HealingPotionAbility;
use Battle\Unit\Classes\UnitClassInterface;
use Battle\Unit\Effect\Effect;
use Battle\Unit\Effect\EffectCollection;
use Battle\Unit\UnitException;
use Battle\Unit\UnitInterface;
use Exception;
use PHPUnit\Framework\TestCase;
use Tests\Battle\Factory\UnitFactory;

class AlchemistTest extends TestCase
{
    /**
     * @throws CommandException
     * @throws UnitException
     * @throws Exception
     */
    public function testCreateAlchemistClass(): void
    {
        $unit = UnitFactory::createByTemplate(22);
        $enemyUnit = UnitFactory::createByTemplate(1);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $alchemist = $unit->getClass();

        self::assertEquals(UnitClassInterface::ALCHEMIST_ID, $alchemist->getId());
        self::assertEquals(UnitClassInterface::ALCHEMIST_NAME, $alchemist->getName());
        self::assertEquals(UnitClassInterface::ALCHEMIST_SMALL_ICON, $alchemist->getSmallIcon());

        $abilities = $alchemist->getAbilities($unit);

        foreach ($abilities as $ability) {
            self::assertContainsOnlyInstancesOf(HealingPotionAbility::class, [$ability]);

            $actions = $ability->getAction($enemyCommand, $command);

            foreach ($actions as $action) {
                self::assertEquals(
                    $this->createEffects($unit, $enemyCommand, $command),
                    $action->getEffects()
                );
            }
        }
    }

    /**
     * @param UnitInterface $unit
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $command
     * @return EffectCollection
     */
    private function createEffects(UnitInterface $unit, CommandInterface $enemyCommand, CommandInterface $command): EffectCollection
    {
        $onNextRoundActions = new ActionCollection();

        $onNextRoundActions->add(new HealAction(
            $unit,
            $enemyCommand,
            $command,
            HealAction::TARGET_SELF,
            15,
            null,
            HealAction::EFFECT_ANIMATION_METHOD
        ));

        // Создаем коллекцию эффектов, с одним эффектом при применении - Reserve Forces
        $effects = new EffectCollection();

        // Создаем сам эффект
        $effects->add(new Effect(
            'Healing Potion',
            '/images/icons/ability/234.png',
            4,
            new ActionCollection(),
            $onNextRoundActions,
            new ActionCollection()
        ));

        return $effects;
    }
}
