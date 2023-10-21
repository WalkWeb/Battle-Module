<?php

declare(strict_types=1);

namespace Tests\Battle\Action;

use Battle\Action\ActionCollection;
use Battle\Action\ActionException;
use Battle\Action\ActionInterface;
use Battle\Action\BuffAction;
use Battle\Command\CommandFactory;
use Battle\Unit\Defense\DefenseInterface;
use Battle\Unit\Offense\OffenseInterface;
use Battle\Unit\UnitException;
use Battle\Unit\UnitInterface;
use Exception;
use Tests\AbstractUnitTest;
use Tests\Factory\UnitFactory;

class BuffActionTest extends AbstractUnitTest
{
    /**
     * Тест на изменение максимального здоровья
     *
     * @dataProvider multiplierMaxLifeDataProvider
     * @param int $unitId
     * @param int $defaultMaxLife
     * @param int $power
     * @param int $expectedMaxLife
     * @param int $expectedLife
     * @throws Exception
     */
    public function testBuffActionMaximumLifeSuccess(
        int $unitId,
        int $defaultMaxLife,
        int $power,
        int $expectedMaxLife,
        int $expectedLife
    ): void
    {
        $unit = UnitFactory::createByTemplate($unitId);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = new BuffAction(
           $this->container,
            $unit,
            $enemyCommand,
            $command,
            BuffAction::TARGET_SELF,
            'change maximum life',
            BuffAction::MAX_LIFE,
            $power
        );

        // Проверяем изначальное значение максимального здоровья
        self::assertEquals($defaultMaxLife, $unit->getTotalLife());

        // Применяем баф
        self::assertTrue($action->canByUsed());
        $callbackActions = $action->handle();

        self::assertEquals(new ActionCollection(), $callbackActions);

        self::assertEquals($expectedMaxLife, $unit->getTotalLife());
        self::assertEquals($expectedLife, $unit->getLife());

        // Откатываем изменения и проверяем, что максимальное здоровье вернулось к исходному значению
        $action->getRevertAction()->handle();

        self::assertEquals($defaultMaxLife, $unit->getTotalLife());
    }

    /**
     * Тест на изменение максимальной маны
     *
     * @dataProvider multiplierMaxManaDataProvider
     * @param int $unitId
     * @param int $defaultMaxMana
     * @param int $power
     * @param int $expectedMaxMana
     * @param int $expectedMana
     * @throws Exception
     */
    public function testBuffActionMaximumManaSuccess(
        int $unitId,
        int $defaultMaxMana,
        int $power,
        int $expectedMaxMana,
        int $expectedMana
    ): void
    {
        $unit = UnitFactory::createByTemplate($unitId);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = new BuffAction(
           $this->container,
            $unit,
            $enemyCommand,
            $command,
            BuffAction::TARGET_SELF,
            'change max mana',
            BuffAction::MAX_MANA,
            $power
        );

        // Проверяем изначальное значение максимальной маны
        self::assertEquals($defaultMaxMana, $unit->getTotalMana());

        // Применяем баф
        self::assertTrue($action->canByUsed());
        $callbackActions = $action->handle();

        self::assertEquals(new ActionCollection(), $callbackActions);

        self::assertEquals($expectedMaxMana, $unit->getTotalMana());
        self::assertEquals($expectedMana, $unit->getMana());

        // Откатываем изменения и проверяем, что мана вернулась к исходному значению
        $action->getRevertAction()->handle();

        self::assertEquals($defaultMaxMana, $unit->getTotalMana());
    }

    /**
     * Тест на изменение скорости атаки юнита
     *
     * @dataProvider multiplierAttackSpeedDataProvider
     * @param int $unitId
     * @param int $power
     * @param float $expectedAttackSpeed
     * @throws Exception
     */
    public function testBuffActionAttackSpeedSuccess(int $unitId, int $power, float $expectedAttackSpeed): void
    {
        $unit = UnitFactory::createByTemplate($unitId);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = new BuffAction(
           $this->container,
            $unit,
            $enemyCommand,
            $command,
            BuffAction::TARGET_SELF,
            'change attack speed',
            BuffAction::ATTACK_SPEED,
            $power
        );

        self::assertEquals(ActionInterface::SKIP_ANIMATION_METHOD, $action->getAnimationMethod());
        self::assertEquals('buff', $action->getMessageMethod());

        $oldAttackSpeed = $unit->getOffense()->getAttackSpeed();

        // BuffAction всегда готов примениться (а EffectAction - только если аналогичный эффект на юните отсутствует)
        self::assertTrue($action->canByUsed());

        // Применяем баф
        $action->handle();

        // Проверяем обновленную скорость атаки
        self::assertEquals($expectedAttackSpeed, $unit->getOffense()->getAttackSpeed());

        // Проверка скорости атаки через множитель (на всякий случай)
        self::assertEquals(round($oldAttackSpeed * (($power + 100) / 100), 2), $unit->getOffense()->getAttackSpeed());

        // Откатываем изменение и проверяем, что скорость атаки изменилась к исходной
        $action->getRevertAction()->handle();

        self::assertEquals($oldAttackSpeed, $unit->getOffense()->getAttackSpeed());
    }

