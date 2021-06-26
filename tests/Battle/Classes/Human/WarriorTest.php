<?php

declare(strict_types=1);

namespace Tests\Battle\Classes\Human;

use Battle\Action\Damage\HeavyStrikeAction;
use Battle\Classes\UnitClassInterface;
use Battle\Command\CommandException;
use Battle\Command\CommandFactory;
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

        $actionCollection = $warrior->getAbility($actionUnit, $enemyCommand, $actionCommand);

        foreach ($actionCollection as $action) {
            self::assertContainsOnlyInstancesOf(HeavyStrikeAction::class, [$action]);
            self::assertEquals($actionUnit->getDamage() * 2.5, $action->getPower());
        }
    }
}
