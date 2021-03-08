<?php

declare(strict_types=1);

namespace Tests\Battle\Classes;

use Battle\Action\GreatHealAction;
use Battle\Classes\ClassFactoryException;
use Battle\Classes\UnitClassInterface;
use Battle\Command;
use Battle\Exception\CommandException;
use PHPUnit\Framework\TestCase;
use Tests\Battle\Factory\UnitFactory;
use Tests\Battle\Factory\UnitFactoryException;

class PriestTest extends TestCase
{
    /**
     * @throws ClassFactoryException
     * @throws UnitFactoryException
     * @throws CommandException
     */
    public function testCreate(): void
    {
        $actionUnit = UnitFactory::create(5);
        $enemyUnit = UnitFactory::create(1);
        $actionCommand = new Command([$actionUnit]);
        $enemyCommand = new Command([$enemyUnit]);

        $priest = $actionUnit->getClass();

        $this->assertEquals(UnitClassInterface::PRIEST, $priest->getId());

        $actionCollection = $priest->getAbility($actionUnit, $enemyCommand, $actionCommand);

        foreach ($actionCollection->getActions() as $action) {
            $this->assertContainsOnlyInstancesOf(GreatHealAction::class, [$action]);
            $this->assertEquals($actionUnit->getDamage() * 3, $action->getPower());
        }
    }
}