    /**
     * Тест на изменение скорости создания заклинаний юнита
     *
     * @throws Exception
     */
    public function testBuffActionCastSpeedSuccess(): void
    {
        $power = 130;

        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $oldCastSpeed = $unit->getOffense()->getCastSpeed();

        $action = new BuffAction(
           $this->container,
            $unit,
            $enemyCommand,
            $command,
            BuffAction::TARGET_SELF,
            'change cast speed',
            BuffAction::CAST_SPEED,
            $power
        );

        self::assertEquals(ActionInterface::SKIP_ANIMATION_METHOD, $action->getAnimationMethod());
        self::assertEquals('buff', $action->getMessageMethod());

        $newCastSpeed = $unit->getOffense()->getCastSpeed() * $power / 100;

        // Применяем баф
        self::assertTrue($action->canByUsed());
        $action->handle();

        self::assertEquals($newCastSpeed, $unit->getOffense()->getCastSpeed());

        // Откат изменения
        $action->getRevertAction()->handle();

        self::assertEquals($oldCastSpeed, $unit->getOffense()->getCastSpeed());
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
           $this->container,
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
        self::assertEquals((int)($oldAccuracy * (($power + 100) / 100)), $unit->getOffense()->getAccuracy());

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
           $this->container,
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
        self::assertEquals((int)($oldAccuracy * (($power + 100) / 100)), $unit->getOffense()->getMagicAccuracy());

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
           $this->container,
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
        self::assertEquals((int)($oldDefense * (($power + 100) / 100)), $unit->getDefense()->getDefense());

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
           $this->container,
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
        self::assertEquals((int)($oldMagicDefense * (($power + 100) / 100)), $unit->getDefense()->getMagicDefense());

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
           $this->container,
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
           $this->container,
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
     * Тест на увеличение/уменьшение физического урона
     *
     * @dataProvider multiplierPhysicalDamageDataProvider
     * @param int $power
     * @param int $newPhysicalDamage
     * @throws Exception
     */
    public function testBuffActionMultiplierPhysicalDamageSuccess(int $power, int $newPhysicalDamage): void
    {
        $unit = UnitFactory::createByTemplate(12);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = new BuffAction(
           $this->container,
            $unit,
            $enemyCommand,
            $command,
            BuffAction::TARGET_SELF,
            'multiplier physical damage',
            BuffAction::PHYSICAL_DAMAGE,
            $power
        );

        // Изначальный физический урон
        self::assertEquals(3000, $unit->getOffense()->getPhysicalDamage());

        $oldPhysicalDamage = $unit->getOffense()->getPhysicalDamage();

        // Применяем баф
        self::assertTrue($action->canByUsed());
        $action->handle();

        // Проверяем обновленный физический урон
        self::assertEquals($newPhysicalDamage, $unit->getOffense()->getPhysicalDamage());

        // Проверяем обновленный физический урон от множителя (на всякий случай)
        self::assertEquals((int)($oldPhysicalDamage * (($power + 100) / 100)), $unit->getOffense()->getPhysicalDamage());

        // Откатываем баф и проверяем, что физический урон вернулся к исходному значению
        $action->getRevertAction()->handle();
        self::assertEquals(3000, $unit->getOffense()->getPhysicalDamage());
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
           $this->container,
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
        self::assertEquals((int)($oldFireDamage * (($power + 100) / 100)), $unit->getOffense()->getFireDamage());

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
           $this->container,
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
        self::assertEquals((int)($oldWaterDamage * (($power + 100) / 100)), $unit->getOffense()->getWaterDamage());

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
           $this->container,
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
        self::assertEquals((int)($oldAirDamage * (($power + 100) / 100)), $unit->getOffense()->getAirDamage());

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
           $this->container,
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
     * Тест на увеличение/уменьшение урона магией жизни
     *
     * @dataProvider multiplierLifeDamageDataProvider
     * @param int $power
     * @param int $newLifeDamage
     * @throws Exception
     */
    public function testBuffActionMultiplierLifeDamageSuccess(int $power, int $newLifeDamage): void
    {
        $unit = UnitFactory::createByTemplate(12);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = new BuffAction(
           $this->container,
            $unit,
            $enemyCommand,
            $command,
            BuffAction::TARGET_SELF,
            'multiplier life damage',
            BuffAction::LIFE_DAMAGE,
            $power
        );

        // Изначальный урон магией жизни
        self::assertEquals(71, $unit->getOffense()->getLifeDamage());

        $oldLifeDamage = $unit->getOffense()->getLifeDamage();

        // Применяем баф
        self::assertTrue($action->canByUsed());
        $action->handle();

        // Проверяем обновленный урон магией жизни
        self::assertEquals($newLifeDamage, $unit->getOffense()->getLifeDamage());

        // Проверяем обновленный урон магией жизни от множителя (на всякий случай)
        self::assertEquals((int)($oldLifeDamage * ($power / 100)), $unit->getOffense()->getLifeDamage());

        // Откатываем баф и проверяем, что урон магией жизни вернулся к исходному значению
        $action->getRevertAction()->handle();
        self::assertEquals(71, $unit->getOffense()->getLifeDamage());
    }

    /**
     * Тест на увеличение/уменьшение урона магией смерти
     *
     * @dataProvider multiplierDeathDamageDataProvider
     * @param int $power
     * @param int $newDeathDamage
     * @throws Exception
     */
    public function testBuffActionMultiplierDeathDamageSuccess(int $power, int $newDeathDamage): void
    {
        $unit = UnitFactory::createByTemplate(12);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = new BuffAction(
           $this->container,
            $unit,
            $enemyCommand,
            $command,
            BuffAction::TARGET_SELF,
            'multiplier death damage',
            BuffAction::DEATH_DAMAGE,
            $power
        );

        // Изначальный урон магией смерти
        self::assertEquals(59, $unit->getOffense()->getDeathDamage());

        $oldLifeDamage = $unit->getOffense()->getDeathDamage();

        // Применяем баф
        self::assertTrue($action->canByUsed());
        $action->handle();

        // Проверяем обновленный урон магией смерти
        self::assertEquals($newDeathDamage, $unit->getOffense()->getDeathDamage());

        // Проверяем обновленный урон магией смерти от множителя (на всякий случай)
        self::assertEquals((int)($oldLifeDamage * ($power / 100)), $unit->getOffense()->getDeathDamage());

        // Откатываем баф и проверяем, что урон магией смерти вернулся к исходному значению
        $action->getRevertAction()->handle();
        self::assertEquals(59, $unit->getOffense()->getDeathDamage());
    }

    /**
     * Тест на плоское увеличение/уменьшение сопротивления урону
     *
     * @dataProvider addResistDataProvider
     * @param int $unitId
     * @param int $power
     * @param int $oldResist
     * @param int $newResist
     * @throws Exception
     */
    public function testBuffActionAddResistSuccess(int $unitId, int $power, int $oldResist, int $newResist): void
    {
        $unit = UnitFactory::createByTemplate($unitId);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $resists = [
            'physical' => [
                'buff' => BuffAction::ADD_PHYSICAL_RESIST,
                'getter' => 'getPhysicalResist',
            ],
            'fire' => [
                'buff' => BuffAction::ADD_FIRE_RESIST,
                'getter' => 'getFireResist',
            ],
            'water' => [
                'buff' => BuffAction::ADD_WATER_RESIST,
                'getter' => 'getWaterResist',
            ],
            'air' => [
                'buff' => BuffAction::ADD_AIR_RESIST,
                'getter' => 'getAirResist',
            ],
            'earth' => [
                'buff' => BuffAction::ADD_EARTH_RESIST,
                'getter' => 'getEarthResist',
            ],
            'life' => [
                'buff' => BuffAction::ADD_LIFE_RESIST,
                'getter' => 'getLifeResist',
            ],
            'death' => [
                'buff' => BuffAction::ADD_DEATH_RESIST,
                'getter' => 'getDeathResist',
            ],
        ];

        foreach ($resists as $resist) {
            $action = new BuffAction(
               $this->container,
                $unit,
                $enemyCommand,
                $command,
                BuffAction::TARGET_SELF,
                'change resist',
                $resist['buff'],
                $power
            );

            // Изначальное сопротивление
            self::assertEquals($oldResist, $unit->getDefense()->{$resist['getter']}());

            // Применяем баф
            self::assertTrue($action->canByUsed());
            $action->handle();

            // Проверяем обновленное сопротивление
            self::assertEquals($newResist, $unit->getDefense()->{$resist['getter']}());

            // Откатываем баф и проверяем, что сопротивление вернулось к исходному значению
            $action->getRevertAction()->handle();
            self::assertEquals($oldResist, $unit->getDefense()->{$resist['getter']}());
        }
    }

    /**
     * Тест на изменение максимального сопротивления
     *
     * @dataProvider addMaxResistDataProvider
     * @param int $power
     * @param int $newResist
     * @throws Exception
     */
    public function testBuffActionAddMaxResistSuccess(int $power, int $newResist): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $resists = [
            'physical' => [
                'buff' => BuffAction::ADD_PHYSICAL_MAX_RESIST,
                'getter' => 'getPhysicalMaxResist',
            ],
            'fire' => [
                'buff' => BuffAction::ADD_FIRE_MAX_RESIST,
                'getter' => 'getFireMaxResist',
            ],
            'water' => [
                'buff' => BuffAction::ADD_WATER_MAX_RESIST,
                'getter' => 'getWaterMaxResist',
            ],
            'air' => [
                'buff' => BuffAction::ADD_AIR_MAX_RESIST,
                'getter' => 'getAirMaxResist',
            ],
            'earth' => [
                'buff' => BuffAction::ADD_EARTH_MAX_RESIST,
                'getter' => 'getEarthMaxResist',
            ],
            'life' => [
                'buff' => BuffAction::ADD_LIFE_MAX_RESIST,
                'getter' => 'getLifeMaxResist',
            ],
            'death' => [
                'buff' => BuffAction::ADD_DEATH_MAX_RESIST,
                'getter' => 'getDeathMaxResist',
            ],
        ];

        foreach ($resists as $resist) {
            $action = new BuffAction(
               $this->container,
                $unit,
                $enemyCommand,
                $command,
                BuffAction::TARGET_SELF,
                'change max resist',
                $resist['buff'],
                $power
            );

            // Изначальное максимальное сопротивление
            self::assertEquals(75, $unit->getDefense()->{$resist['getter']}());

            // Применяем баф
            self::assertTrue($action->canByUsed());
            $action->handle();

            // Проверяем обновленное максимальное сопротивление
            self::assertEquals($newResist, $unit->getDefense()->{$resist['getter']}());

            // Откатываем баф и проверяем, что максимальное сопротивление вернулось к исходному значению
            $action->getRevertAction()->handle();
            self::assertEquals(75, $unit->getDefense()->{$resist['getter']}());
        }
    }

    /**
     * Тест на изменение блока
     *
     * @dataProvider addBlockDataProvider
     * @param int $power
     * @param int $expectedBlock
     * @throws Exception
     */
    public function testBuffActionAddBlockSuccess(int $power, int $expectedBlock): void
    {
        $unit = UnitFactory::createByTemplate(12);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = new BuffAction(
           $this->container,
            $unit,
            $enemyCommand,
            $command,
            BuffAction::TARGET_SELF,
            'change block',
            BuffAction::ADD_BLOCK,
            $power
        );

        // Изначальный блок
        self::assertEquals(30, $unit->getDefense()->getBlock());

        // Применяем баф
        self::assertTrue($action->canByUsed());
        $action->handle();

        // Проверяем обновленный блок
        self::assertEquals($expectedBlock, $unit->getDefense()->getBlock());

        // Откатываем баф и проверяем, что блок вернулся к исходному значению
        $action->getRevertAction()->handle();
        self::assertEquals(30, $unit->getDefense()->getBlock());
    }

    /**
     * Тест на изменение магического блока
     *
     * @dataProvider addMagicBlockDataProvider
     * @param int $power
     * @param int $expectedBlock
     * @throws Exception
     */
    public function testBuffActionAddMagicBlockSuccess(int $power, int $expectedBlock): void
    {
        $unit = UnitFactory::createByTemplate(12);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = new BuffAction(
           $this->container,
            $unit,
            $enemyCommand,
            $command,
            BuffAction::TARGET_SELF,
            'change magic block',
            BuffAction::ADD_MAGIC_BLOCK,
            $power
        );

        // Изначальный магический блок
        self::assertEquals(40, $unit->getDefense()->getMagicBlock());

        // Применяем баф
        self::assertTrue($action->canByUsed());
        $action->handle();

        // Проверяем обновленный магический блок
        self::assertEquals($expectedBlock, $unit->getDefense()->getMagicBlock());

        // Откатываем баф и проверяем, что магический блок вернулся к исходному значению
        $action->getRevertAction()->handle();
        self::assertEquals(40, $unit->getDefense()->getMagicBlock());
    }

    /**
     * Тест на изменение магического блока
     *
     * @dataProvider addBlockIgnoreDataProvider
     * @param int $power
     * @param int $expectedBlockIgnore
     * @throws Exception
     */
    public function testBuffActionAddBlockIgnoreSuccess(int $power, int $expectedBlockIgnore): void
    {
        $unit = UnitFactory::createByTemplate(12);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = new BuffAction(
           $this->container,
            $unit,
            $enemyCommand,
            $command,
            BuffAction::TARGET_SELF,
            'change block ignore',
            BuffAction::ADD_BLOCK_IGNORE,
            $power
        );

        // Изначальный показатель игнорирования блока
        self::assertEquals(30, $unit->getOffense()->getBlockIgnoring());

        // Применяем баф
        self::assertTrue($action->canByUsed());
        $action->handle();

        // Проверяем обновленный показатель игнорирования блока
        self::assertEquals($expectedBlockIgnore, $unit->getOffense()->getBlockIgnoring());

        // Откатываем баф и проверяем, что обновленный показатель игнорирования блока вернулся к исходному
        $action->getRevertAction()->handle();
        self::assertEquals(30, $unit->getOffense()->getBlockIgnoring());
    }

    /**
     * Тест на изменение показателя вампиризма
     *
     * @dataProvider addVampirismDataProvider
     * @param int $power
     * @param int $expectedVampirism
     * @throws Exception
     */
    public function testBuffActionAddVampirismSuccess(int $power, int $expectedVampirism): void
    {
        $unit = UnitFactory::createByTemplate(12);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = new BuffAction(
           $this->container,
            $unit,
            $enemyCommand,
            $command,
            BuffAction::TARGET_SELF,
            'change vampirism',
            BuffAction::ADD_VAMPIRISM,
            $power
        );

        // Изначальный вампиризм
        self::assertEquals(10, $unit->getOffense()->getVampirism());

        // Применяем баф
        self::assertTrue($action->canByUsed());
        $action->handle();

        // Проверяем обновленный вампиризм
        self::assertEquals($expectedVampirism, $unit->getOffense()->getVampirism());

        // Откатываем баф и проверяем, что вампиризм вернулся к исходному значению
        $action->getRevertAction()->handle();
        self::assertEquals(10, $unit->getOffense()->getVampirism());
    }

    /**
     * Тест на изменение показателя магического вампиризма
     *
     * @dataProvider addMagicVampirismDataProvider
     * @param int $power
     * @param int $expectedMagicVampirism
     * @throws Exception
     */
    public function testBuffActionAddMagicVampirismSuccess(int $power, int $expectedMagicVampirism): void
    {
        $unit = UnitFactory::createByTemplate(12);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = new BuffAction(
           $this->container,
            $unit,
            $enemyCommand,
            $command,
            BuffAction::TARGET_SELF,
            'change magic vampirism',
            BuffAction::ADD_MAGIC_VAMPIRISM,
            $power
        );

        // Изначальный магический вампиризм
        self::assertEquals(20, $unit->getOffense()->getMagicVampirism());

        // Применяем баф
        self::assertTrue($action->canByUsed());
        $action->handle();

        // Проверяем обновленный магический вампиризм
        self::assertEquals($expectedMagicVampirism, $unit->getOffense()->getMagicVampirism());

        // Откатываем баф и проверяем, что магический вампиризм вернулся к исходному значению
        $action->getRevertAction()->handle();
        self::assertEquals(20, $unit->getOffense()->getMagicVampirism());
    }

    /**
     * Тест на изменение показателя общего сопротивления урону
     *
     * @dataProvider addGlobalResistDataProvider
     * @param int $power
     * @param int $expectedGlobalResist
     * @throws Exception
     */
    public function testBuffActionAddGlobalResistSuccess(int $power, int $expectedGlobalResist): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = new BuffAction(
           $this->container,
            $unit,
            $enemyCommand,
            $command,
            BuffAction::TARGET_SELF,
            'change global damage resist',
            BuffAction::ADD_GLOBAL_RESIST,
            $power
        );

        // Изначальное общее сопротивление урону
        self::assertEquals(0, $unit->getDefense()->getGlobalResist());

        // Применяем баф
        self::assertTrue($action->canByUsed());
        $action->handle();

        // Проверяем обновленное общее сопротивление урону
        self::assertEquals($expectedGlobalResist, $unit->getDefense()->getGlobalResist());

        // Откатываем баф и проверяем, что магический вампиризм вернулся к исходному значению
        $action->getRevertAction()->handle();
        self::assertEquals(0, $unit->getDefense()->getGlobalResist());
    }

