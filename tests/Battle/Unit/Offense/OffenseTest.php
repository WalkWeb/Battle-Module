<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Offense;

use Battle\Unit\Defense\Defense;
use Battle\Unit\Offense\Offense;
use Battle\Unit\Offense\OffenseException;
use Battle\Unit\Offense\OffenseInterface;
use Exception;
use Tests\AbstractUnitTest;

class OffenseTest extends AbstractUnitTest
{
    /**
     * Тест на создание Offense
     *
     * @throws Exception
     */
    public function testOffenseCreate(): void
    {
        $typeDamage = 1;
        $weaponTypeId = 3;
        $physicalDamage = 300;
        $fireDamage = 310;
        $waterDamage = 320;
        $airDamage = 330;
        $earthDamage = 340;
        $lifeDamage = 350;
        $deathDamage = 360;
        $attackSpeed = 1.2;
        $accuracy = 200;
        $magicAccuracy = 100;
        $blockIgnore = 0;
        $criticalChance = 10;
        $criticalMultiplier = 200;
        $vampire = 5;

        $offense = new Offense(
            $typeDamage,
            $weaponTypeId,
            $physicalDamage,
            $fireDamage,
            $waterDamage,
            $airDamage,
            $earthDamage,
            $lifeDamage,
            $deathDamage,
            $attackSpeed,
            $accuracy,
            $magicAccuracy,
            $blockIgnore,
            $criticalChance,
            $criticalMultiplier,
            $vampire
        );

        $defense = new Defense(0, 10, 10, 10, 5, 0);

        self::assertEquals($typeDamage, $offense->getDamageType());
        self::assertEquals($weaponTypeId, $offense->getWeaponType()->getId());
        self::assertEquals($physicalDamage, $offense->getDamage($defense));
        self::assertEquals($fireDamage, $offense->getFireDamage());
        self::assertEquals($waterDamage, $offense->getWaterDamage());
        self::assertEquals($airDamage, $offense->getAirDamage());
        self::assertEquals($earthDamage, $offense->getEarthDamage());
        self::assertEquals($lifeDamage, $offense->getLifeDamage());
        self::assertEquals($deathDamage, $offense->getDeathDamage());
        self::assertEquals($attackSpeed, $offense->getAttackSpeed());
        self::assertEquals($accuracy, $offense->getAccuracy());
        self::assertEquals($magicAccuracy, $offense->getMagicAccuracy());
        self::assertEquals($blockIgnore, $offense->getBlockIgnore());
        self::assertEquals($criticalChance, $offense->getCriticalChance());
        self::assertEquals($criticalMultiplier, $offense->getCriticalMultiplier());
        self::assertEquals($vampire, $offense->getVampire());
        self::assertEquals(
            round(($physicalDamage + $fireDamage + $waterDamage + $airDamage + $earthDamage + $lifeDamage + $deathDamage) * $attackSpeed, 1),
            $offense->getDPS()
        );
    }

    /**
     * Тест на обновление Offense
     *
     * @throws Exception
     */
    public function testOffenseUpdate(): void
    {
        $offense = $this->createOffence();
        $defense = new Defense(0, 10, 10, 10, 5, 0);

        $offense->setPhysicalDamage($physicalDamage = 15);
        $offense->setFireDamage($fireDamage = 16);
        $offense->setWaterDamage($waterDamage = 17);
        $offense->setAirDamage($airDamage = 18);
        $offense->setEarthDamage($earthDamage = 19);
        $offense->setLifeDamage($lifeDamage = 20);
        $offense->setDeathDamage($deathDamage = 21);

        $offense->setAttackSpeed($attackSpeed = 1.2);
        $offense->setAccuracy($accuracy = 250);
        $offense->setMagicAccuracy($magicAccuracy = 150);
        $offense->setBlockIgnore($blockIgnore = 100);

        self::assertEquals($physicalDamage, $offense->getDamage($defense));

        self::assertEquals($physicalDamage, $offense->getPhysicalDamage());
        self::assertEquals($fireDamage, $offense->getFireDamage());
        self::assertEquals($waterDamage, $offense->getWaterDamage());
        self::assertEquals($airDamage, $offense->getAirDamage());
        self::assertEquals($lifeDamage, $offense->getLifeDamage());
        self::assertEquals($deathDamage, $offense->getDeathDamage());

        self::assertEquals($attackSpeed, $offense->getAttackSpeed());
        self::assertEquals($accuracy, $offense->getAccuracy());
        self::assertEquals($magicAccuracy, $offense->getMagicAccuracy());
        self::assertEquals($blockIgnore, $offense->getBlockIgnore());
    }

