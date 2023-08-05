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
            'use Reserve Forces',
            BuffAction::MAX_LIFE,
            50
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
            'use Battle Fury',
            BuffAction::ATTACK_SPEED,
            50
        );

        $this->expectException(UnitException::class);
        $this->expectErrorMessage(UnitException::NO_REDUCED_ATTACK_SPEED);
        $action->handle();
    }

    /**
     * Тест на увеличение/уменьшение меткости
     *
     * @dataProvider multiplierAccuracyDataProvider
     * @param int $power
     * @param int $newAccuracy
     * @throws Exception
     */
    public function testBuffActionMultiplierAccuracySuccess(int $power, int $newAccuracy): void
    {
        $unit = UnitFactory::createByTemplate(12);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = new BuffAction(
            $this->getContainer(),
            $unit,
            $enemyCommand,
            $command,
            BuffAction::TARGET_SELF,
            'multiplier accuracy',
            BuffAction::ACCURACY,
            $power
        );

        // Изначальная меткость
        self::assertEquals(213, $unit->getOffense()->getAccuracy());

        $oldAccuracy = $unit->getOffense()->getAccuracy();

        // Применяем баф
        self::assertTrue($action->canByUsed());
        $action->handle();

        // Проверяем обновленную меткость
        self::assertEquals($newAccuracy, $unit->getOffense()->getAccuracy());

        // Проверяем обновленную меткость от множителя (на всякий случай)
        self::assertEquals((int)($oldAccuracy * ($power / 100)), $unit->getOffense()->getAccuracy());

        // Откатываем баф и проверяем, что меткость вернулась к исходной
        $action->getRevertAction()->handle();
        self::assertEquals(213, $unit->getOffense()->getAccuracy());
    }

    /**
     * Тест на чрезмерное уменьшение меткости
     *
     * @throws Exception
     */
    public function testBuffActionOverReducedAccuracy(): void
    {
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
            'reduced accuracy',
            BuffAction::ACCURACY,
            5
        );

        $this->expectException(UnitException::class);
        $this->expectErrorMessage(UnitException::OVER_REDUCED . BuffAction::MIN_MULTIPLIER);
        $action->handle();
    }

    /**
     * Тест на увеличение/уменьшение магической меткости
     *
     * @dataProvider multiplierMagicAccuracyDataProvider
     * @param int $power
     * @param int $newAccuracy
     * @throws Exception
     */
    public function testBuffActionMultiplierMagicAccuracySuccess(int $power, int $newAccuracy): void
    {
        $unit = UnitFactory::createByTemplate(12);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = new BuffAction(
            $this->getContainer(),
            $unit,
            $enemyCommand,
            $command,
            BuffAction::TARGET_SELF,
            'multiplier magic accuracy',
            BuffAction::MAGIC_ACCURACY,
            $power
        );

        // Изначальная меткость
        self::assertEquals(114, $unit->getOffense()->getMagicAccuracy());

        $oldAccuracy = $unit->getOffense()->getMagicAccuracy();

        // Применяем баф
        self::assertTrue($action->canByUsed());
        $action->handle();

        // Проверяем обновленную меткость
        self::assertEquals($newAccuracy, $unit->getOffense()->getMagicAccuracy());

        // Проверяем обновленную меткость от множителя (на всякий случай)
        self::assertEquals((int)($oldAccuracy * ($power / 100)), $unit->getOffense()->getMagicAccuracy());

        // Откатываем баф и проверяем, что меткость вернулась к исходной
        $action->getRevertAction()->handle();
        self::assertEquals(114, $unit->getOffense()->getMagicAccuracy());
    }

    /**
     * Тест на чрезмерное уменьшение магической меткости
     *
     * @throws Exception
     */
    public function testBuffActionOverReducedMagicAccuracy(): void
    {
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
            'reduced magic accuracy',
            BuffAction::MAGIC_ACCURACY,
            5
        );

        $this->expectException(UnitException::class);
        $this->expectErrorMessage(UnitException::OVER_REDUCED . BuffAction::MIN_MULTIPLIER);
        $action->handle();
    }

    /**
     * Тест на увеличение/уменьшение защиты
     *
     * @dataProvider multiplierDefenseDataProvider
     * @param int $power
     * @param int $newDefense
     * @throws Exception
     */
    public function testBuffActionMultiplierDefenseSuccess(int $power, int $newDefense): void
    {
        $unit = UnitFactory::createByTemplate(12);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = new BuffAction(
            $this->getContainer(),
            $unit,
            $enemyCommand,
            $command,
            BuffAction::TARGET_SELF,
            'multiplier defense',
            BuffAction::DEFENSE,
            $power
        );

        // Изначальная защита
        self::assertEquals(275, $unit->getDefense()->getDefense());

        $oldDefense = $unit->getDefense()->getDefense();

        // Применяем баф
        self::assertTrue($action->canByUsed());
        $action->handle();

        // Проверяем обновленную защиту
        self::assertEquals($newDefense, $unit->getDefense()->getDefense());

        // Проверяем обновленную защиту от множителя (на всякий случай)
        self::assertEquals((int)($oldDefense * ($power / 100)), $unit->getDefense()->getDefense());

        // Откатываем баф и проверяем, что защита вернулась к исходной
        $action->getRevertAction()->handle();
        self::assertEquals(275, $unit->getDefense()->getDefense());
    }

    /**
     * Тест на чрезмерное уменьшение защиты
     *
     * @throws Exception
     */
    public function testBuffActionOverReducedDefense(): void
    {
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
            'reduced defense',
            BuffAction::DEFENSE,
            5
        );

        $this->expectException(UnitException::class);
        $this->expectErrorMessage(UnitException::OVER_REDUCED . BuffAction::MIN_MULTIPLIER);
        $action->handle();
    }

    /**
     * Тест на увеличение/уменьшение магической защиты
     *
     * @dataProvider multiplierMagicDefenseDataProvider
     * @param int $power
     * @param int $newDefense
     * @throws Exception
     */
    public function testBuffActionMultiplierMagicDefenseSuccess(int $power, int $newDefense): void
    {
        $unit = UnitFactory::createByTemplate(12);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = new BuffAction(
            $this->getContainer(),
            $unit,
            $enemyCommand,
            $command,
            BuffAction::TARGET_SELF,
            'multiplier magic defense',
            BuffAction::MAGIC_DEFENSE,
            $power
        );

        // Изначальная магическая защита
        self::assertEquals(131, $unit->getDefense()->getMagicDefense());

        $oldMagicDefense = $unit->getDefense()->getMagicDefense();

        // Применяем баф
        self::assertTrue($action->canByUsed());
        $action->handle();

        // Проверяем обновленную магическую защиту
        self::assertEquals($newDefense, $unit->getDefense()->getMagicDefense());

        // Проверяем обновленную магическую защиту от множителя (на всякий случай)
        self::assertEquals((int)($oldMagicDefense * ($power / 100)), $unit->getDefense()->getMagicDefense());

        // Откатываем баф и проверяем, что магическая защита вернулась к исходной
        $action->getRevertAction()->handle();
        self::assertEquals(131, $unit->getDefense()->getMagicDefense());
    }

    /**
     * Тест на чрезмерное уменьшение магической защиты
     *
     * @throws Exception
     */
    public function testBuffActionOverReducedMagicDefense(): void
    {
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
            'reduced magic defense',
            BuffAction::MAGIC_DEFENSE,
            5
        );

        $this->expectException(UnitException::class);
        $this->expectErrorMessage(UnitException::OVER_REDUCED . BuffAction::MIN_MULTIPLIER);
        $action->handle();
    }

    /**
     * Тест на увеличение/уменьшение магической защиты
     *
     * @dataProvider multiplierCriticalChanceDataProvider
     * @param int $power
     * @param int $newCriticalChance
     * @throws Exception
     */
    public function testBuffActionMultiplierCriticalChanceSuccess(int $power, int $newCriticalChance): void
    {
        $unit = UnitFactory::createByTemplate(12);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = new BuffAction(
            $this->getContainer(),
            $unit,
            $enemyCommand,
            $command,
            BuffAction::TARGET_SELF,
            'multiplier critical chance',
            BuffAction::CRITICAL_CHANCE,
            $power
        );

        // Изначальный шанс критического удара
        self::assertEquals(15, $unit->getOffense()->getCriticalChance());

        $oldCriticalChance = $unit->getOffense()->getCriticalChance();

        // Применяем баф
        self::assertTrue($action->canByUsed());
        $action->handle();

        // Проверяем обновленный шанс критического удара
        self::assertEquals($newCriticalChance, $unit->getOffense()->getCriticalChance());

        // Проверяем обновленный шанс критического удара от множителя (на всякий случай)
        self::assertEquals((int)($oldCriticalChance * ($power / 100)), $unit->getOffense()->getCriticalChance());

        // Откатываем баф и проверяем, что шанс критического удара вернулся к исходному значению
        $action->getRevertAction()->handle();
        self::assertEquals(15, $unit->getOffense()->getCriticalChance());
    }

    /**
     * Тест на чрезмерное уменьшение магической защиты
     *
     * @throws Exception
     */
    public function testBuffActionOverReducedCriticalChance(): void
    {
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
            'multiplier critical chance',
            BuffAction::CRITICAL_CHANCE,
            5
        );

        $this->expectException(UnitException::class);
        $this->expectErrorMessage(UnitException::OVER_REDUCED . BuffAction::MIN_MULTIPLIER);
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

    /**
     * @return array
     */
    public function multiplierAccuracyDataProvider(): array
    {
        return [
            [
                200,
                426,
            ],
            [
                111,
                236,
            ],
            [
                87,
                185,
            ],
            [
                32,
                68,
            ],
        ];
    }

    /**
     * @return array
     */
    public function multiplierMagicAccuracyDataProvider(): array
    {
        return [
            [
                200,
                228,
            ],
            [
                111,
                126,
            ],
            [
                87,
                99,
            ],
            [
                32,
                36,
            ],
        ];
    }

    /**
     * @return array
     */
    public function multiplierDefenseDataProvider(): array
    {
        return [
            [
                200,
                550,
            ],
            [
                111,
                305,
            ],
            [
                87,
                239,
            ],
            [
                32,
                88,
            ],
        ];
    }

    /**
     * @return array
     */
    public function multiplierMagicDefenseDataProvider(): array
    {
        return [
            [
                200,
                262,
            ],
            [
                111,
                145,
            ],
            [
                87,
                113,
            ],
            [
                32,
                41,
            ],
        ];
    }

    /**
     * @return array
     */
    public function multiplierCriticalChanceDataProvider(): array
    {
        return [
            [
                200,
                30,
            ],
            [
                111,
                16,
            ],
            [
                87,
                13,
            ],
            [
                32,
                4,
            ],
        ];
    }
}