    /**
     * Тест на изменение показателя ментального барьера
     *
     * @dataProvider addMentalBarrierDataProvider
     * @param int $power
     * @param int $expectedMentalBarrier
     * @throws Exception
     */
    public function testBuffActionAddMentalBarrierSuccess(int $power, int $expectedMentalBarrier): void
    {
        $unit = UnitFactory::createByTemplate(12);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = new BuffAction(
           $this->container,
            $unit,
            $enemyCommand,
            $command,
            BuffAction::TARGET_SELF,
            'change mental barrier',
            BuffAction::ADD_MENTAL_BARRIER,
            $power
        );

        // Изначальный ментальный барьер
        self::assertEquals(20, $unit->getDefense()->getMentalBarrier());

        // Применяем баф
        self::assertTrue($action->canByUsed());
        $action->handle();

        // Проверяем обновленный ментальный барьер
        self::assertEquals($expectedMentalBarrier, $unit->getDefense()->getMentalBarrier());

        // Откатываем баф и проверяем, что ментальный барьер вернулся к исходному значению
        $action->getRevertAction()->handle();
        self::assertEquals(20, $unit->getDefense()->getMentalBarrier());
    }

    /**
     * Тест на изменение показателя множителя получаемой концентрации
     *
     * @dataProvider addMultiplierConcentrationDataProvider
     * @param int $unitId
     * @param int $baseMultiplierConcentration
     * @param int $power
     * @param int $expectedMultiplierConcentration
     * @throws Exception
     */
    public function testBuffActionAddMultiplierConcentrationSuccess(
        int $unitId,
        int $baseMultiplierConcentration,
        int $power,
        int $expectedMultiplierConcentration
    ): void
    {
        $unit = UnitFactory::createByTemplate($unitId);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = new BuffAction(
           $this->container,
            $unit,
            $enemyCommand,
            $command,
            BuffAction::TARGET_SELF,
            'change multiplier add concentration',
            BuffAction::ADD_CONCENTRATION,
            $power
        );

        // Изначальный множитель получаемой концентрации
        self::assertEquals($baseMultiplierConcentration, $unit->getAddConcentrationMultiplier());

        // Применяем баф
        self::assertTrue($action->canByUsed());
        $action->handle();

        // Проверяем обновленный множитель получаемой концентрации
        self::assertEquals($expectedMultiplierConcentration, $unit->getAddConcentrationMultiplier());

        // Откатываем баф и проверяем, что множитель получаемой концентрации вернулся к исходному значению
        $action->getRevertAction()->handle();
        self::assertEquals($baseMultiplierConcentration, $unit->getAddConcentrationMultiplier());
    }

