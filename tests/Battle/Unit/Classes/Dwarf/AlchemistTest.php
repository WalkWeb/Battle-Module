<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Classes\Dwarf;

use Battle\Action\ActionFactory;
use Battle\Action\ActionInterface;
use Battle\Action\HealAction;
use Battle\Command\CommandFactory;
use Battle\Command\CommandInterface;
use Battle\Unit\Ability\Effect\HealingPotionAbility;
use Battle\Unit\Effect\EffectCollection;
use Battle\Unit\Effect\EffectFactory;
use Battle\Unit\UnitInterface;
use Exception;
use PHPUnit\Framework\TestCase;
use Tests\Battle\Factory\UnitFactory;

class AlchemistTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testAlchemistCreate(): void
    {
        $unit = UnitFactory::createByTemplate(22);
        $enemyUnit = UnitFactory::createByTemplate(1);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $alchemist = $unit->getClass();

        self::assertEquals(6, $alchemist->getId());
        self::assertEquals('Alchemist', $alchemist->getName());
        self::assertEquals('/images/icons/small/alchemist.png', $alchemist->getSmallIcon());

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
     * @throws Exception
     */
    private function createEffects(UnitInterface $unit, CommandInterface $enemyCommand, CommandInterface $command): EffectCollection
    {
        $effects = new EffectCollection($unit);
        $effectFactory = new EffectFactory(new ActionFactory());

        $data = [
            'name'                  => 'Healing Potion',
            'icon'                  =>'/images/icons/ability/234.png',
            'duration'              => 4,
            'on_apply_actions'      => [],
            'on_next_round_actions' => [
                [
                    'type'            => ActionInterface::HEAL,
                    'action_unit'     => $unit,
                    'enemy_command'   => $enemyCommand,
                    'allies_command'  => $command,
                    'type_target'     => ActionInterface::TARGET_SELF,
                    'name'            => null,
                    'power'           => 15,
                    'animation_method' => HealAction::EFFECT_ANIMATION_METHOD,
                ],
            ],
            'on_disable_actions'    => [],
        ];

        $effects->add($effectFactory->create($data));

        return $effects;
    }
}
