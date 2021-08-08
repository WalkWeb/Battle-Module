<?php

declare(strict_types=1);

namespace Tests\Battle\Action;

use Exception;
use Battle\Action\ActionException;
use Battle\Action\DamageAction;
use Battle\Command\CommandFactory;
use PHPUnit\Framework\TestCase;
use Tests\Battle\Factory\UnitFactory;

class ActionTest extends TestCase
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
        $action = new DamageAction($unit, $defendCommand, $alliesCommand);

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
        $action = new DamageAction($unit, $defendCommand, $alliesCommand);

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
        $action = new DamageAction($unit, $defendCommand, $alliesCommand);

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
        $action = new DamageAction($unit, $defendCommand, $alliesCommand);

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
        $action = new DamageAction($unit, $defendCommand, $alliesCommand);

        $this->expectException(ActionException::class);
        $this->expectExceptionMessage('No method: Battle\Action\AbstractAction::Battle\Action\AbstractAction::getRevertAction');
        $action->getRevertAction();
    }
}
