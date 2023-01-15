<?php

declare(strict_types=1);

namespace Tests\Battle\Action;

use Battle\Action\ActionException;
use Battle\Action\ActionInterface;
use Battle\Action\BuffAction;
use Battle\Command\CommandException;
use Battle\Command\CommandFactory;
use Battle\Unit\UnitException;
use Tests\AbstractUnitTest;
use Tests\Factory\UnitFactory;
use Tests\Factory\UnitFactoryException;

class BuffActionTest extends AbstractUnitTest
{
    /**
     * Тест на баф, который увеличит здоровье юнита на 30%, а потом откат изменения
     *
     * @throws CommandException
     * @throws UnitException
     * @throws UnitFactoryException
     * @throws ActionException
     */
    public function testBuffActionMaximumLifeSuccess(): void
    {
        $name = 'use Reserve Forces';
        $modifyMethod = 'multiplierMaxLife';
        $power = 130;

        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $oldLife = $unit->getTotalLife();

        $action = new BuffAction($this->getContainer(), $unit, $enemyCommand, $command, BuffAction::TARGET_SELF, $name, $modifyMethod, $power);

        self::assertEquals(BuffAction::SKIP_ANIMATION_METHOD, $action->getAnimationMethod());
        self::assertEquals('buff', $action->getMessageMethod());

        $multiplier = $power / 100;
        $newLife = (int)($unit->getTotalLife() * $multiplier);

        // BuffAction всегда готов примениться (а EffectAction - только если аналогичный эффект на юните отсутствует)
        self::assertTrue($action->canByUsed());

        // Применяем баф
        $action->handle();

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
     * @throws CommandException
     * @throws UnitException
     * @throws UnitFactoryException
     * @throws ActionException
     */
    public function testBuffActionMaximumLifeReduced(): void
    {
        $name = 'use Reserve Forces';
        $modifyMethod = 'multiplierMaxLife';
        $power = 50;

        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = new BuffAction($this->getContainer(), $unit, $enemyCommand, $command, BuffAction::TARGET_SELF, $name, $modifyMethod, $power);

        $this->expectException(UnitException::class);
        $this->expectErrorMessage(UnitException::NO_REDUCED_MAXIMUM_LIFE);
        $action->handle();
    }

    /**
     * Тест на увеличение скорости атаки юнита
     *
     * @throws ActionException
     * @throws CommandException
     * @throws UnitException
     * @throws UnitFactoryException
     */
    public function testBuffActionAttackSpeedSuccess(): void
    {
        $name = 'use Battle Fury';
        $modifyMethod = 'multiplierAttackSpeed';
        $power = 125;

        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $oldAttackSpeed = $unit->getOffense()->getAttackSpeed();

        $action = new BuffAction($this->getContainer(), $unit, $enemyCommand, $command, BuffAction::TARGET_SELF, $name, $modifyMethod, $power);

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
     * @throws ActionException
     * @throws CommandException
     * @throws UnitException
     * @throws UnitFactoryException
     */
    public function testBuffActionAttackSpeedReduced(): void
    {
        $name = 'use Battle Fury';
        $modifyMethod = 'multiplierAttackSpeed';
        $power = 50;

        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = new BuffAction($this->getContainer(), $unit, $enemyCommand, $command, BuffAction::TARGET_SELF, $name, $modifyMethod, $power);

        $this->expectException(UnitException::class);
        $this->expectErrorMessage(UnitException::NO_REDUCED_ATTACK_SPEED);
        $action->handle();
    }

    /**
     * Тест на ситуацию, когда указан неизвестный метод модификации характеристики
     *
     * @throws CommandException
     * @throws UnitException
     * @throws UnitFactoryException
     * @throws ActionException
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
     * @throws ActionException
     * @throws CommandException
     * @throws UnitException
     * @throws UnitFactoryException
     */
    public function testBuffActionNoTargetForBuff(): void
    {
        $name = 'use Reserve Forces';
        $modifyMethod = 'multiplierMaxLife';
        $power = 130;

        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(10);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        // Цель бафа - случайный противник, но противник мертв
        $action = new BuffAction($this->getContainer(), $unit, $enemyCommand, $command, BuffAction::TARGET_RANDOM_ENEMY, $name, $modifyMethod, $power);

        // Применяем баф и получаем исключение - нет цели для применения бафа

        $this->expectException(ActionException::class);
        $this->expectExceptionMessage(ActionException::NO_TARGET_FOR_BUFF);
        $action->handle();
    }
}
