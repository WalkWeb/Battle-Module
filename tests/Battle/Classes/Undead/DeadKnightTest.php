<?php

declare(strict_types=1);

namespace Tests\Battle\Classes\Undead;

use Battle\Action\HeavyStrikeAction;
use Battle\Classes\ClassFactoryException;
use Battle\Classes\UnitClassInterface;
use Battle\Command\CommandException;
use Battle\Command\CommandFactory;
use Battle\Unit\UnitException;
use PHPUnit\Framework\TestCase;
use Tests\Battle\Factory\UnitFactory;
use Tests\Battle\Factory\UnitFactoryException;

class DeadKnightTest extends TestCase
{
    /**
     * @throws ClassFactoryException
     * @throws CommandException
     * @throws UnitFactoryException
     * @throws UnitException
     */
    public function testCreateDeadKnightClass(): void
    {
        $actionUnit = UnitFactory::createByTemplate(8);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $actionCommand = CommandFactory::create([$actionUnit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $warrior = $actionUnit->getClass();

        self::assertEquals(UnitClassInterface::DEAD_KNIGHT, $warrior->getId());
        self::assertEquals(UnitClassInterface::DEAD_KNIGHT_SMALL_ICON, $warrior->getSmallIcon());

        $actionCollection = $warrior->getAbility($actionUnit, $enemyCommand, $actionCommand);

        foreach ($actionCollection->getActions() as $action) {
            self::assertContainsOnlyInstancesOf(HeavyStrikeAction::class, [$action]);
            self::assertEquals($actionUnit->getDamage() * 2.5, $action->getPower());
        }
    }
}