    /**
     * Тест на ошибку, когда в Offense пытаются записать слишком низкое значение физического урона
     *
     * @throws Exception
     */
    public function testOffenseSetUltraMinPhysicalDamage(): void
    {
        $offense = $this->createOffence();

        $this->expectException(OffenseException::class);
        $this->expectExceptionMessage(
            OffenseException::INCORRECT_PHYSICAL_DAMAGE_VALUE . OffenseInterface::MIN_DAMAGE . '-' . OffenseInterface::MAX_DAMAGE
        );
        $offense->setPhysicalDamage(OffenseInterface::MIN_DAMAGE - 1);
    }

    /**
     * Тест на ошибку, когда в Offense пытаются записать слишком высокое значение физического урона
     *
     * @throws Exception
     */
    public function testOffenseSetUltraMaxPhysicalDamage(): void
    {
        $offense = $this->createOffence();

        $this->expectException(OffenseException::class);
        $this->expectExceptionMessage(
            OffenseException::INCORRECT_PHYSICAL_DAMAGE_VALUE . OffenseInterface::MIN_DAMAGE . '-' . OffenseInterface::MAX_DAMAGE
        );
        $offense->setPhysicalDamage(OffenseInterface::MAX_DAMAGE + 1);
    }

    /**
     * Тест на ошибку, когда в Offense пытаются записать слишком низкое значение урона огнем
     *
     * @throws Exception
     */
    public function testOffenseSetUltraMinFireDamage(): void
    {
        $offense = $this->createOffence();

        $this->expectException(OffenseException::class);
        $this->expectExceptionMessage(
            OffenseException::INCORRECT_FIRE_DAMAGE_VALUE . OffenseInterface::MIN_DAMAGE . '-' . OffenseInterface::MAX_DAMAGE
        );
        $offense->setFireDamage(OffenseInterface::MIN_DAMAGE - 1);
    }

    /**
     * Тест на ошибку, когда в Offense пытаются записать слишком высокое значение урона огнем
     *
     * @throws Exception
     */
    public function testOffenseSetUltraMaxFireDamage(): void
    {
        $offense = $this->createOffence();

        $this->expectException(OffenseException::class);
        $this->expectExceptionMessage(
            OffenseException::INCORRECT_FIRE_DAMAGE_VALUE . OffenseInterface::MIN_DAMAGE . '-' . OffenseInterface::MAX_DAMAGE
        );
        $offense->setFireDamage(OffenseInterface::MAX_DAMAGE + 1);
    }

    /**
     * Тест на ошибку, когда в Offense пытаются записать слишком низкое значение урона водой
     *
     * @throws Exception
     */
    public function testOffenseSetUltraMinWaterDamage(): void
    {
        $offense = $this->createOffence();

        $this->expectException(OffenseException::class);
        $this->expectExceptionMessage(
            OffenseException::INCORRECT_WATER_DAMAGE_VALUE . OffenseInterface::MIN_DAMAGE . '-' . OffenseInterface::MAX_DAMAGE
        );
        $offense->setWaterDamage(OffenseInterface::MIN_DAMAGE - 1);
    }

    /**
     * Тест на ошибку, когда в Offense пытаются записать слишком высокое значение урона водой
     *
     * @throws Exception
     */
    public function testOffenseSetUltraMaxWaterDamage(): void
    {
        $offense = $this->createOffence();

        $this->expectException(OffenseException::class);
        $this->expectExceptionMessage(
            OffenseException::INCORRECT_WATER_DAMAGE_VALUE . OffenseInterface::MIN_DAMAGE . '-' . OffenseInterface::MAX_DAMAGE
        );
        $offense->setWaterDamage(OffenseInterface::MAX_DAMAGE + 1);
    }

