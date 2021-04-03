<?php

declare(strict_types=1);

namespace Tests\Battle\Classes;

use Battle\Action\HeavyStrikeAction;
use Battle\Classes\ClassFactoryException;
use Battle\Classes\UnitClassInterface;
use Battle\Command\Command;
use Battle\Command\CommandException;
use Battle\Command\CommandFactory;
use PHPUnit\Framework\TestCase;
use Tests\Battle\Factory\UnitFactory;
use Tests\Battle\Factory\UnitFactoryException;

class WarriorTest extends TestCase
{
    /**
     * @throws ClassFactoryException
     * @throws UnitFactoryException
     * @throws CommandException
     */
    public function testCreate(): void
    {
        $actionUnit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $actionCommand = CommandFactory::create([$actionUnit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $warrior = $actionUnit->getClass();

        self::assertEquals(UnitClassInterface::WARRIOR, $warrior->getId());

        $actionCollection = $warrior->getAbility($actionUnit, $enemyCommand, $actionCommand);

        foreach ($actionCollection->getActions() as $action) {
            self::assertContainsOnlyInstancesOf(HeavyStrikeAction::class, [$action]);
            self::assertEquals($actionUnit->getDamage() * 2.5, $action->getPower());
        }
    }
}
