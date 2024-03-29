<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Classes\Human;

use Battle\Action\HealAction;
use Battle\Action\ResurrectionAction;
use Battle\Command\CommandException;
use Battle\Command\CommandFactory;
use Battle\Unit\Ability\Ability;
use Battle\Unit\UnitException;
use Exception;
use Tests\AbstractUnitTest;
use Tests\Factory\UnitFactory;

class PriestTest extends AbstractUnitTest
{
    /**
     * Тест на создание класса Priest и проверка его способностей
     *
     * @throws CommandException
     * @throws UnitException
     * @throws Exception
     */
    public function testPriestCreateClass(): void
    {
        $unit = UnitFactory::createByTemplate(5);
        $enemyUnit = UnitFactory::createByTemplate(1);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $priest = $unit->getClass();

        self::assertEquals(2, $priest->getId());
        self::assertEquals('Priest', $priest->getName());
        self::assertEquals('/images/icons/small/priest.png', $priest->getSmallIcon());

        $abilities = $priest->getAbilities($unit);

        self::assertCount(2, $abilities);

        foreach ($abilities as $i => $ability) {
            if ($i === 0) {
                self::assertContainsOnlyInstancesOf(Ability::class, [$ability]);

                $actions = $ability->getActions($enemyCommand, $command);

                foreach ($actions as $action) {
                    self::assertContainsOnlyInstancesOf(HealAction::class, [$action]);
                    self::assertEquals(60, $action->getPower());
                }
            }
            if ($i === 1) {
                self::assertContainsOnlyInstancesOf(Ability::class, [$ability]);

                $actions = $ability->getActions($enemyCommand, $command);

                foreach ($actions as $action) {
                    self::assertContainsOnlyInstancesOf(ResurrectionAction::class, [$action]);
                    self::assertEquals(30, $action->getPower());
                }
            }
        }
    }

    /**
     * Тест на активацию способностей Priest
     *
     * @throws Exception
     */
    public function testPriestReadyAbility(): void
    {
        $unit = UnitFactory::createByTemplate(5);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        for ($i = 0; $i < 30; $i++) {
            $unit->newRound();
        }

        foreach ($unit->getAbilities() as $i => $ability) {
            if ($i === 0) {
                // Лечение не может быть применено - лечить некого
                self::assertTrue($ability->isReady());
                self::assertFalse($ability->canByUsed($enemyCommand, $command));
            }
            if ($i === 1) {
                // Воскрешение не может быть применено - воскрешать некого
                self::assertTrue($ability->isReady());
                self::assertFalse($ability->canByUsed($enemyCommand, $command));
            }
            if ($i === 2) {
                // Расовая способность к воскрешению не готова, но может быть применена (еще не использовалась)
                self::assertFalse($ability->isReady());
                self::assertTrue($ability->canByUsed($enemyCommand, $command));
            }

        }
    }
}
