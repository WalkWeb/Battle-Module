<?php

declare(strict_types=1);

namespace Tests\Battle\Action;

use Battle\Action\DamageAction;
use Battle\Classes\ClassFactoryException;
use Battle\Command\Command;
use Battle\Exception\ActionCollectionException;
use Battle\Exception\CommandException;
use Battle\Exception\DamageActionException;
use PHPUnit\Framework\TestCase;
use Tests\Battle\Factory\UnitFactory;
use Tests\Battle\Factory\UnitFactoryException;
use Throwable;

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
        $unit = UnitFactory::create(1);
        $defendUnit = UnitFactory::create(2);
        $defendCommand = new Command([$defendUnit]);
        $action = new DamageAction($unit, $defendCommand);
        self::assertInstanceOf(DamageAction::class, $action);
    }

    public function testCreateFail(): void
    {
        $this->expectException(Throwable::class);
        new DamageAction();
    }

    /**
     * @throws ClassFactoryException
     * @throws CommandException
     * @throws DamageActionException
     * @throws UnitFactoryException
     */
    public function testApplyAction(): void
    {
        $unit = UnitFactory::create(1);
        $defendUnit = UnitFactory::create(2);
        $defendCommand = new Command([$defendUnit]);
        $action = new DamageAction($unit, $defendCommand);
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
        $actionUnit = UnitFactory::create(1);
        $defendUnit = UnitFactory::create(2);
        $defendCommand = new Command([$defendUnit]);

        $action = new DamageAction($actionUnit, $defendCommand);
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
        $attackerUnit = UnitFactory::create(2);
        $defendUnit = UnitFactory::create(4);
        $defendCommand = new Command([$defendUnit]);

        $actionCollection = $attackerUnit->getDamageAction($defendCommand);

        foreach ($actionCollection->getActions() as $action) {
            $action->handle();
            self::assertEquals(30, $action->getPower());
            self::assertEquals(20, $action->getFactualPower());
        }
    }
}
