<?php

declare(strict_types=1);

namespace Tests\Battle\Action\Damage;

use Battle\Action\Damage\DamageAction;
use Battle\Action\ActionException;
use Battle\Command\CommandFactory;
use Battle\Result\Chat\Message;
use Exception;
use PHPUnit\Framework\TestCase;
use Tests\Battle\Factory\Mock\CommandMockFactory;
use Tests\Battle\Factory\UnitFactory;

class DamageActionTest extends TestCase
{
    private const MESSAGE = '<span style="color: #1e72e3">unit_1</span> attack <span style="color: #1e72e3">unit_2</span> on 20 damage';

    /**
     * @throws Exception
     */
    public function testCreateDamageAction(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $defendUnit = UnitFactory::createByTemplate(2);
        $defendCommand = CommandFactory::create([$defendUnit]);
        $alliesCommand = CommandFactory::create([$unit]);
        $action = new DamageAction($unit, $defendCommand, $alliesCommand, new Message());
        self::assertEquals($unit->getDamage(), $action->getPower());
        self::assertTrue($action->canByUsed());
    }

    /**
     * @throws Exception
     */
    public function testApplyDamageAction(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $defendUnit = UnitFactory::createByTemplate(2);
        $defendCommand = CommandFactory::create([$defendUnit]);
        $alliesCommand = CommandFactory::create([$unit]);
        $action = new DamageAction($unit, $defendCommand, $alliesCommand, new Message());
        $message = $action->handle();
        self::assertEquals($unit->getDamage(), $action->getPower());
        self::assertEquals(self::MESSAGE, $message);
    }

    /**
     * @throws Exception
     */
    public function testDamageActionApplyUnit(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $defendUnit = UnitFactory::createByTemplate(2);
        $defendCommand = CommandFactory::create([$defendUnit]);
        $alliesCommand = CommandFactory::create([$unit]);
        $action = new DamageAction($unit, $defendCommand, $alliesCommand, new Message());
        $action->handle();

        self::assertEquals(20, $action->getPower());
        self::assertEquals($unit->getName(), $action->getActionUnit()->getName());
        self::assertEquals($defendUnit->getName(), $action->getTargetUnit()->getName());
    }

    /**
     * @throws Exception
     */
    public function testDamageActionFactualDamage(): void
    {
        $attackerUnit = UnitFactory::createByTemplate(2);
        $enemyUnit = UnitFactory::createByTemplate(4);
        $enemyCommand = CommandFactory::create([$enemyUnit]);
        $alliesCommand = CommandFactory::create([$attackerUnit]);

        $actionCollection = $attackerUnit->getAction($enemyCommand, $alliesCommand);

        foreach ($actionCollection as $action) {
            $action->handle();
            self::assertEquals(30, $action->getPower());
            self::assertEquals(20, $action->getFactualPower());
        }
    }

    /**
     * @throws Exception
     */
    public function testDamageActionDeadCommand(): void
    {
        $attackerUnit = UnitFactory::createByTemplate(2);

        // dead unit
        $enemyUnit = UnitFactory::createByTemplate(10);
        $enemyCommand = CommandFactory::create([$enemyUnit]);
        $alliesCommand = CommandFactory::create([$attackerUnit]);

        $actionCollection = $attackerUnit->getAction($enemyCommand, $alliesCommand);

        foreach ($actionCollection as $action) {
            $this->expectException(ActionException::class);
            $this->expectExceptionMessage(ActionException::NO_DEFINED);
            $action->handle();
        }
    }

    /**
     * Тест на исключительную ситуацию со сломанным объектом команды, который на запрос isAlive() вернет true, т.е.
     * команда жива, но на запрос getDefinedUnit(), т.е. на запрос юнита для атаки вернет null (т.е. живых нет)
     *
     * @throws Exception
     */
    public function testDamageActionNoTarget(): void
    {
        $attackerUnit = UnitFactory::createByTemplate(2);
        $alliesCommand = CommandFactory::create([$attackerUnit]);
        $enemyCommand = (new CommandMockFactory())->createAliveAndNoDefinedUnit();

        $actionCollection = $attackerUnit->getAction($enemyCommand, $alliesCommand);

        foreach ($actionCollection as $action) {
            $this->expectException(ActionException::class);
            $this->expectExceptionMessage(ActionException::NO_DEFINED_AGAIN);
            $action->handle();
        }
    }
}
