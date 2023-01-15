<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Classes\Undead;

use Battle\Command\CommandException;
use Battle\Command\CommandFactory;
use Battle\Unit\Ability\Ability;
use Battle\Unit\UnitException;
use Exception;
use Tests\AbstractUnitTest;
use Tests\Factory\UnitFactory;

class DarkMageTest extends AbstractUnitTest
{
    /**
     * @throws CommandException
     * @throws UnitException
     * @throws Exception
     */
    public function testCreateDarkMageClass(): void
    {
        $unit = UnitFactory::createByTemplate(7);
        $enemyUnit = UnitFactory::createByTemplate(1);
        $actionCommand = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $darkMage = $unit->getClass();

        self::assertEquals(4, $darkMage->getId());
        self::assertEquals('Dark Mage', $darkMage->getName());
        self::assertEquals('/images/icons/small/dark-mage.png', $darkMage->getSmallIcon());

        $abilities = $darkMage->getAbilities($unit);

        foreach ($abilities as $ability) {
            self::assertContainsOnlyInstancesOf(Ability::class, [$ability]);

            $actions = $ability->getActions($enemyCommand, $actionCommand);

            foreach ($actions as $action) {
                $action->handle();
            }
        }

        // Размер команды увеличился на 1 юнита
        self::assertCount(2, $actionCommand->getUnits());
    }

    /**
     * @throws Exception
     */
    public function testDarkMageReadyAbility(): void
    {
        $unit = UnitFactory::createByTemplate(7);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        for ($i = 0; $i < 30; $i++) {
            $unit->newRound();
        }

        foreach ($unit->getAbilities() as $i => $ability) {
            if ($i === 0) {
                // SummonSkeletonAbility готов и может быть применен
                self::assertTrue($ability->isReady());
                self::assertTrue($ability->canByUsed($enemyCommand, $command));
            }
            if ($i === 1) {
                // WillToLiveAbility не готов (юнит не мертв), но может быть применен (ранее не применялся)
                self::assertFalse($ability->isReady());
                self::assertTrue($ability->canByUsed($enemyCommand, $command));
            }
        }
    }
}
