<?php

declare(strict_types=1);

namespace Tests\Battle\Action;

use Battle\Action\DamageAction;
use Battle\Classes\ClassFactoryException;
use Battle\Command\Command;
use Battle\Command\CommandException;
use Battle\Exception\DamageActionException;
use PHPUnit\Framework\TestCase;
use Tests\Battle\Factory\UnitFactory;
use Tests\Battle\Factory\UnitFactoryException;

class DamageActionTest extends TestCase
{
    private const MESSAGE = '<b>unit_1</b> [100/100] normal attack <b>unit_2</b> [130/150] on 20 damage';

    /**
     * @throws CommandException
     * @throws UnitFactoryException
     * @throws ClassFactoryException
     */
    public function testCreate(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $defendUnit = UnitFactory::createByTemplate(2);
        $defendCommand = new Command([$defendUnit]);
        $alliesCommand = new Command([$unit]);
        $action = new DamageAction($unit, $defendCommand, $alliesCommand);
        self::assertInstanceOf(DamageAction::class, $action);
    }

    /**
     * @throws ClassFactoryException
     * @throws CommandException
     * @throws DamageActionException
     * @throws UnitFactoryException
     */
    public function testApplyAction(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $defendUnit = UnitFactory::createByTemplate(2);
        $defendCommand = new Command([$defendUnit]);
        $alliesCommand = new Command([$unit]);
        $action = new DamageAction($unit, $defendCommand, $alliesCommand);
        $message = $action->handle();
        self::assertEquals($unit->getDamage(), $action->getPower());
        self::assertEquals(self::MESSAGE, $message);
    }

    /**
     * @throws ClassFactoryException
     * @throws CommandException
     * @throws DamageActionException
     * @throws UnitFactoryException
     */
    public function testActionUnit(): void
    {
        $actionUnit = UnitFactory::createByTemplate(1);
        $defendUnit = UnitFactory::createByTemplate(2);
        $defendCommand = new Command([$defendUnit]);
        $alliesCommand = new Command([$actionUnit]);
        $action = new DamageAction($actionUnit, $defendCommand, $alliesCommand);
        $action->handle();

        self::assertEquals(20, $action->getPower());
        self::assertEquals($actionUnit->getName(), $action->getActionUnit()->getName());
        self::assertEquals($defendUnit->getName(), $action->getTargetUnit()->getName());
    }

    /**
     * @throws ClassFactoryException
     * @throws CommandException
     * @throws UnitFactoryException
     */
    public function testFactualDamage(): void
    {
        $attackerUnit = UnitFactory::createByTemplate(2);
        $enemyUnit = UnitFactory::createByTemplate(4);
        $enemyCommand = new Command([$enemyUnit]);
        $alliesCommand = new Command([$attackerUnit]);

        $actionCollection = $attackerUnit->getDamageAction($enemyCommand, $alliesCommand);

        foreach ($actionCollection->getActions() as $action) {
            $action->handle();
            self::assertEquals(30, $action->getPower());
            self::assertEquals(20, $action->getFactualPower());
        }
    }
}