    /**
     * Тест на ошибку, когда в Offense пытаются записать слишком низкое значение урона воздухом
     *
     * @throws Exception
     */
    public function testOffenseSetUltraMinAirDamage(): void
    {
        $offense = $this->createOffence();

        $this->expectException(OffenseException::class);
        $this->expectExceptionMessage(
            OffenseException::INCORRECT_AIR_DAMAGE_VALUE . OffenseInterface::MIN_DAMAGE . '-' . OffenseInterface::MAX_DAMAGE
        );
        $offense->setAirDamage(OffenseInterface::MIN_DAMAGE - 1);
    }

    /**
     * Тест на ошибку, когда в Offense пытаются записать слишком высокое значение урона воздухом
     *
     * @throws Exception
     */
    public function testOffenseSetUltraMaxAirDamage(): void
    {
        $offense = $this->createOffence();

        $this->expectException(OffenseException::class);
        $this->expectExceptionMessage(
            OffenseException::INCORRECT_AIR_DAMAGE_VALUE . OffenseInterface::MIN_DAMAGE . '-' . OffenseInterface::MAX_DAMAGE
        );
        $offense->setAirDamage(OffenseInterface::MAX_DAMAGE + 1);
    }

    /**
     * Тест на ошибку, когда в Offense пытаются записать слишком низкое значение урона землей
     *
     * @throws Exception
     */
    public function testOffenseSetUltraMinEarthDamage(): void
    {
        $offense = $this->createOffence();

        $this->expectException(OffenseException::class);
        $this->expectExceptionMessage(
            OffenseException::INCORRECT_EARTH_DAMAGE_VALUE . OffenseInterface::MIN_DAMAGE . '-' . OffenseInterface::MAX_DAMAGE
        );
        $offense->setEarthDamage(OffenseInterface::MIN_DAMAGE - 1);
    }

    /**
     * Тест на ошибку, когда в Offense пытаются записать слишком высокое значение урона землей
     *
     * @throws Exception
     */
    public function testOffenseSetUltraMaxEarthDamage(): void
    {
        $offense = $this->createOffence();

        $this->expectException(OffenseException::class);
        $this->expectExceptionMessage(
            OffenseException::INCORRECT_EARTH_DAMAGE_VALUE . OffenseInterface::MIN_DAMAGE . '-' . OffenseInterface::MAX_DAMAGE
        );
        $offense->setEarthDamage(OffenseInterface::MAX_DAMAGE + 1);
    }

    /**
     * Тест на ошибку, когда в Offense пытаются записать слишком низкое значение урона магией жизни
     *
     * @throws Exception
     */
    public function testOffenseSetUltraMinLifeDamage(): void
    {
        $offense = $this->createOffence();

        $this->expectException(OffenseException::class);
        $this->expectExceptionMessage(
            OffenseException::INCORRECT_LIFE_DAMAGE_VALUE . OffenseInterface::MIN_DAMAGE . '-' . OffenseInterface::MAX_DAMAGE
        );
        $offense->setLifeDamage(OffenseInterface::MIN_DAMAGE - 1);
    }

    /**
     * Тест на ошибку, когда в Offense пытаются записать слишком высокое значение урона магией жизни
     *
     * @throws Exception
     */
    public function testOffenseSetUltraMaxLifeDamage(): void
    {
        $offense = $this->createOffence();

        $this->expectException(OffenseException::class);
        $this->expectExceptionMessage(
            OffenseException::INCORRECT_LIFE_DAMAGE_VALUE . OffenseInterface::MIN_DAMAGE . '-' . OffenseInterface::MAX_DAMAGE
        );
        $offense->setLifeDamage(OffenseInterface::MAX_DAMAGE + 1);
    }

