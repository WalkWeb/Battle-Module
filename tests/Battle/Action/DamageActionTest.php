<?php

declare(strict_types=1);

namespace Tests\Battle\Action;

use Battle\Action\DamageAction;
use Battle\Action\ActionException;
use Battle\Command\CommandFactory;
use Exception;
use Tests\AbstractUnitTest;
use Tests\Battle\Factory\Mock\CommandMockFactory;
use Tests\Battle\Factory\UnitFactory;

class DamageActionTest extends AbstractUnitTest
{
    /**
     * @throws Exception
     */
    public function testCreateDamageAction(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $defendUnit = UnitFactory::createByTemplate(2);
        $defendCommand = CommandFactory::create([$defendUnit]);
        $alliesCommand = CommandFactory::create([$unit]);
        $action = new DamageAction($unit, $defendCommand, $alliesCommand, DamageAction::TARGET_RANDOM_ENEMY);

        self::assertEquals($unit, $action->getActionUnit());
        self::assertEquals($unit, $action->getCreatorUnit());
        self::assertEquals($unit->getDamage(), $action->getPower());
        self::assertTrue($action->canByUsed());
        self::assertEquals(DamageAction::UNIT_ANIMATION_METHOD, $action->getAnimationMethod());
        self::assertEquals('damage', $action->getMessageMethod());
        self::assertEquals('attack', $action->getNameAction());
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
        $action = new DamageAction($unit, $defendCommand, $alliesCommand, DamageAction::TARGET_RANDOM_ENEMY);
        $action->handle();
        self::assertEquals($unit->getDamage(), $action->getPower());
    }

    /**
     * @throws Exception
     */
    public function testDamageActionApplyUnit(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$enemyUnit]);
        $enemyCommand = CommandFactory::create([$unit]);
        $action = new DamageAction($unit, $command, $enemyCommand, DamageAction::TARGET_RANDOM_ENEMY);
        $action->handle();

        self::assertEquals(20, $action->getPower());
        self::assertEquals(20, $action->getFactualPower());
        self::assertEquals(20, $action->getFactualPowerByUnit($enemyUnit->getId()));
        self::assertEquals($unit->getId(), $action->getActionUnit()->getId());
        self::assertCount(1, $action->getTargetUnits());

        foreach ($action->getTargetUnits() as $targetUnit) {
            self::assertEquals($enemyUnit->getId(), $targetUnit->getId());
        }
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

    /**
     * @throws Exception
     */
    public function testDamageActionUnknownTypeTarget(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $defendUnit = UnitFactory::createByTemplate(2);
        $defendCommand = CommandFactory::create([$defendUnit]);
        $alliesCommand = CommandFactory::create([$unit]);

        $typeTarget = 10;

        $action = new DamageAction($unit, $defendCommand, $alliesCommand, $typeTarget);

        self::assertEquals($typeTarget, $action->getTypeTarget());

        $this->expectException(ActionException::class);
        $this->expectExceptionMessage(ActionException::UNKNOWN_TYPE_TARGET . ': ' . $typeTarget);
        $action->handle();
    }

    /**
     * Тест на ситуацию, когда у DamageAction запрашивается фактический урон по юниту, по которому урон не наносился
     *
     * @throws Exception
     */
    public function testDamageActionNoPowerByUnit(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $defendUnit = UnitFactory::createByTemplate(2);
        $defendCommand = CommandFactory::create([$defendUnit]);
        $alliesCommand = CommandFactory::create([$unit]);
        $action = new DamageAction($unit, $defendCommand, $alliesCommand, DamageAction::TARGET_RANDOM_ENEMY);
        $action->handle();

        // Общий factualPower получаем нормально
        self::assertEquals($unit->getDamage(), $action->getFactualPower());

        // factualPower, по юниту, по которому урон наносился - тоже
        self::assertEquals($unit->getDamage(), $action->getFactualPowerByUnit($defendUnit->getId()));

        // А вот factualPower по юниту, по которому урон не наносился - отсутствует
        $this->expectException(ActionException::class);
        $this->expectExceptionMessage(ActionException::NO_POWER_BY_UNIT);
        $action->getFactualPowerByUnit($unit->getId());
    }

    /**
     * Тест на нанесение урона сразу всей вражеской команде
     *
     * @throws Exception
     */
    public function testDamageActionTargetAllAliveEnemy(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $firstEnemyUnit = UnitFactory::createByTemplate(2);
        $secondaryEnemyUnit = UnitFactory::createByTemplate(3);

        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$firstEnemyUnit, $secondaryEnemyUnit]);

        $action = new DamageAction($unit, $enemyCommand, $command, DamageAction::TARGET_ALL_ENEMY);

        $action->handle();

        // Проверяем, что урон нанесен по обоим юнитам
        self::assertEquals($firstEnemyUnit->getTotalLife() - $unit->getDamage(), $firstEnemyUnit->getLife());
        self::assertEquals($secondaryEnemyUnit->getTotalLife() - $unit->getDamage(), $secondaryEnemyUnit->getLife());
    }

    /**
     * Тест на указывание, что событие (урон) было заблокировано
     *
     * @throws Exception
     */
    public function testDamageActionBlocked(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $defendUnit = UnitFactory::createByTemplate(2);
        $defendCommand = CommandFactory::create([$defendUnit]);
        $alliesCommand = CommandFactory::create([$unit]);
        $action = new DamageAction($unit, $defendCommand, $alliesCommand, DamageAction::TARGET_RANDOM_ENEMY);

        // По-умолчанию isBlocked возвращает false
        self::assertFalse($action->isBlocked($defendUnit));

        // Указываем, что урон был заблокирован
        $action->blocked($defendUnit);

        // И получаем true
        self::assertTrue($action->isBlocked($defendUnit));
    }
}
