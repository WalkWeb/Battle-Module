<?php

declare(strict_types=1);

namespace Tests\Battle\Action;

use Battle\Action\ActionCollection;
use Battle\Action\ActionException;
use Battle\Action\ActionInterface;
use Battle\Action\BuffAction;
use Battle\Command\CommandFactory;
use Battle\Unit\UnitException;
use Exception;
use Tests\AbstractUnitTest;
use Tests\Factory\UnitFactory;

class BuffActionTest extends AbstractUnitTest
{
    /**
     * Тест на баф, который увеличит здоровье юнита на 30%, а потом откат изменения
     *
     * @throws Exception
     */
    public function testBuffActionMaximumLifeSuccess(): void
    {
        $name = 'use Reserve Forces';
        $power = 130;

        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $oldLife = $unit->getTotalLife();

        $action = new BuffAction(
            $this->getContainer(),
            $unit,
            $enemyCommand,
            $command,
            BuffAction::TARGET_SELF,
            $name,
            BuffAction::MAX_LIFE,
            $power
        );

        self::assertEquals(BuffAction::SKIP_ANIMATION_METHOD, $action->getAnimationMethod());
        self::assertEquals('buff', $action->getMessageMethod());

        $multiplier = $power / 100;
        $newLife = (int)($unit->getTotalLife() * $multiplier);

        // BuffAction всегда готов примениться (а EffectAction - только если аналогичный эффект на юните отсутствует)
        self::assertTrue($action->canByUsed());

        // Применяем баф
        $callbackActions = $action->handle();

        self::assertEquals(new ActionCollection(), $callbackActions);

        self::assertEquals($newLife, $unit->getTotalLife());
        self::assertEquals($newLife, $unit->getLife());

        // Откат изменения
        $action->getRevertAction()->handle();

        self::assertEquals($oldLife, $unit->getTotalLife());
        self::assertEquals($oldLife, $unit->getLife());
    }

    /**
     * Тест на попытку уменьшения здоровья - пока такой вариант не допустим (нужно проработать отдельно)
     *
     * @throws Exception
     */
    public function testBuffActionMaximumLifeReduced(): void
    {
        $name = 'use Reserve Forces';
        $power = 50;

        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = new BuffAction(
            $this->getContainer(),
            $unit,
            $enemyCommand,
            $command,
            BuffAction::TARGET_SELF,
            $name,
            BuffAction::MAX_LIFE,
            $power
        );

        $this->expectException(UnitException::class);
        $this->expectErrorMessage(UnitException::NO_REDUCED_MAXIMUM_LIFE);
        $action->handle();
    }

    /**
     * Тест на увеличение скорости атаки юнита
     *
     * @throws Exception
     */
    public function testBuffActionAttackSpeedSuccess(): void
    {
        $name = 'use Battle Fury';
        $power = 125;

        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $oldAttackSpeed = $unit->getOffense()->getAttackSpeed();

        $action = new BuffAction(
            $this->getContainer(),
            $unit,
            $enemyCommand,
            $command,
            BuffAction::TARGET_SELF,
            $name,
            BuffAction::ATTACK_SPEED,
            $power
        );

        self::assertEquals(ActionInterface::SKIP_ANIMATION_METHOD, $action->getAnimationMethod());
        self::assertEquals('buff', $action->getMessageMethod());

        $multiplier = $power / 100;
        $newAttackSpeed = $unit->getOffense()->getAttackSpeed() * $multiplier;

        // BuffAction всегда готов примениться (а EffectAction - только если аналогичный эффект на юните отсутствует)
        self::assertTrue($action->canByUsed());

        // Применяем баф
        $action->handle();

        self::assertEquals($newAttackSpeed, $unit->getOffense()->getAttackSpeed());

        // Откат изменения
        $action->getRevertAction()->handle();

        self::assertEquals($oldAttackSpeed, $unit->getOffense()->getAttackSpeed());
    }

    /**
     * Тест на уменьшение скорости атаки - такая механика пока недоступна
     *
     * @throws Exception
     */
    public function testBuffActionAttackSpeedReduced(): void
    {
        $name = 'use Battle Fury';
        $power = 50;

        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = new BuffAction(
            $this->getContainer(),
            $unit,
            $enemyCommand,
            $command,
            BuffAction::TARGET_SELF,
            $name,
            BuffAction::ATTACK_SPEED,
            $power
        );

        $this->expectException(UnitException::class);
        $this->expectErrorMessage(UnitException::NO_REDUCED_ATTACK_SPEED);
        $action->handle();
    }

    /**
     * Тест на увеличение меткости
     *
     * @throws Exception
     */
    public function testBuffActionIncreasedAccuracySuccess(): void
    {
        $name = 'increased accuracy';
        $power = 150;

        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = new BuffAction(
            $this->getContainer(),
            $unit,
            $enemyCommand,
            $command,
            BuffAction::TARGET_SELF,
            $name,
            BuffAction::ACCURACY,
            $power
        );

        $oldAccuracy = $unit->getOffense()->getAccuracy();

        $multiplier = $power / 100;
        $newAccuracy = $unit->getOffense()->getAccuracy() * $multiplier;

        // Применяем баф
        self::assertTrue($action->canByUsed());
        $action->handle();

        self::assertEquals($newAccuracy, $unit->getOffense()->getAccuracy());

        // Откатываем баф
        $action->getRevertAction()->handle();
        self::assertEquals($oldAccuracy, $unit->getOffense()->getAccuracy());
    }

    /**
     * Тест на уменьшение меткости (пока такая возможность недоступна)
     *
     * @throws Exception
     */
    public function testBuffActionReducedAccuracy(): void
    {
        $name = 'increased accuracy';
        $power = 50;

        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = new BuffAction(
            $this->getContainer(),
            $unit,
            $enemyCommand,
            $command,
            BuffAction::TARGET_SELF,
            $name,
            BuffAction::ACCURACY,
            $power
        );

        $this->expectException(UnitException::class);
        $this->expectErrorMessage(UnitException::NO_REDUCED_ACCURACY);
        $action->handle();
    }

    /**
     * Тест на ситуацию, когда указан неизвестный метод модификации характеристики
     *
     * @throws Exception
     */
    public function testBuffActionUndefinedModifyMethod(): void
    {
        $name = 'use Reserve Forces';
        $modifyMethod = 'undefinedModifyMethod';
        $power = 200;

        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = new BuffAction($this->getContainer(), $unit, $enemyCommand, $command, BuffAction::TARGET_SELF, $name, $modifyMethod, $power);

        $this->expectException(UnitException::class);
        $this->expectErrorMessage(UnitException::UNDEFINED_MODIFY_METHOD . ': ' . $modifyMethod);
        $action->handle();
    }

    /**
     * @throws Exception
     */
    public function testBuffActionNoTargetForBuff(): void
    {
        $name = 'use Reserve Forces';
        $power = 130;

        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(10);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        // Цель бафа - случайный противник, но противник мертв
        $action = new BuffAction(
            $this->getContainer(),
            $unit,
            $enemyCommand,
            $command,
            BuffAction::TARGET_RANDOM_ENEMY,
            $name,
            BuffAction::MAX_LIFE,
            $power
        );

        // Применяем баф и получаем исключение - нет цели для применения бафа

        $this->expectException(ActionException::class);
        $this->expectExceptionMessage(ActionException::NO_TARGET_FOR_BUFF);
        $action->handle();
    }
}
