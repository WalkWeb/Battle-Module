<?php

declare(strict_types=1);

namespace Tests\Battle\Classes;

use Battle\Action\GreatHealAction;
use Battle\Classes\ClassFactoryException;
use Battle\Classes\UnitClassInterface;
use Battle\Command\Command;
use Battle\Command\CommandException;
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
        $actionUnit = UnitFactory::createByTemplate(5);
        $enemyUnit = UnitFactory::createByTemplate(1);
        $actionCommand = new Command([$actionUnit]);
        $enemyCommand = new Command([$enemyUnit]);

        $priest = $actionUnit->getClass();

        self::assertEquals(UnitClassInterface::PRIEST, $priest->getId());

        $actionCollection = $priest->getAbility($actionUnit, $enemyCommand, $actionCommand);

        foreach ($actionCollection->getActions() as $action) {
            self::assertContainsOnlyInstancesOf(GreatHealAction::class, [$action]);
            self::assertEquals($actionUnit->getDamage() * 3, $action->getPower());
        }
    }
}
