<?php

declare(strict_types=1);

namespace Tests\Battle\Classes\Human;

use Battle\Classes\UnitClassInterface;
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

        self::assertEquals(UnitClassInterface::WARRIOR_ID, $warrior->getId());
        self::assertEquals(UnitClassInterface::WARRIOR_NAME, $warrior->getName());
        self::assertEquals(UnitClassInterface::WARRIOR_SMALL_ICON, $warrior->getSmallIcon());

        $abilities = $warrior->getAbilities($actionUnit);

        foreach ($abilities as $ability) {
            self::assertContainsOnlyInstancesOf(HeavyStrikeAbility::class, [$ability]);

            $actions = $ability->getAction($enemyCommand, $actionCommand);

            foreach ($actions as $action) {
                self::assertEquals((int)($actionUnit->getDamage() * 2.5), $action->getPower());
            }
        }
    }
}