    /**
     * Тест на ошибку, когда в Offense пытаются записать слишком низкое значение урона магией смерти
     *
     * @throws Exception
     */
    public function testOffenseSetUltraMinDeathDamage(): void
    {
        $offense = $this->createOffence();

        $this->expectException(OffenseException::class);
        $this->expectExceptionMessage(
            OffenseException::INCORRECT_DEATH_DAMAGE_VALUE . OffenseInterface::MIN_DAMAGE . '-' . OffenseInterface::MAX_DAMAGE
        );
        $offense->setDeathDamage(OffenseInterface::MIN_DAMAGE - 1);
    }

    /**
     * Тест на ошибку, когда в Offense пытаются записать слишком высокое значение урона магией смерти
     *
     * @throws Exception
     */
    public function testOffenseSetUltraMaxDeathDamage(): void
    {
        $offense = $this->createOffence();

        $this->expectException(OffenseException::class);
        $this->expectExceptionMessage(
            OffenseException::INCORRECT_DEATH_DAMAGE_VALUE . OffenseInterface::MIN_DAMAGE . '-' . OffenseInterface::MAX_DAMAGE
        );
        $offense->setDeathDamage(OffenseInterface::MAX_DAMAGE + 1);
    }

    /**
     * Тест на ошибку, когда в Offense пытаются записать слишком низкое значение скорости атаки
     *
     * @throws Exception
     */
    public function testOffenseSetUltraMinAttackSpeed(): void
    {
        $offense = $this->createOffence();

        $this->expectException(OffenseException::class);
        $this->expectExceptionMessage(
            OffenseException::INCORRECT_ATTACK_SPEED_VALUE . OffenseInterface::MIN_ATTACK_SPEED . '-' . OffenseInterface::MAX_ATTACK_SPEED
        );
        $offense->setAttackSpeed(OffenseInterface::MIN_ATTACK_SPEED - 1);
    }

    /**
     * Тест на ошибку, когда в Offense пытаются записать слишком высокое значение скорости атаки
     *
     * @throws Exception
     */
    public function testOffenseSetUltraMaxAttackSpeed(): void
    {
        $offense = $this->createOffence();

        $this->expectException(OffenseException::class);
        $this->expectExceptionMessage(
            OffenseException::INCORRECT_ATTACK_SPEED_VALUE . OffenseInterface::MIN_ATTACK_SPEED . '-' . OffenseInterface::MAX_ATTACK_SPEED
        );
        $offense->setAttackSpeed(OffenseInterface::MAX_ATTACK_SPEED + 0.01);
    }

    /**
     * Тест на ошибку, когда в Offense пытаются записать слишком низкое значение меткости
     *
     * @throws Exception
     */
    public function testOffenseSetUltraMinAccuracy(): void
    {
        $offense = $this->createOffence();

        $this->expectException(OffenseException::class);
        $this->expectExceptionMessage(
            OffenseException::INCORRECT_ACCURACY_VALUE . OffenseInterface::MIN_ACCURACY . '-' . OffenseInterface::MAX_ACCURACY
        );
        $offense->setAccuracy(OffenseInterface::MIN_ACCURACY - 1);
    }

    /**
     * Тест на ошибку, когда в Offense пытаются записать слишком высокое значение меткости
     *
     * @throws Exception
     */
    public function testOffenseSetUltraMaxAccuracy(): void
    {
        $offense = $this->createOffence();

        $this->expectException(OffenseException::class);
        $this->expectExceptionMessage(
            OffenseException::INCORRECT_ACCURACY_VALUE . OffenseInterface::MIN_ACCURACY . '-' . OffenseInterface::MAX_ACCURACY
        );
        $offense->setAccuracy(OffenseInterface::MAX_ACCURACY + 1);
    }

    /**
     * Тест на ошибку, когда в Offense пытаются записать слишком низкое значение магической меткости
     *
     * @throws Exception
     */
    public function testOffenseSetUltraMinMagicAccuracy(): void
    {
        $offense = $this->createOffence();

        $this->expectException(OffenseException::class);
        $this->expectExceptionMessage(
            OffenseException::INCORRECT_MAGIC_ACCURACY_VALUE . OffenseInterface::MIN_MAGIC_ACCURACY . '-' . OffenseInterface::MAX_MAGIC_ACCURACY
        );
        $offense->setMagicAccuracy(OffenseInterface::MIN_MAGIC_ACCURACY - 1);
    }

