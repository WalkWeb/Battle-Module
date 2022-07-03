<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Classes\Undead;

use Battle\Command\CommandException;
use Battle\Command\CommandFactory;
use Battle\Unit\Ability\Ability;
use Battle\Unit\Ability\Damage\HeavyStrikeAbility;
use Battle\Unit\UnitException;
use Exception;
use Tests\AbstractUnitTest;
use Tests\Battle\Factory\UnitFactory;

class DeadKnightTest extends AbstractUnitTest
{
    /**
     * TODO Этот тест будет удален вместе с отдельными php-классами на юнит-классы
     *
     * @throws CommandException
     * @throws UnitException
     * @throws Exception
     */
    public function testCreateDeadKnightClass(): void
    {
        $actionUnit = UnitFactory::createByTemplate(8);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $actionCommand = CommandFactory::create([$actionUnit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $deadKnight = $actionUnit->getClass();

        self::assertEquals(3, $deadKnight->getId());
        self::assertEquals('Dead Knight', $deadKnight->getName());
        self::assertEquals('/images/icons/small/dead-knight.png', $deadKnight->getSmallIcon());

        $abilities = $deadKnight->getAbilities($actionUnit);

        foreach ($abilities as $ability) {
            self::assertContainsOnlyInstancesOf(HeavyStrikeAbility::class, [$ability]);

            $actions = $ability->getAction($enemyCommand, $actionCommand);

            foreach ($actions as $action) {
                self::assertEquals((int)($actionUnit->getOffense()->getDamage() * 2.5), $action->getPower());
            }
        }
    }

    /**
     * TODO Этот тест будет удален вместе с отдельными php-классами на юнит-классы
     *
     * @throws Exception
     */
    public function testDeadKnightReadyAbility(): void
    {
        $unit = UnitFactory::createByTemplate(8);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        for ($i = 0; $i < 30; $i++) {
            $unit->newRound();
        }

        foreach ($unit->getAbilities() as $i => $ability) {
            if ($i === 0) {
                // HeavyStrikeAbility готов и может быть применен
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
    public function testNewCreateDeadKnightClass(): void
    {
        $unit = UnitFactory::createByTemplateNewClassMechanic(8, 3);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $actionCommand = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $deadKnight = $unit->getClass();

        self::assertEquals(3, $deadKnight->getId());
        self::assertEquals('Dead Knight', $deadKnight->getName());
        self::assertEquals('/images/icons/small/dead-knight.png', $deadKnight->getSmallIcon());

        $abilities = $deadKnight->getAbilities($unit);

        foreach ($abilities as $ability) {
            self::assertContainsOnlyInstancesOf(Ability::class, [$ability]);

            $actions = $ability->getAction($enemyCommand, $actionCommand);

            foreach ($actions as $action) {
                self::assertEquals((int)($unit->getOffense()->getDamage() * 2.5), $action->getPower());
            }
        }
    }

    /**
     * @throws Exception
     */
    public function testNewDeadKnightReadyAbility(): void
    {
        $unit = UnitFactory::createByTemplateNewClassMechanic(8, 3);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        for ($i = 0; $i < 30; $i++) {
            $unit->newRound();
        }

        foreach ($unit->getAbilities() as $i => $ability) {
            if ($i === 0) {
                // HeavyStrikeAbility готов и может быть применен
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
