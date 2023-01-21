<?php

declare(strict_types=1);

namespace Tests\Battle\Action;

use Battle\Action\ActionCollection;
use Battle\Action\ActionException;
use Battle\Action\ManaRestoreAction;
use Battle\Command\CommandFactory;
use Exception;
use Tests\AbstractUnitTest;
use Tests\Factory\UnitFactory;

class ManaRestoreActionTest extends AbstractUnitTest
{
    /**
     * Тест на успешное создание ManaRestoreAction
     *
     * @throws Exception
     */
    public function testManaRestoreActionCreateSuccess(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(3);
        $alliesCommand = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $power = 20;

        $action = new ManaRestoreAction(
            $this->getContainer(),
            $unit,
            $enemyCommand,
            $alliesCommand,
            ManaRestoreAction::TARGET_SELF,
            $power,
            ManaRestoreAction::NAME,
            ManaRestoreAction::SKIP_ANIMATION_METHOD,
            ManaRestoreAction::SKIP_MESSAGE_METHOD
        );

        self::assertEquals('applyManaRestoreAction', $action->getHandleMethod());
        self::assertEquals($power, $action->getPower());
        self::assertEquals(0, $action->getFactualPower());
        self::assertEquals(ManaRestoreAction::NAME, $action->getNameAction());
        self::assertEquals(ManaRestoreAction::SKIP_ANIMATION_METHOD, $action->getAnimationMethod());
        self::assertEquals(ManaRestoreAction::SKIP_MESSAGE_METHOD, $action->getMessageMethod());
        self::assertTrue($action->canByUsed());
    }

    /**
     * Тест на ситуацию, когда передан некорректный тип цели
     *
     * @throws Exception
     */
    public function testManaRestoreActionCreateInvalidTypeTarget(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(3);
        $alliesCommand = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $this->expectException(ActionException::class);
        $this->expectExceptionMessage(ActionException::INVALID_MANA_RESTORE_TARGET);
        new ManaRestoreAction(
            $this->getContainer(),
            $unit,
            $enemyCommand,
            $alliesCommand,
            ManaRestoreAction::TARGET_WOUNDED_ALLIES,
            20,
            ManaRestoreAction::NAME,
            ManaRestoreAction::SKIP_ANIMATION_METHOD,
            ManaRestoreAction::SKIP_MESSAGE_METHOD
        );
    }

    /**
     * Тест на применение ManaRestoreAction
     *
     * @throws Exception
     */
    public function testManaRestoreActionApply(): void
    {
        $unit = UnitFactory::createByTemplate(2);
        $enemyUnit = UnitFactory::createByTemplate(3);
        $alliesCommand = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        // Проверяем, что у юнита не максимальная мана
        self::assertEquals(20, $unit->getMana());
        self::assertEquals(50, $unit->getTotalMana());

        $power = 20;

        $action = new ManaRestoreAction(
            $this->getContainer(),
            $unit,
            $enemyCommand,
            $alliesCommand,
            ManaRestoreAction::TARGET_SELF,
            $power,
            ManaRestoreAction::NAME,
            ManaRestoreAction::SKIP_ANIMATION_METHOD,
            ManaRestoreAction::SKIP_MESSAGE_METHOD
        );

        // Применяем способность
        $callbackActions = $action->handle();

        self::assertEquals(new ActionCollection(), $callbackActions);

        // Проверяем, что мана восстановилась
        self::assertEquals(20 + $power, $unit->getMana());
        self::assertEquals(50, $unit->getTotalMana());

        // Проверяем фактическую силу
        self::assertEquals($power, $action->getFactualPower());
        self::assertEquals($power, $action->getFactualPowerByUnit($unit));

        // Создаем и применяем способность еще раз
        $action = new ManaRestoreAction(
            $this->getContainer(),
            $unit,
            $enemyCommand,
            $alliesCommand,
            ManaRestoreAction::TARGET_SELF,
            $power,
            ManaRestoreAction::NAME,
            ManaRestoreAction::SKIP_ANIMATION_METHOD,
            ManaRestoreAction::SKIP_MESSAGE_METHOD
        );

        $action->handle();

        // Проверяем, что мана восстановилась полностью
        self::assertEquals(50, $unit->getMana());
        self::assertEquals(50, $unit->getTotalMana());

        // Проверяем фактическую силу, на этот раз она будет 10 - столько оставалось до максимума
        self::assertEquals(10, $action->getFactualPower());
        self::assertEquals(10, $action->getFactualPowerByUnit($unit));
    }

    /**
     * Тест на ситуацию, когда нет данных о фактически восстановленной мане по указанному юниту
     *
     * @throws Exception
     */
    public function testManaRestoreActionNoPowerByUnit(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(3);
        $alliesCommand = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = new ManaRestoreAction(
            $this->getContainer(),
            $unit,
            $enemyCommand,
            $alliesCommand,
            ManaRestoreAction::TARGET_SELF,
            20,
            ManaRestoreAction::NAME,
            ManaRestoreAction::SKIP_ANIMATION_METHOD,
            ManaRestoreAction::SKIP_MESSAGE_METHOD
        );

        $this->expectException(ActionException::class);
        $this->expectExceptionMessage(ActionException::NO_POWER_BY_UNIT . ': ' . $enemyUnit->getId());
        $action->getFactualPowerByUnit($enemyUnit);
    }
}