    /**
     * Тест на ошибку, когда в Offense пытаются записать слишком высокое значение магической меткости
     *
     * @throws Exception
     */
    public function testOffenseSetUltraMaxMagicAccuracy(): void
    {
        $offense = $this->createOffence();

        $this->expectException(OffenseException::class);
        $this->expectExceptionMessage(
            OffenseException::INCORRECT_MAGIC_ACCURACY_VALUE . OffenseInterface::MIN_MAGIC_ACCURACY . '-' . OffenseInterface::MAX_MAGIC_ACCURACY
        );
        $offense->setMagicAccuracy(OffenseInterface::MAX_MAGIC_ACCURACY + 1);
    }

    /**
     * Тест на ошибку, когда в Offense пытаются записать слишком низкое значение игнорирования блока
     *
     * @throws Exception
     */
    public function testOffenseSetUltraMinBlockIgnore(): void
    {
        $offense = $this->createOffence();

        $this->expectException(OffenseException::class);
        $this->expectExceptionMessage(
            OffenseException::INCORRECT_BLOCK_IGNORE_VALUE . OffenseInterface::MIN_BLOCK_IGNORE . '-' . OffenseInterface::MAX_BLOCK_IGNORE
        );
        $offense->setBlockIgnore(OffenseInterface::MIN_BLOCK_IGNORE - 1);
    }

    /**
     * Тест на ошибку, когда в Offense пытаются записать слишком высокое значение игнорирования блока
     *
     * @throws Exception
     */
    public function testOffenseSetUltraMaxBlockIgnore(): void
    {
        $offense = $this->createOffence();

        $this->expectException(OffenseException::class);
        $this->expectExceptionMessage(
            OffenseException::INCORRECT_BLOCK_IGNORE_VALUE . OffenseInterface::MIN_BLOCK_IGNORE . '-' . OffenseInterface::MAX_BLOCK_IGNORE
        );
        $offense->setBlockIgnore(OffenseInterface::MAX_BLOCK_IGNORE + 1);
    }

    /**
     * Тест на ситуацию, когда передан некорректный тип урона
     *
     * @throws Exception
     */
    public function testOffenseInvalidTypeDamage(): void
    {
        $this->expectException(OffenseException::class);
        $this->expectExceptionMessage(OffenseException::INCORRECT_DAMAGE_TYPE_VALUE);
        new Offense(3, 1, 10, 0, 0, 0, 0, 0, 0, 1, 100, 50, 0, 5, 200, 7);
    }

    /**
     * Тест на ошибку, когда в Offense пытаются записать слишком низкое значение шанса критического удара
     *
     * @throws Exception
     */
    public function testOffenseSetUltraMinCriticalChance(): void
    {
        $offense = $this->createOffence();

        $this->expectException(OffenseException::class);
        $this->expectExceptionMessage(
            OffenseException::INCORRECT_CRITICAL_CHANCE_VALUE . OffenseInterface::MIN_CRITICAL_CHANCE . '-' . OffenseInterface::MAX_CRITICAL_CHANCE
        );
        $offense->setCriticalChance(OffenseInterface::MIN_CRITICAL_CHANCE - 1);
    }

    /**
     * Тест на ошибку, когда в Offense пытаются записать слишком высокое значение шанса критического удара
     *
     * @throws Exception
     */
    public function testOffenseSetUltraMaxCriticalChance(): void
    {
        $offense = $this->createOffence();

        $this->expectException(OffenseException::class);
        $this->expectExceptionMessage(
            OffenseException::INCORRECT_CRITICAL_CHANCE_VALUE . OffenseInterface::MIN_CRITICAL_CHANCE . '-' . OffenseInterface::MAX_CRITICAL_CHANCE
        );
        $offense->setCriticalChance(OffenseInterface::MAX_CRITICAL_CHANCE + 1);
    }

