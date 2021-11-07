<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Classes\Undead;

use Battle\Command\CommandException;
use Battle\Command\CommandFactory;
use Battle\Unit\Ability\Summon\SummonSkeletonAbility;
use Battle\Unit\UnitException;
use Exception;
use PHPUnit\Framework\TestCase;
use Tests\Battle\Factory\UnitFactory;

class DarkMageTest extends TestCase
{
    /**
     * @throws CommandException
     * @throws UnitException
     * @throws Exception
     */
    public function testCreateDarkMageClass(): void
    {
        $actionUnit = UnitFactory::createByTemplate(7);
        $enemyUnit = UnitFactory::createByTemplate(1);
        $actionCommand = CommandFactory::create([$actionUnit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $darkMage = $actionUnit->getClass();

        self::assertEquals(4, $darkMage->getId());
        self::assertEquals('Dark Mage', $darkMage->getName());
        self::assertEquals('/images/icons/small/dark-mage.png', $darkMage->getSmallIcon());

        $abilities = $darkMage->getAbilities($actionUnit);

        foreach ($abilities as $ability) {
            self::assertContainsOnlyInstancesOf(SummonSkeletonAbility::class, [$ability]);

            $actions = $ability->getAction($enemyCommand, $actionCommand);

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

        foreach ($unit->getAbilities() as $ability) {
            self::assertTrue($ability->isReady());
            self::assertTrue($ability->canByUsed($enemyCommand, $command));
        }
    }
}
