<?php

declare(strict_types=1);

namespace Tests\Battle\Classes\Orcs;

use Battle\Action\ActionCollection;
use Battle\Action\BuffAction;
use Battle\Action\EffectAction;
use Battle\Command\CommandInterface;
use Battle\Unit\Effect\Effect;
use Battle\Unit\Effect\EffectCollection;
use Battle\Unit\UnitInterface;
use Exception;
use PHPUnit\Framework\TestCase;
use Battle\Command\CommandFactory;
use Tests\Battle\Factory\UnitFactory;
use Battle\Classes\UnitClassInterface;
use Battle\Unit\Ability\Effect\ReserveForcesAbility;

class TitanTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testCreateTitanClass(): void
    {
        $unit = UnitFactory::createByTemplate(21);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $class = $unit->getClass();

        self::assertEquals(UnitClassInterface::TITAN_ID, $class->getId());
        self::assertEquals(UnitClassInterface::TITAN_NAME, $class->getName());
        self::assertEquals(UnitClassInterface::TITAN_SMALL_ICON, $class->getSmallIcon());

        $abilities = $class->getAbilities($unit);

        foreach ($abilities as $ability) {
            self::assertContainsOnlyInstancesOf(ReserveForcesAbility::class, [$ability]);

            self::assertEquals(
                $this->getReserveForcesActions($unit, $enemyCommand, $command),
                $ability->getAction($enemyCommand, $command)
            );
        }
    }

    private function getReserveForcesActions(
        UnitInterface $unit,
        CommandInterface $enemyCommand,
        CommandInterface $alliesCommand
    ): ActionCollection
    {
        $name = 'Reserve Forces';
        $icon = '/images/icons/ability/156.png';
        $useMessage = 'use Reserve Forces for self';
        $duration = 6;
        $modifyMethod = 'multiplierMaxLife';
        $modifyPower = 130;

        $collection = new ActionCollection();

        // Создаем коллекцию событий (с одним бафом), которая будет применена к персонажу, при применении эффекта
        $onApplyActionCollection = new ActionCollection();

        $onApplyActionCollection->add(new BuffAction(
            $unit,
            $enemyCommand,
            $alliesCommand,
            $useMessage,
            $modifyMethod,
            $modifyPower
        ));

        // Создаем коллекцию эффектов, с одним эффектом при применении - Reserve Forces
        $effects = new EffectCollection();

        $effects->add(new Effect(
            $name,
            $icon,
            $duration,
            $onApplyActionCollection,
            new ActionCollection(),
            new ActionCollection()
        ));

        // Создаем сам эффект
        $collection->add(new EffectAction(
            $unit,
            $enemyCommand,
            $alliesCommand,
            $name,
            $effects
        ));

        return $collection;
    }
}
