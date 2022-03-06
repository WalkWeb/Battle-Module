<?php

declare(strict_types=1);

namespace Tests\Battle\Action;

use Battle\Action\WaitAction;
use Exception;
use Battle\Action\ActionException;
use Battle\Action\DamageAction;
use Battle\Command\CommandFactory;
use Tests\AbstractUnitTest;
use Tests\Battle\Factory\BaseFactory;
use Tests\Battle\Factory\UnitFactory;

class ActionTest extends AbstractUnitTest
{
    /**
     * Тест на ситуацию, когда у не-SummonAction вызывают метод getSummonUnit()
     *
     * @throws Exception
     */
    public function testActionNoGetSummonUnit(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $defendUnit = UnitFactory::createByTemplate(2);
        $defendCommand = CommandFactory::create([$defendUnit]);
        $alliesCommand = CommandFactory::create([$unit]);
        $action = new DamageAction($unit, $defendCommand, $alliesCommand, DamageAction::TARGET_RANDOM_ENEMY);

        $this->expectException(ActionException::class);
        $this->expectExceptionMessage('No method: Battle\Action\AbstractAction::Battle\Action\AbstractAction::getSummonUnit');
        $action->getSummonUnit();
    }

    /**
     * Тест на ситуацию, когда у не-BuffAction вызывают метод getModifyMethod()
     *
     * @throws Exception
     */
    public function testActionNoGetModifyMethod(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $defendUnit = UnitFactory::createByTemplate(2);
        $defendCommand = CommandFactory::create([$defendUnit]);
        $alliesCommand = CommandFactory::create([$unit]);
        $action = new DamageAction($unit, $defendCommand, $alliesCommand, DamageAction::TARGET_RANDOM_ENEMY);

        $this->expectException(ActionException::class);
        $this->expectExceptionMessage('No method: Battle\Action\AbstractAction::Battle\Action\AbstractAction::getModifyMethod');
        $action->getModifyMethod();
    }

    /**
     * Тест на ситуацию, когда у не-BuffAction вызывают метод setRevertValue()
     *
     * @throws Exception
     */
    public function testActionNoSetRevertValue(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $defendUnit = UnitFactory::createByTemplate(2);
        $defendCommand = CommandFactory::create([$defendUnit]);
        $alliesCommand = CommandFactory::create([$unit]);
        $action = new DamageAction($unit, $defendCommand, $alliesCommand, DamageAction::TARGET_RANDOM_ENEMY);

        $this->expectException(ActionException::class);
        $this->expectExceptionMessage('No method: Battle\Action\AbstractAction::Battle\Action\AbstractAction::setRevertValue');
        $action->setRevertValue(10);
    }

    /**
     * Тест на ситуацию, когда у не-BuffAction вызывают метод getRevertValue()
     *
     * @throws Exception
     */
    public function testActionNoGetRevertValue(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $defendUnit = UnitFactory::createByTemplate(2);
        $defendCommand = CommandFactory::create([$defendUnit]);
        $alliesCommand = CommandFactory::create([$unit]);
        $action = new DamageAction($unit, $defendCommand, $alliesCommand, DamageAction::TARGET_RANDOM_ENEMY);

        $this->expectException(ActionException::class);
        $this->expectExceptionMessage('No method: Battle\Action\AbstractAction::Battle\Action\AbstractAction::getRevertValue');
        $action->getRevertValue();
    }

    /**
     * Тест на ситуацию, когда у не-BuffAction вызывают метод getRevertAction()
     *
     * @throws Exception
     */
    public function testActionNoGetRevertAction(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $defendUnit = UnitFactory::createByTemplate(2);
        $defendCommand = CommandFactory::create([$defendUnit]);
        $alliesCommand = CommandFactory::create([$unit]);
        $action = new DamageAction($unit, $defendCommand, $alliesCommand, DamageAction::TARGET_RANDOM_ENEMY);

        $this->expectException(ActionException::class);
        $this->expectExceptionMessage('No method: Battle\Action\AbstractAction::Battle\Action\AbstractAction::getRevertAction');
        $action->getRevertAction();
    }

    /**
     * Тест на ситуацию, когда у не-EffectAction вызывают метод getEffects()
     *
     * @throws Exception
     */
    public function testActionNoGetEffects(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $defendUnit = UnitFactory::createByTemplate(2);
        $defendCommand = CommandFactory::create([$defendUnit]);
        $alliesCommand = CommandFactory::create([$unit]);
        $action = new DamageAction($unit, $defendCommand, $alliesCommand, DamageAction::TARGET_RANDOM_ENEMY);

        $this->expectException(ActionException::class);
        $this->expectExceptionMessage('No method: Battle\Action\AbstractAction::Battle\Action\AbstractAction::getEffect');
        $action->getEffect();
    }

    /**
     * Тест на ситуацию, когда у не лечения/удара/воскрешения вызывают метод getFactualPowerByUnit()
     *
     * @throws Exception
     */
    public function testActionNoGetFactualPowerByUnit(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $defendUnit = UnitFactory::createByTemplate(2);
        $defendCommand = CommandFactory::create([$defendUnit]);
        $alliesCommand = CommandFactory::create([$unit]);
        $action = new WaitAction($unit, $defendCommand, $alliesCommand);

        $this->expectException(ActionException::class);
        $this->expectExceptionMessage('Action: No method: Battle\Action\AbstractAction::Battle\Action\AbstractAction::getFactualPowerByUnit');
        $action->getFactualPowerByUnit($unit->getId());
    }

    /**
     * Тест на изменение $actionUnit в Action
     *
     * @throws Exception
     */
    public function testActionChangeActionUnit(): void
    {
        [$unit, $command, $enemyCommand, $enemyUnit] = BaseFactory::create(1, 2);

        $action = new DamageAction($unit, $enemyCommand, $command, DamageAction::TARGET_RANDOM_ENEMY);

        self::assertEquals($unit, $action->getActionUnit());
        self::assertEquals($unit, $action->getCreatorUnit());

        $action->changeActionUnit($enemyUnit);

        self::assertEquals($enemyUnit, $action->getActionUnit());

        // При этом creatorUnit не изменился
        self::assertEquals($unit, $action->getCreatorUnit());
    }

    /**
     * Тест проверяет, что у Action корректно работает clone и смена ActionUnit - юнит меняется только там, где и был
     * изменен
     *
     * @throws Exception
     */
    public function testActionCloneChangeActionUnit(): void
    {
        [$unit, $command, $enemyCommand, $enemyUnit] = BaseFactory::create(1, 2);

        $action = new DamageAction($unit, $enemyCommand, $command, DamageAction::TARGET_RANDOM_ENEMY);

        $cloneAction = clone $action;

        self::assertEquals($unit, $action->getActionUnit());
        self::assertEquals($unit, $cloneAction->getActionUnit());

        $action->changeActionUnit($enemyUnit);

        self::assertEquals($enemyUnit, $action->getActionUnit());
        self::assertEquals($unit, $cloneAction->getActionUnit());
    }
}
