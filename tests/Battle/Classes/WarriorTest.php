<?php

declare(strict_types=1);

namespace Tests\Battle\Classes;

use Battle\Action\HeavyStrikeAction;
use Battle\Classes\ClassFactoryException;
use Battle\Classes\UnitClassInterface;
use Battle\Command;
use Battle\Exception\CommandException;
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
        $actionUnit = UnitFactory::create(1);
        $enemyUnit = UnitFactory::create(2);
        $actionCommand = new Command([$actionUnit]);
        $enemyCommand = new Command([$enemyUnit]);

        $warrior = $actionUnit->getClass();

        $this->assertEquals(UnitClassInterface::WARRIOR, $warrior->getId());

        $actionCollection = $warrior->getAbility($actionUnit, $enemyCommand, $actionCommand);

        foreach ($actionCollection->getActions() as $action) {
            $this->assertContainsOnlyInstancesOf(HeavyStrikeAction::class, [$action]);
            $this->assertEquals($actionUnit->getDamage() * 2.5, $action->getPower());
        }
    }
}