    /**
     * Тест на изменение показателя множителя хитрости
     *
     * @dataProvider addMultiplierCunningDataProvider
     * @param int $unitId
     * @param int $baseMultiplierCunning
     * @param int $power
     * @param int $expectedMultiplierCunning
     * @throws Exception
     */
    public function testBuffActionAddMultiplierCunningSuccess(
        int $unitId,
        int $baseMultiplierCunning,
        int $power,
        int $expectedMultiplierCunning
    ): void
    {
        $unit = UnitFactory::createByTemplate($unitId);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = new BuffAction(
           $this->container,
            $unit,
            $enemyCommand,
            $command,
            BuffAction::TARGET_SELF,
            'change multiplier cunning',
            BuffAction::ADD_CUNNING,
            $power
        );

        // Изначальный множитель хитрости
        self::assertEquals($baseMultiplierCunning, $unit->getCunningMultiplier());

        // Применяем баф
        self::assertTrue($action->canByUsed());
        $action->handle();

        // Проверяем обновленный множитель хитрости
        self::assertEquals($expectedMultiplierCunning, $unit->getCunningMultiplier());

        // Откатываем баф и проверяем, что множитель хитрости вернулся к исходному значению
        $action->getRevertAction()->handle();
        self::assertEquals($baseMultiplierCunning, $unit->getCunningMultiplier());
    }

