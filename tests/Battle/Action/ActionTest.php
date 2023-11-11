<?php

declare(strict_types=1);

namespace Tests\Battle\Action;

use Battle\Action\WaitAction;
use Battle\Command\CommandInterface;
use Battle\Unit\UnitInterface;
use Exception;
use Battle\Action\ActionException;
use Battle\Action\DamageAction;
use Battle\Command\CommandFactory;
use Tests\AbstractUnitTest;
use Tests\Factory\BaseFactory;
use Tests\Factory\UnitFactory;

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
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = $this->createDamageAction($unit, $enemyCommand, $command);

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
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = $this->createDamageAction($unit, $enemyCommand, $command);

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
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = $this->createDamageAction($unit, $enemyCommand, $command);

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
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = $this->createDamageAction($unit, $enemyCommand, $command);

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
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = $this->createDamageAction($unit, $enemyCommand, $command);

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
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = $this->createDamageAction($unit, $enemyCommand, $command);

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
        $action = new WaitAction($this->getContainer(), $unit, $defendCommand, $alliesCommand);

        $this->expectException(ActionException::class);
        $this->expectExceptionMessage('Action: No method: Battle\Action\AbstractAction::Battle\Action\AbstractAction::getFactualPowerByUnit');
        $action->getFactualPowerByUnit($unit);
    }

    /**
     * Тест на ситуацию, когда у не-DamageAction вызывается метод blocked()
     *
     * @throws Exception
     */
    public function testActionNoBlocked(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $defendUnit = UnitFactory::createByTemplate(2);
        $defendCommand = CommandFactory::create([$defendUnit]);
        $alliesCommand = CommandFactory::create([$unit]);
        $action = new WaitAction($this->getContainer(), $unit, $defendCommand, $alliesCommand);

        self::assertFalse($action->isBlocked($defendUnit));

        $this->expectException(ActionException::class);
        $this->expectExceptionMessage('Action: No method: Battle\Action\AbstractAction::Battle\Action\AbstractAction::blocked');
        $action->blocked($defendUnit);
    }

    /**
     * Тест на ситуацию, когда у не-DamageAction вызывается метод blocked()
     *
     * @throws Exception
     */
    public function testActionNoDodged(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);
        $action = new WaitAction($this->getContainer(), $unit, $enemyCommand, $command);

        self::assertFalse($action->isEvaded($enemyUnit));

        $this->expectException(ActionException::class);
        $this->expectExceptionMessage('Action: No method: Battle\Action\AbstractAction::Battle\Action\AbstractAction::dodged');
        $action->dodged($enemyUnit);
    }

    /**
     * Тест на ситуацию, когда у не-DamageAction вызывается метод isCanBeAvoided()
     *
     * @throws Exception
     */
    public function testActionNoCanBeAvoided(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $defendUnit = UnitFactory::createByTemplate(2);
        $defendCommand = CommandFactory::create([$defendUnit]);
        $alliesCommand = CommandFactory::create([$unit]);
        $action = new WaitAction($this->getContainer(), $unit, $defendCommand, $alliesCommand);

        $this->expectException(ActionException::class);
        $this->expectExceptionMessage('Action: No method: Battle\Action\AbstractAction::Battle\Action\AbstractAction::isCanBeAvoided');
        $action->isCanBeAvoided();
    }

    /**
     * Тест на ситуацию, когда у не-DamageAction вызывается метод getOffense()
     *
     * @throws Exception
     */
    public function testActionNoOffense(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $defendUnit = UnitFactory::createByTemplate(2);
        $defendCommand = CommandFactory::create([$defendUnit]);
        $alliesCommand = CommandFactory::create([$unit]);
        $action = new WaitAction($this->getContainer(), $unit, $defendCommand, $alliesCommand);

        $this->expectException(ActionException::class);
        $this->expectExceptionMessage('Action: No method: Battle\Action\AbstractAction::Battle\Action\AbstractAction::getOffense');
        $action->getOffense();
    }

    /**
     * Тест на ситуацию, когда у не-DamageAction вызывается метод isCriticalDamage()
     *
     * @throws Exception
     */
    public function testActionIsCriticalDamage(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $defendUnit = UnitFactory::createByTemplate(2);
        $defendCommand = CommandFactory::create([$defendUnit]);
        $alliesCommand = CommandFactory::create([$unit]);
        $action = new WaitAction($this->getContainer(), $unit, $defendCommand, $alliesCommand);

        $this->expectException(ActionException::class);
        $this->expectExceptionMessage('Action: No method: Battle\Action\AbstractAction::Battle\Action\AbstractAction::isCriticalDamage');
        $action->isCriticalDamage();
    }

    /**
     * Тест на ситуацию, когда у не-DamageAction вызывается метод getRestoreLifeFromVampirism()
     *
     * @throws Exception
     */
    public function testActionGetRestoreLifeFromVampirism(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $defendUnit = UnitFactory::createByTemplate(2);
        $defendCommand = CommandFactory::create([$defendUnit]);
        $alliesCommand = CommandFactory::create([$unit]);
        $action = new WaitAction($this->getContainer(), $unit, $defendCommand, $alliesCommand);

        $this->expectException(ActionException::class);
        $this->expectExceptionMessage('Action: No method: Battle\Action\AbstractAction::Battle\Action\AbstractAction::getRestoreLifeFromVampirism');
        $action->getRestoreLifeFromVampirism();
    }

    /**
     * Тест на ситуацию, когда у не-DamageAction вызывается метод getRestoreManaFromMagicVampirism()
     *
     * @throws Exception
     */
    public function testActionGetRestoreManaFromMagicVampirism(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $defendUnit = UnitFactory::createByTemplate(2);
        $defendCommand = CommandFactory::create([$defendUnit]);
        $alliesCommand = CommandFactory::create([$unit]);
        $action = new WaitAction($this->getContainer(), $unit, $defendCommand, $alliesCommand);

        $this->expectException(ActionException::class);
        $this->expectExceptionMessage('Action: No method: Battle\Action\AbstractAction::Battle\Action\AbstractAction::getRestoreManaFromMagicVampirism');
        $action->getRestoreManaFromMagicVampirism();
    }

    /**
     * Тест на ситуацию, когда у не-DamageAction вызывается метод getDamageMultiplier()
     *
     * @throws Exception
     */
    public function testActionGetRandomDamageMultiplier(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $defendUnit = UnitFactory::createByTemplate(2);
        $defendCommand = CommandFactory::create([$defendUnit]);
        $alliesCommand = CommandFactory::create([$unit]);
        $action = new WaitAction($this->getContainer(), $unit, $defendCommand, $alliesCommand);

        $this->expectException(ActionException::class);
        $this->expectExceptionMessage('Action: No method: Battle\Action\AbstractAction::Battle\Action\AbstractAction::getRandomDamageMultiplier');
        $action->getRandomDamageMultiplier();
    }

    /**
     * Тест на изменение $actionUnit в Action
     *
     * @throws Exception
     */
    public function testActionChangeActionUnit(): void
    {
        [$unit, $command, $enemyCommand, $enemyUnit] = BaseFactory::create(1, 2);

        $action = $this->createDamageAction($unit, $enemyCommand, $command);

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

        $action = $this->createDamageAction($unit, $enemyCommand, $command);

        $cloneAction = clone $action;

        self::assertEquals($unit, $action->getActionUnit());
        self::assertEquals($unit, $cloneAction->getActionUnit());

        $action->changeActionUnit($enemyUnit);

        self::assertEquals($enemyUnit, $action->getActionUnit());
        self::assertEquals($unit, $cloneAction->getActionUnit());
    }

    /**
     * Проверка метода targetTracking(), который по-умолчанию возвращает true
     *
     * @throws Exception
     */
    public function testActionTargetTracking(): void
    {
        [$unit, $command, $enemyCommand] = BaseFactory::create(1, 2);

        $action = $this->createDamageAction($unit, $enemyCommand, $command);

        self::assertTrue($action->isTargetTracking());
    }

    /**
     * @param UnitInterface $unit
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $command
     * @return DamageAction
     * @throws Exception
     */
    private function createDamageAction(
        UnitInterface $unit,
        CommandInterface $enemyCommand,
        CommandInterface $command
    ): DamageAction
    {
        return new DamageAction(
            $this->container,
            $unit,
            $enemyCommand,
            $command,
            DamageAction::TARGET_RANDOM_ENEMY,
            true,
            DamageAction::DEFAULT_NAME,
            DamageAction::UNIT_ANIMATION_METHOD,
            DamageAction::DEFAULT_MESSAGE_METHOD,
            $unit->getOffense()
        );
    }
}
