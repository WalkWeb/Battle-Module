<?php

declare(strict_types=1);

namespace Tests\Battle\Classes\Undead;

use Battle\Action\GreatHealAction;
use Battle\Classes\ClassFactoryException;
use Battle\Classes\UnitClassInterface;
use Battle\Command\CommandException;
use Battle\Command\CommandFactory;
use Battle\Unit\UnitException;
use PHPUnit\Framework\TestCase;
use Tests\Battle\Factory\UnitFactory;
use Tests\Battle\Factory\UnitFactoryException;

class DarkMageTest extends TestCase
{
    /**
     * @throws ClassFactoryException
     * @throws CommandException
     * @throws UnitFactoryException
     * @throws UnitException
     */
    public function testCreateDarkMageClass(): void
    {
        $actionUnit = UnitFactory::createByTemplate(7);
        $enemyUnit = UnitFactory::createByTemplate(1);
        $actionCommand = CommandFactory::create([$actionUnit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $priest = $actionUnit->getClass();

        self::assertEquals(UnitClassInterface::DARK_MAGE, $priest->getId());
        self::assertEquals(UnitClassInterface::DARK_MAGE_SMALL_ICON, $priest->getSmallIcon());

        $actionCollection = $priest->getAbility($actionUnit, $enemyCommand, $actionCommand);

        foreach ($actionCollection->getActions() as $action) {
            self::assertContainsOnlyInstancesOf(GreatHealAction::class, [$action]);
            self::assertEquals($actionUnit->getDamage() * 3, $action->getPower());
        }
    }
}