    /**
     * Тест на изменение показателя множителя получаемой ярости
     *
     * @dataProvider addMultiplierRageDataProvider
     * @param int $unitId
     * @param int $baseMultiplierRage
     * @param int $power
     * @param int $expectedMultiplierRage
     * @throws Exception
     */
    public function testBuffActionAddMultiplierRageSuccess(
        int $unitId,
        int $baseMultiplierRage,
        int $power,
        int $expectedMultiplierRage
    ): void
    {
        $unit = UnitFactory::createByTemplate($unitId);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = new BuffAction(
           $this->container,
            $unit,
            $enemyCommand,
            $command,
            BuffAction::TARGET_SELF,
            'change multiplier add rage',
            BuffAction::ADD_RAGE,
            $power
        );

        // Изначальный множитель получаемой ярости
        self::assertEquals($baseMultiplierRage, $unit->getAddRageMultiplier());

        // Применяем баф
        self::assertTrue($action->canByUsed());
        $action->handle();

        // Проверяем обновленный множитель получаемой ярости
        self::assertEquals($expectedMultiplierRage, $unit->getAddRageMultiplier());

        // Откатываем баф и проверяем, что множитель получаемой ярости вернулся к исходному значению
        $action->getRevertAction()->handle();
        self::assertEquals($baseMultiplierRage, $unit->getAddRageMultiplier());
    }