    /**
     * Тест на ошибку, когда в Offense пытаются записать слишком низкое значение силы критического удара
     *
     * @throws Exception
     */
    public function testOffenseSetUltraMinCriticalMultiplier(): void
    {
        $offense = $this->createOffence();

        $this->expectException(OffenseException::class);
        $this->expectExceptionMessage(
            OffenseException::INCORRECT_CRITICAL_MULTIPLIER_VALUE . OffenseInterface::MIN_CRITICAL_MULTIPLIER . '-' . OffenseInterface::MAX_CRITICAL_MULTIPLIER
        );
        $offense->setCriticalMultiplier(OffenseInterface::MIN_CRITICAL_MULTIPLIER - 1);
    }

    /**
     * Тест на ошибку, когда в Offense пытаются записать слишком высокое значение силы критического удара
     *
     * @throws Exception
     */
    public function testOffenseSetUltraMaxCriticalMultiplier(): void
    {
        $offense = $this->createOffence();

        $this->expectException(OffenseException::class);
        $this->expectExceptionMessage(
            OffenseException::INCORRECT_CRITICAL_MULTIPLIER_VALUE . OffenseInterface::MIN_CRITICAL_MULTIPLIER . '-' . OffenseInterface::MAX_CRITICAL_MULTIPLIER
        );
        $offense->setCriticalMultiplier(OffenseInterface::MAX_CRITICAL_MULTIPLIER + 1);
    }

    /**
     * Тест на ошибку, когда в Offense пытаются записать слишком низкое значение вампиризма
     *
     * @throws Exception
     */
    public function testOffenseSetUltraMinVampire(): void
    {
        $offense = $this->createOffence();

        $this->expectException(OffenseException::class);
        $this->expectExceptionMessage(
            OffenseException::INCORRECT_VAMPIRE_VALUE . OffenseInterface::MIN_VAMPIRE . '-' . OffenseInterface::MAX_VAMPIRE
        );
        $offense->setVampire(OffenseInterface::MIN_VAMPIRE - 1);
    }

    /**
     * Тест на ошибку, когда в Offense пытаются записать слишком высокое значение вампиризма
     *
     * @throws Exception
     */
    public function testOffenseSetUltraMaxVampire(): void
    {
        $offense = $this->createOffence();

        $this->expectException(OffenseException::class);
        $this->expectExceptionMessage(
            OffenseException::INCORRECT_VAMPIRE_VALUE . OffenseInterface::MIN_VAMPIRE . '-' . OffenseInterface::MAX_VAMPIRE
        );
        $offense->setVampire(OffenseInterface::MAX_VAMPIRE + 1);
    }

    /**
     * @dataProvider resistDataProvider
     * @param int $physicalDamage
     * @param int $physicalResist
     * @param int $exceptedDamage
     * @throws Exception
     */
    public function testOffenseGetDamage(int $physicalDamage, int $physicalResist, int $exceptedDamage): void
    {
        $offense = new Offense(1, 1, $physicalDamage, 0, 0, 0, 0, 0, 0, 1, 100, 50, 0, 5, 200, 7);
        $defense = new Defense($physicalResist, 10, 10, 10, 5, 0);

        self::assertEquals($exceptedDamage, $offense->getDamage($defense));
    }

    /**
     * @return array
     */
    public function resistDataProvider(): array
    {
        return [
            [
                100,
                0,
                100,
            ],
            [
                100,
                25,
                75,
            ],
            [
                100,
                50,
                50,
            ],
            [
                100,
                75,
                25,
            ],
            [
                100,
                100,
                0,
            ],
            [
                100,
                -100,
                200,
            ],
        ];
    }

    /**
     * @return OffenseInterface
     * @throws Exception
     */
    private function createOffence(): OffenseInterface
    {
        return new Offense(
            1,
            1,
            20,
            21,
            22,
            23,
            24,
            25,
            26,
            1,
            100,
            50,
            0,
            5,
            200,
            7
        );
    }
}
