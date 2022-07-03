<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Classes\Undead;

use Battle\Command\CommandException;
use Battle\Command\CommandFactory;
use Battle\Unit\Ability\Ability;
use Battle\Unit\Ability\Summon\SummonSkeletonAbility;
use Battle\Unit\UnitException;
use Exception;
use Tests\AbstractUnitTest;
use Tests\Battle\Factory\UnitFactory;

class DarkMageTest extends AbstractUnitTest
{
    /**
     * TODO Этот тест будет удален вместе с отдельными php-классами на юнит-классы
     *
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
     * TODO Этот тест будет удален вместе с отдельными php-классами на юнит-классы
     *
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

    /**
     * @throws CommandException
     * @throws UnitException
     * @throws Exception
     */
    public function testNewCreateDarkMageClass(): void
    {
        $unit = UnitFactory::createByTemplateNewClassMechanic(7, 4);
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
    public function testNewDarkMageReadyAbility(): void
    {
        $unit = UnitFactory::createByTemplateNewClassMechanic(7, 4);
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