    /**
     * Тест на изменение общего множителя наносимого урона
     *
     * @dataProvider addMultiplierDamageDataProvider
     * @param int $unitId
     * @param int $power
     * @param int $baseDamageMultiplier
     * @param int $exceptedDamageMultiplier
     * @throws Exception
     */
    public function testBuffActionAddMultiplierDamageSuccess(
        int $unitId,
        int $power,
        int $baseDamageMultiplier,
        int $exceptedDamageMultiplier
    ): void
    {
        $unit = UnitFactory::createByTemplate($unitId);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = new BuffAction(
           $this->container,
            $unit,
            $enemyCommand,
            $command,
            BuffAction::TARGET_SELF,
            'add multiplier damage',
            BuffAction::ADD_DAMAGE_MULTIPLIER,
            $power
        );

        // Изначальный общий множитель урона
        self::assertEquals($baseDamageMultiplier, $unit->getOffense()->getDamageMultiplier());

        // Применяем баф
        self::assertTrue($action->canByUsed());
        $action->handle();

        // Проверяем обновленный множитель урона
        self::assertEquals($exceptedDamageMultiplier, $unit->getOffense()->getDamageMultiplier());

        // Откатываем баф и проверяем, что множитель урона вернулся к исходному значению
        $action->getRevertAction()->handle();
        self::assertEquals($baseDamageMultiplier, $unit->getOffense()->getDamageMultiplier());
    }

