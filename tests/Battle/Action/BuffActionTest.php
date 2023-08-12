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
     * Тест на увеличение/уменьшение шанса критического удара
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
     * Тест на увеличение/уменьшение силы критического удара
     *
     * @dataProvider multiplierCriticalMultiplierDataProvider
     * @param int $power
     * @param int $newCriticalMultiplier
     * @throws Exception
     */
    public function testBuffActionMultiplierCriticalMultiplierSuccess(int $power, int $newCriticalMultiplier): void
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
            'multiplier critical multiplier',
            BuffAction::CRITICAL_MULTIPLIER,
            $power
        );

        // Изначальная сила критического удара
        self::assertEquals(200, $unit->getOffense()->getCriticalMultiplier());

        $oldCriticalMultiplier = $unit->getOffense()->getCriticalMultiplier();

        // Применяем баф
        self::assertTrue($action->canByUsed());
        $action->handle();

        // Проверяем обновленную силу критического удара
        self::assertEquals($newCriticalMultiplier, $unit->getOffense()->getCriticalMultiplier());

        // Проверяем обновленную силу критического удара от множителя (на всякий случай)
        self::assertEquals((int)($oldCriticalMultiplier * ($power / 100)), $unit->getOffense()->getCriticalMultiplier());

        // Откатываем баф и проверяем, что сила критического удара вернулась к исходному значению
        $action->getRevertAction()->handle();
        self::assertEquals(200, $unit->getOffense()->getCriticalMultiplier());
    }

    /**
     * Тест на увеличение/уменьшение урона огнем
     *
     * @dataProvider multiplierFireDamageDataProvider
     * @param int $power
     * @param int $newFireDamage
     * @throws Exception
     */
    public function testBuffActionMultiplierFireDamageSuccess(int $power, int $newFireDamage): void
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
            'multiplier fire damage',
            BuffAction::FIRE_DAMAGE,
            $power
        );

        // Изначальный урон огнем
        self::assertEquals(65, $unit->getOffense()->getFireDamage());

        $oldFireDamage = $unit->getOffense()->getFireDamage();

        // Применяем баф
        self::assertTrue($action->canByUsed());
        $action->handle();

        // Проверяем обновленный урон огнем
        self::assertEquals($newFireDamage, $unit->getOffense()->getFireDamage());

        // Проверяем обновленный урон огнем от множителя (на всякий случай)
        self::assertEquals((int)($oldFireDamage * ($power / 100)), $unit->getOffense()->getFireDamage());

        // Откатываем баф и проверяем, что урон огнем вернулся к исходному значению
        $action->getRevertAction()->handle();
        self::assertEquals(65, $unit->getOffense()->getFireDamage());
    }

    /**
     * Тест на увеличение/уменьшение урона водой
     *
     * @dataProvider multiplierWaterDamageDataProvider
     * @param int $power
     * @param int $newWaterDamage
     * @throws Exception
     */
    public function testBuffActionMultiplierWaterDamageSuccess(int $power, int $newWaterDamage): void
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
            'multiplier water damage',
            BuffAction::WATER_DAMAGE,
            $power
        );

        // Изначальный урон водой
        self::assertEquals(87, $unit->getOffense()->getWaterDamage());

        $oldWaterDamage = $unit->getOffense()->getWaterDamage();

        // Применяем баф
        self::assertTrue($action->canByUsed());
        $action->handle();

        // Проверяем обновленный урон водой
        self::assertEquals($newWaterDamage, $unit->getOffense()->getWaterDamage());

        // Проверяем обновленный урон водой от множителя (на всякий случай)
        self::assertEquals((int)($oldWaterDamage * ($power / 100)), $unit->getOffense()->getWaterDamage());

        // Откатываем баф и проверяем, что урон водой вернулся к исходному значению
        $action->getRevertAction()->handle();
        self::assertEquals(87, $unit->getOffense()->getWaterDamage());
    }

    /**
     * Тест на увеличение/уменьшение урона воздухом
     *
     * @dataProvider multiplierAirDamageDataProvider
     * @param int $power
     * @param int $newAirDamage
     * @throws Exception
     */
    public function testBuffActionMultiplierAirDamageSuccess(int $power, int $newAirDamage): void
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
            'multiplier air damage',
            BuffAction::AIR_DAMAGE,
            $power
        );

        // Изначальный урон воздухом
        self::assertEquals(54, $unit->getOffense()->getAirDamage());

        $oldAirDamage = $unit->getOffense()->getAirDamage();

        // Применяем баф
        self::assertTrue($action->canByUsed());
        $action->handle();

        // Проверяем обновленный урон воздухом
        self::assertEquals($newAirDamage, $unit->getOffense()->getAirDamage());

        // Проверяем обновленный урон воздухом от множителя (на всякий случай)
        self::assertEquals((int)($oldAirDamage * ($power / 100)), $unit->getOffense()->getAirDamage());

        // Откатываем баф и проверяем, что урон воздухом вернулся к исходному значению
        $action->getRevertAction()->handle();
        self::assertEquals(54, $unit->getOffense()->getAirDamage());
    }

    /**
     * Тест на увеличение/уменьшение урона землей
     *
     * @dataProvider multiplierEarthDamageDataProvider
     * @param int $power
     * @param int $newEarthDamage
     * @throws Exception
     */
    public function testBuffActionMultiplierEarthDamageSuccess(int $power, int $newEarthDamage): void
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
            'multiplier earth damage',
            BuffAction::EARTH_DAMAGE,
            $power
        );

        // Изначальный урон землей
        self::assertEquals(63, $unit->getOffense()->getEarthDamage());

        $oldEarthDamage = $unit->getOffense()->getEarthDamage();

        // Применяем баф
        self::assertTrue($action->canByUsed());
        $action->handle();

        // Проверяем обновленный урон землей
        self::assertEquals($newEarthDamage, $unit->getOffense()->getEarthDamage());

        // Проверяем обновленный урон землей от множителя (на всякий случай)
        self::assertEquals((int)($oldEarthDamage * ($power / 100)), $unit->getOffense()->getEarthDamage());

        // Откатываем баф и проверяем, что урон землей вернулся к исходному значению
        $action->getRevertAction()->handle();
        self::assertEquals(63, $unit->getOffense()->getEarthDamage());
    }

    /**
     * Тест на чрезмерное уменьшение характеристики
     *
     * @dataProvider overReducedStatDataProvider
     * @param string $modifyMethod
     * @throws Exception
     */
    public function testBuffActionOverReducedStat(string $modifyMethod): void
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
            'OverReducedStat',
            $modifyMethod,
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

    /**
     * @return array
     */
    public function multiplierCriticalMultiplierDataProvider(): array
    {
        return [
            [
                200,
                400,
            ],
            [
                111,
                222,
            ],
            [
                87,
                174,
            ],
            [
                32,
                64,
            ],
        ];
    }

    /**
     * @return array
     */
    public function multiplierFireDamageDataProvider(): array
    {
        return [
            [
                200,
                130,
            ],
            [
                111,
                72,
            ],
            [
                87,
                56,
            ],
            [
                32,
                20,
            ],
        ];
    }

    /**
     * @return array
     */
    public function multiplierWaterDamageDataProvider(): array
    {
        return [
            [
                200,
                174,
            ],
            [
                111,
                96,
            ],
            [
                87,
                75,
            ],
            [
                32,
                27,
            ],
        ];
    }

    /**
     * @return array
     */
    public function multiplierAirDamageDataProvider(): array
    {
        return [
            [
                200,
                108,
            ],
            [
                111,
                59,
            ],
            [
                87,
                46,
            ],
            [
                32,
                17,
            ],
        ];
    }

    /**
     * @return array
     */
    public function multiplierEarthDamageDataProvider(): array
    {
        return [
            [
                200,
                126,
            ],
            [
                111,
                69,
            ],
            [
                87,
                54,
            ],
            [
                32,
                20,
            ],
        ];
    }

    public function overReducedStatDataProvider(): array
    {
        return [
            [BuffAction::FIRE_DAMAGE],
            [BuffAction::WATER_DAMAGE],
            [BuffAction::AIR_DAMAGE],
            [BuffAction::EARTH_DAMAGE],
            [BuffAction::CRITICAL_MULTIPLIER],
            [BuffAction::CRITICAL_CHANCE],
            [BuffAction::MAGIC_DEFENSE],
            [BuffAction::DEFENSE],
            [BuffAction::MAGIC_ACCURACY],
            [BuffAction::ACCURACY],
        ];
    }
}
