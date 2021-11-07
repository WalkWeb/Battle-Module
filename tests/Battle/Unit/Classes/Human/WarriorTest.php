<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Classes\Human;

use Battle\Command\CommandException;
use Battle\Command\CommandFactory;
use Battle\Unit\Ability\Damage\HeavyStrikeAbility;
use Battle\Unit\UnitException;
use Exception;
use PHPUnit\Framework\TestCase;
use Tests\Battle\Factory\UnitFactory;

class WarriorTest extends TestCase
{
    /**
     * @throws CommandException
     * @throws UnitException
     * @throws Exception
     */
    public function testCreateWarriorClass(): void
    {
        $actionUnit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $actionCommand = CommandFactory::create([$actionUnit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $warrior = $actionUnit->getClass();

        self::assertEquals(1, $warrior->getId());
        self::assertEquals('Warrior', $warrior->getName());
        self::assertEquals('/images/icons/small/warrior.png', $warrior->getSmallIcon());

        $abilities = $warrior->getAbilities($actionUnit);

        foreach ($abilities as $ability) {
            self::assertContainsOnlyInstancesOf(HeavyStrikeAbility::class, [$ability]);

            $actions = $ability->getAction($enemyCommand, $actionCommand);

            foreach ($actions as $action) {
                self::assertEquals((int)($actionUnit->getDamage() * 2.5), $action->getPower());
            }
        }
    }

    /**
     * @throws Exception
     */
    public function testWarriorReadyAbility(): void
    {
        $unit = UnitFactory::createByTemplate(1);
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