    /**
     * Тест на чрезмерное уменьшение характеристики
     *
     * @dataProvider overReducedStatDataProviderOld
     * @param string $modifyMethod
     * @throws Exception
     */
    public function testBuffActionOverReducedStatOld(string $modifyMethod): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = new BuffAction(
           $this->container,
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
     * Тест на чрезмерное уменьшение характеристики (вариант для новой механики, где
     *
     * @dataProvider overReducedStatDataProviderNew
     * @param string $modifyMethod
     * @throws Exception
     */
    public function testBuffActionOverReducedStatNew(string $modifyMethod): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = new BuffAction(
            $this->container,
            $unit,
            $enemyCommand,
            $command,
            BuffAction::TARGET_SELF,
            'OverReducedStat',
            $modifyMethod,
            -95
        );

        $this->expectException(UnitException::class);
        $this->expectErrorMessage(UnitException::OVER_REDUCED . BuffAction::MEW_MIN_MULTIPLIER);
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
           $this->container,
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
                100,
                426,
            ],
            [
                11,
                236,
            ],
            [
                -13,
                185,
            ],
            [
                -68,
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
                100,
                228,
            ],
            [
                11,
                126,
            ],
            [
                -13,
                99,
            ],
            [
                -68,
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
                100,
                550,
            ],
            [
                11,
                305,
            ],
            [
                -13,
                239,
            ],
            [
                -68,
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
                100,
                262,
            ],
            [
                11,
                145,
            ],
            [
                -13,
                113,
            ],
            [
                -68,
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

    public function multiplierPhysicalDamageDataProvider(): array
    {
        return [
            [
                100,
                6000,
            ],
            [
                11,
                3330,
            ],
            [
                -13,
                2610,
            ],
            [
                -68,
                960,
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
                100,
                130,
            ],
            [
                11,
                72,
            ],
            [
                -13,
                56,
            ],
            [
                -68,
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
                100,
                174,
            ],
            [
                11,
                96,
            ],
            [
                -13,
                75,
            ],
            [
                -68,
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
                100,
                108,
            ],
            [
                11,
                59,
            ],
            [
                -13,
                46,
            ],
            [
                -68,
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

    /**
     * @return array
     */
    public function multiplierLifeDamageDataProvider(): array
    {
        return [
            [
                200,
                142,
            ],
            [
                111,
                78,
            ],
            [
                87,
                61,
            ],
            [
                32,
                22,
            ],
        ];
    }

    /**
     * @return array
     */
    public function multiplierDeathDamageDataProvider(): array
    {
        return [
            [
                200,
                118,
            ],
            [
                111,
                65,
            ],
            [
                87,
                51,
            ],
            [
                32,
                18,
            ],
        ];
    }

    public function addResistDataProvider(): array
    {
        return [
            [
                1,
                10,
                0,
                10,
            ],
            [
                1,
                -20,
                0,
                -20,
            ],
            [
                1,
                100,
                0,
                75,
            ],
            [
                12,
                10,
                60,
                70,
            ],
            [
                12,
                20,
                60,
                75,
            ],
            [
                12,
                -20,
                60,
                40,
            ],
            [
                12,
                100,
                60,
                75,
            ],
            [
                12,
                -2000,
                60,
                -1000,
            ],
        ];
    }


    public function addMaxResistDataProvider(): array
    {
        return [
            [
                5,
                80,
            ],
            [
                15,
                90,
            ],
            [
                50,
                100
            ],
        ];
    }

    public function addBlockDataProvider(): array
    {
        return [
            [
                10,
                40,
            ],
            [
                200,
                DefenseInterface::MAX_BLOCK,
            ],
            [
                -10,
                20,
            ],
            [
                -300,
                DefenseInterface::MIN_BLOCK,
            ],
        ];
    }

    public function addMagicBlockDataProvider(): array
    {
        return [
            [
                10,
                50,
            ],
            [
                200,
                DefenseInterface::MAX_BLOCK,
            ],
            [
                -10,
                30,
            ],
            [
                -300,
                DefenseInterface::MIN_BLOCK,
            ],
        ];
    }

    public function addBlockIgnoreDataProvider(): array
    {
        return [
            [
                5,
                35,
            ],
            [
                100,
                100,
            ],
            [
                -50,
                0,
            ],
        ];
    }

    public function addVampirismDataProvider(): array
    {
        return [
            [
                5,
                15,
            ],
            [
                200,
                OffenseInterface::MAX_VAMPIRISM,
            ],
            [
                -5,
                5,
            ],
            [
                -50,
                OffenseInterface::MIN_VAMPIRISM,
            ],
        ];
    }

    public function addMagicVampirismDataProvider(): array
    {
        return [
            [
                5,
                25,
            ],
            [
                200,
                OffenseInterface::MAX_VAMPIRISM,
            ],
            [
                -5,
                15,
            ],
            [
                -50,
                OffenseInterface::MIN_VAMPIRISM,
            ],
        ];
    }

    public function addGlobalResistDataProvider(): array
    {
        return [
            [
                10,
                10,
            ],
            [
                -30,
                -30,
            ],
            [
                200,
                DefenseInterface::MAX_RESISTANCE,
            ],
            [
                -2000,
                DefenseInterface::MIN_RESISTANCE,
            ],
        ];
    }

    public function addMentalBarrierDataProvider(): array
    {
        return [
            [
                10,
                30,
            ],
            [
                -50,
                -30,
            ],
            [
                300,
                DefenseInterface::MAX_MENTAL_BARRIER,
            ],
            [
                -300,
                DefenseInterface::MIN_MENTAL_BARRIER,
            ],
        ];
    }

    public function addMultiplierConcentrationDataProvider(): array
    {
        return [
            [
                1,
                0,
                30,
                30,
            ],
            [
                1,
                0,
                -50,
                -50,
            ],
            [
                12,
                30,
                30,
                60,
            ],
            [
                12,
                30,
                -50,
                -20,
            ],
            [
                1,
                0,
                3000,
                UnitInterface::MAX_RESOURCE_MULTIPLIER,
            ],
            [
                1,
                0,
                -500,
                UnitInterface::MIN_RESOURCE_MULTIPLIER,
            ],
            [
                12,
                30,
                3000,
                UnitInterface::MAX_RESOURCE_MULTIPLIER,
            ],
            [
                12,
                30,
                -500,
                UnitInterface::MIN_RESOURCE_MULTIPLIER,
            ],
        ];
    }

    public function addMultiplierCunningDataProvider(): array
    {
        return [
            [
                1,
                0,
                25,
                25,
            ],
            [
                1,
                0,
                -35,
                -35,
            ],
            [
                12,
                20,
                25,
                45,
            ],
            [
                12,
                20,
                -50,
                -30,
            ],
            [
                1,
                0,
                3000,
                UnitInterface::MAX_RESOURCE_MULTIPLIER,
            ],
            [
                1,
                0,
                -500,
                UnitInterface::MIN_RESOURCE_MULTIPLIER,
            ],
            [
                12,
                20,
                3000,
                UnitInterface::MAX_RESOURCE_MULTIPLIER,
            ],
            [
                12,
                20,
                -500,
                UnitInterface::MIN_RESOURCE_MULTIPLIER,
            ],
        ];
    }

    public function addMultiplierRageDataProvider(): array
    {
        return [
            [
                1,
                0,
                15,
                15,
            ],
            [
                1,
                0,
                -25,
                -25,
            ],
            [
                12,
                40,
                33,
                73,
            ],
            [
                12,
                40,
                -80,
                -40,
            ],
            [
                1,
                0,
                3000,
                UnitInterface::MAX_RESOURCE_MULTIPLIER,
            ],
            [
                1,
                0,
                -500,
                UnitInterface::MIN_RESOURCE_MULTIPLIER,
            ],
            [
                12,
                40,
                3000,
                UnitInterface::MAX_RESOURCE_MULTIPLIER,
            ],
            [
                12,
                40,
                -500,
                UnitInterface::MIN_RESOURCE_MULTIPLIER,
            ],
        ];
    }

    public function addMultiplierDamageDataProvider(): array
    {
        return [
            [
                1,
                10,
                100,
                110,
            ],
            [
                1,
                -25,
                100,
                75,
            ],
            [
                1,
                -250,
                100,
                OffenseInterface::MIN_DAMAGE_MULTIPLIER,
            ],
            [
                1,
                10000000,
                100,
                OffenseInterface::MAX_DAMAGE_MULTIPLIER,
            ],
        ];
    }

    public function multiplierMaxManaDataProvider(): array
    {
        return [
            [
                1,
                50,
                10,
                55,
                55,
            ],
            [
                1,
                50,
                100,
                100,
                100,
            ],
            [
                1,
                50,
                37,
                68,
                68,
            ],
            [
                1,
                50,
                -79,
                10,
                10,
            ],
            [
                2,
                50,
                100,
                100,
                70, // 20 маны изначально + 50 на значение которого мана выросла
            ],
            [
                2,
                50,
                -89,
                5,
                5, // 20 изначальной маны уменьшились до максимального значения
            ],
            [
                23,
                3,
                -89,
                1, // 3 * 0.11 - округлится до 0, но мы не позволяем максимальной мане быть меньше 1
                1,
            ],
        ];
    }

    public function multiplierMaxLifeDataProvider(): array
    {
        return [
            [
                1,
                100,
                10,
                110,
                110,
            ],
            [
                2,
                250,
                37,
                342,
                342,
            ],
            [
                9,
                100,
                25,
                125,
                115,
            ],
            [
                9,
                100,
                -30,
                70,
                70,
            ],
            [
                23,
                3,
                -89,
                1, // 3 * 0.11 - округлится до 0, но мы не позволяем максимальной мане быть меньше 1
                1,
            ],
        ];
    }

    public function multiplierAttackSpeedDataProvider(): array
    {
        return [
            [
                1,
                10,
                1.1,
            ],
            [
                1,
                -15,
                0.85,
            ],
            [
                39,
                20,
                1.5,
            ],
            [
                39,
                -36,
                0.8,
            ],
            [
                39,
                -63,
                0.46,
            ],
        ];
    }

    public function overReducedStatDataProviderOld(): array
    {
        return [
            [BuffAction::EARTH_DAMAGE],
            [BuffAction::LIFE_DAMAGE],
            [BuffAction::DEATH_DAMAGE],
            [BuffAction::CRITICAL_MULTIPLIER],
            [BuffAction::CRITICAL_CHANCE],
            [BuffAction::CAST_SPEED],
        ];
    }

    public function overReducedStatDataProviderNew(): array
    {
        return [
            [BuffAction::ACCURACY],
            [BuffAction::MAGIC_ACCURACY],
            [BuffAction::DEFENSE],
            [BuffAction::MAGIC_DEFENSE],
            [BuffAction::MAX_LIFE],
            [BuffAction::MAX_MANA],
            [BuffAction::ATTACK_SPEED],
            [BuffAction::PHYSICAL_DAMAGE],
            [BuffAction::FIRE_DAMAGE],
            [BuffAction::WATER_DAMAGE],
            [BuffAction::AIR_DAMAGE],
        ];
    }
}
