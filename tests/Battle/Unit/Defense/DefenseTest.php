<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Defense;

use Battle\Unit\Defense\Defense;
use Battle\Unit\Defense\DefenseException;
use Battle\Unit\Defense\DefenseInterface;
use Exception;
use Tests\AbstractUnitTest;

class DefenseTest extends AbstractUnitTest
{
    /**
     * Тест на создание Defense
     *
     * @throws Exception
     */
    public function testDefenseCreate(): void
    {
        $physicalResist = 15;
        $fireResist = 25;
        $waterResist = 35;
        $airResist = 45;
        $earthResist = 55;
        $lifeResist = 65;
        $deathResist = 75;
        $defenseValue = 100;
        $magicDefence = 50;
        $block = 0;
        $magicBlock = 10;
        $mentalBarrier = 50;
        $physicalMaxResist = 76;
        $fireMaxResist = 77;
        $waterMaxResist = 78;
        $airMaxResist = 79;
        $earthMaxResist = 80;
        $lifeMaxResist = 81;
        $deathMaxResist = 82;
        $globalResist = 5;
        $dodge = 3;

        $defense = new Defense(
            $physicalResist,
            $fireResist,
            $waterResist,
            $airResist,
            $earthResist,
            $lifeResist,
            $deathResist,
            $defenseValue,
            $magicDefence,
            $block,
            $magicBlock,
            $mentalBarrier,
            $physicalMaxResist,
            $fireMaxResist,
            $waterMaxResist,
            $airMaxResist,
            $earthMaxResist,
            $lifeMaxResist,
            $deathMaxResist,
            $globalResist,
            $dodge
        );

        self::assertEquals($physicalResist, $defense->getPhysicalResist());
        self::assertEquals($fireResist, $defense->getFireResist());
        self::assertEquals($airResist, $defense->getAirResist());
        self::assertEquals($waterResist, $defense->getWaterResist());
        self::assertEquals($earthResist, $defense->getEarthResist());
        self::assertEquals($lifeResist, $defense->getLifeResist());
        self::assertEquals($deathResist, $defense->getDeathResist());
        self::assertEquals($defenseValue, $defense->getDefense());
        self::assertEquals($magicDefence, $defense->getMagicDefense());
        self::assertEquals($block, $defense->getBlock());
        self::assertEquals($magicBlock, $defense->getMagicBlock());
        self::assertEquals($mentalBarrier, $defense->getMentalBarrier());
        self::assertEquals($physicalMaxResist, $defense->getPhysicalMaxResist());
        self::assertEquals($fireMaxResist, $defense->getFireMaxResist());
        self::assertEquals($waterMaxResist, $defense->getWaterMaxResist());
        self::assertEquals($airMaxResist, $defense->getAirMaxResist());
        self::assertEquals($earthMaxResist, $defense->getEarthMaxResist());
        self::assertEquals($lifeMaxResist, $defense->getLifeMaxResist());
        self::assertEquals($deathMaxResist, $defense->getDeathMaxResist());
        self::assertEquals($globalResist, $defense->getGlobalResist());
        self::assertEquals($dodge, $defense->getDodge());
    }

    /**
     * Тест на обновление параметров Defense
     *
     * @throws DefenseException
     */
    public function testDefenseUpdate(): void
    {
        $defense = $this->createDefense();

        $defense->setPhysicalResist($physicalResist = -10);
        $defense->setFireResist($fireResist = -20);
        $defense->setWaterResist($waterResist = -30);
        $defense->setAirResist($airResist = -40);
        $defense->setEarthResist($earthResist = -50);
        $defense->setLifeResist($lifeResist = -60);
        $defense->setDeathResist($deathResist = -70);
        $defense->setDefense($defenseValue = 1000);
        $defense->setMagicDefense($magicDefence = 500);
        $defense->setBlock($block = 5);
        $defense->setMagicBlock($magicBlock = 15);
        $defense->setMentalBarrier($mentalBarrier = 50);
        $defense->setPhysicalMaxResist($physicalMaxResist = 81);
        $defense->setFireMaxResist($fireMaxResist = 82);
        $defense->setWaterMaxResist($waterMaxResist = 83);
        $defense->setAirMaxResist($airMaxResist = 84);
        $defense->setEarthMaxResist($earthMaxResist = 85);
        $defense->setLifeMaxResist($lifeMaxResist = 86);
        $defense->setDeathMaxResist($deathMaxResist = 87);
        $defense->setGlobalResist($globalResist = 10);
        $defense->setDodge($dodge = 3);

        self::assertEquals($physicalResist, $defense->getPhysicalResist());
        self::assertEquals($fireResist, $defense->getFireResist());
        self::assertEquals($waterResist, $defense->getWaterResist());
        self::assertEquals($airResist, $defense->getAirResist());
        self::assertEquals($earthResist, $defense->getEarthResist());
        self::assertEquals($lifeResist, $defense->getLifeResist());
        self::assertEquals($deathResist, $defense->getDeathResist());
        self::assertEquals($defenseValue, $defense->getDefense());
        self::assertEquals($magicDefence, $defense->getMagicDefense());
        self::assertEquals($block, $defense->getBlock());
        self::assertEquals($magicBlock, $defense->getMagicBlock());
        self::assertEquals($mentalBarrier, $defense->getMentalBarrier());
        self::assertEquals($physicalMaxResist, $defense->getPhysicalMaxResist());
        self::assertEquals($fireMaxResist, $defense->getFireMaxResist());
        self::assertEquals($waterMaxResist, $defense->getWaterMaxResist());
        self::assertEquals($airMaxResist, $defense->getAirMaxResist());
        self::assertEquals($earthMaxResist, $defense->getEarthMaxResist());
        self::assertEquals($lifeMaxResist, $defense->getLifeMaxResist());
        self::assertEquals($deathMaxResist, $defense->getDeathMaxResist());
        self::assertEquals($globalResist, $defense->getGlobalResist());
        self::assertEquals($dodge, $defense->getDodge());
    }

    /**
     * Тест на ошибку, когда в Defense пытаются записать слишком низкое значение сопротивления физическому урону
     *
     * @throws DefenseException
     */
    public function testDefenseSetUltraMinPhysicalResist(): void
    {
        $this->expectException(DefenseException::class);
        $this->expectExceptionMessage(
            DefenseException::INCORRECT_PHYSICAL_RESIST_VALUE . DefenseInterface::MIN_RESISTANCE . '-' . DefenseInterface::MAX_RESISTANCE
        );
        $this->createDefense()->setPhysicalResist(DefenseInterface::MIN_RESISTANCE - 1);
    }

    /**
     * Тест на ошибку, когда в Defense пытаются записать слишком большое значение сопротивления физическому урону
     *
     * @throws DefenseException
     */
    public function testDefenseSetUltraMaxPhysicalResist(): void
    {
        $this->expectException(DefenseException::class);
        $this->expectExceptionMessage(
            DefenseException::INCORRECT_PHYSICAL_RESIST_VALUE . DefenseInterface::MIN_RESISTANCE . '-' . DefenseInterface::MAX_RESISTANCE
        );
        $this->createDefense()->setPhysicalResist(DefenseInterface::MAX_RESISTANCE + 1);
    }

    /**
     * Тест на ошибку, когда в Defense пытаются записать слишком низкое значение сопротивления урону огнем
     *
     * @throws DefenseException
     */
    public function testDefenseSetUltraMinFireResist(): void
    {
        $this->expectException(DefenseException::class);
        $this->expectExceptionMessage(
            DefenseException::INCORRECT_FIRE_RESIST_VALUE . DefenseInterface::MIN_RESISTANCE . '-' . DefenseInterface::MAX_RESISTANCE
        );
        $this->createDefense()->setFireResist(DefenseInterface::MIN_RESISTANCE - 1);
    }

    /**
     * Тест на ошибку, когда в Defense пытаются записать слишком большое значение сопротивления урону огнем
     *
     * @throws DefenseException
     */
    public function testDefenseSetUltraMaxFireResist(): void
    {
        $this->expectException(DefenseException::class);
        $this->expectExceptionMessage(
            DefenseException::INCORRECT_FIRE_RESIST_VALUE . DefenseInterface::MIN_RESISTANCE . '-' . DefenseInterface::MAX_RESISTANCE
        );
        $this->createDefense()->setFireResist(DefenseInterface::MAX_RESISTANCE + 1);
    }

    /**
     * Тест на ошибку, когда в Defense пытаются записать слишком низкое значение сопротивления урону водой
     *
     * @throws DefenseException
     */
    public function testDefenseSetUltraMinWaterResist(): void
    {
        $this->expectException(DefenseException::class);
        $this->expectExceptionMessage(
            DefenseException::INCORRECT_WATER_RESIST_VALUE . DefenseInterface::MIN_RESISTANCE . '-' . DefenseInterface::MAX_RESISTANCE
        );
        $this->createDefense()->setWaterResist(DefenseInterface::MIN_RESISTANCE - 1);
    }

    /**
     * Тест на ошибку, когда в Defense пытаются записать слишком большое значение сопротивления урону водой
     *
     * @throws DefenseException
     */
    public function testDefenseSetUltraMaxWaterResist(): void
    {
        $this->expectException(DefenseException::class);
        $this->expectExceptionMessage(
            DefenseException::INCORRECT_WATER_RESIST_VALUE . DefenseInterface::MIN_RESISTANCE . '-' . DefenseInterface::MAX_RESISTANCE
        );
        $this->createDefense()->setWaterResist(DefenseInterface::MAX_RESISTANCE + 1);
    }

    /**
     * Тест на ошибку, когда в Defense пытаются записать слишком низкое значение сопротивления урону воздухом
     *
     * @throws DefenseException
     */
    public function testDefenseSetUltraMinAirResist(): void
    {
        $this->expectException(DefenseException::class);
        $this->expectExceptionMessage(
            DefenseException::INCORRECT_AIR_RESIST_VALUE . DefenseInterface::MIN_RESISTANCE . '-' . DefenseInterface::MAX_RESISTANCE
        );
        $this->createDefense()->setAirResist(DefenseInterface::MIN_RESISTANCE - 1);
    }

    /**
     * Тест на ошибку, когда в Defense пытаются записать слишком большое значение сопротивления урону воздухом
     *
     * @throws DefenseException
     */
    public function testDefenseSetUltraMaxAirResist(): void
    {
        $this->expectException(DefenseException::class);
        $this->expectExceptionMessage(
            DefenseException::INCORRECT_AIR_RESIST_VALUE . DefenseInterface::MIN_RESISTANCE . '-' . DefenseInterface::MAX_RESISTANCE
        );
        $this->createDefense()->setAirResist(DefenseInterface::MAX_RESISTANCE + 1);
    }

    /**
     * Тест на ошибку, когда в Defense пытаются записать слишком низкое значение сопротивления урону землей
     *
     * @throws DefenseException
     */
    public function testDefenseSetUltraMinEarthResist(): void
    {
        $this->expectException(DefenseException::class);
        $this->expectExceptionMessage(
            DefenseException::INCORRECT_EARTH_RESIST_VALUE . DefenseInterface::MIN_RESISTANCE . '-' . DefenseInterface::MAX_RESISTANCE
        );
        $this->createDefense()->setEarthResist(DefenseInterface::MIN_RESISTANCE - 1);
    }

    /**
     * Тест на ошибку, когда в Defense пытаются записать слишком большое значение сопротивления урону землей
     *
     * @throws DefenseException
     */
    public function testDefenseSetUltraMaxEarthResist(): void
    {
        $this->expectException(DefenseException::class);
        $this->expectExceptionMessage(
            DefenseException::INCORRECT_EARTH_RESIST_VALUE . DefenseInterface::MIN_RESISTANCE . '-' . DefenseInterface::MAX_RESISTANCE
        );
        $this->createDefense()->setEarthResist(DefenseInterface::MAX_RESISTANCE + 1);
    }

    /**
     * Тест на ошибку, когда в Defense пытаются записать слишком низкое значение сопротивления урону магией жизни
     *
     * @throws DefenseException
     */
    public function testDefenseSetUltraMinLifeResist(): void
    {
        $this->expectException(DefenseException::class);
        $this->expectExceptionMessage(
            DefenseException::INCORRECT_LIFE_RESIST_VALUE . DefenseInterface::MIN_RESISTANCE . '-' . DefenseInterface::MAX_RESISTANCE
        );
        $this->createDefense()->setLifeResist(DefenseInterface::MIN_RESISTANCE - 1);
    }

    /**
     * Тест на ошибку, когда в Defense пытаются записать слишком большое значение сопротивления урону магией жизни
     *
     * @throws DefenseException
     */
    public function testDefenseSetUltraMaxLifeResist(): void
    {
        $this->expectException(DefenseException::class);
        $this->expectExceptionMessage(
            DefenseException::INCORRECT_LIFE_RESIST_VALUE . DefenseInterface::MIN_RESISTANCE . '-' . DefenseInterface::MAX_RESISTANCE
        );
        $this->createDefense()->setLifeResist(DefenseInterface::MAX_RESISTANCE + 1);
    }

    /**
     * Тест на ошибку, когда в Defense пытаются записать слишком низкое значение сопротивления урону магией смерти
     *
     * @throws DefenseException
     */
    public function testDefenseSetUltraMinDeathResist(): void
    {
        $this->expectException(DefenseException::class);
        $this->expectExceptionMessage(
            DefenseException::INCORRECT_DEATH_RESIST_VALUE . DefenseInterface::MIN_RESISTANCE . '-' . DefenseInterface::MAX_RESISTANCE
        );
        $this->createDefense()->setDeathResist(DefenseInterface::MIN_RESISTANCE - 1);
    }

    /**
     * Тест на ошибку, когда в Defense пытаются записать слишком большое значение сопротивления урону магией смерти
     *
     * @throws DefenseException
     */
    public function testDefenseSetUltraMaxDeathResist(): void
    {
        $this->expectException(DefenseException::class);
        $this->expectExceptionMessage(
            DefenseException::INCORRECT_DEATH_RESIST_VALUE . DefenseInterface::MIN_RESISTANCE . '-' . DefenseInterface::MAX_RESISTANCE
        );
        $this->createDefense()->setDeathResist(DefenseInterface::MAX_RESISTANCE + 1);
    }

    /**
     * Тест на ошибку, когда в Defense пытаются записать слишком низкое значение защиты
     *
     * @throws DefenseException
     */
    public function testDefenseSetUltraMinDefense(): void
    {
        $this->expectException(DefenseException::class);
        $this->expectExceptionMessage(
            DefenseException::INCORRECT_DEFENSE_VALUE . DefenseInterface::MIN_DEFENSE . '-' . DefenseInterface::MAX_DEFENSE
        );
        $this->createDefense()->setDefense(DefenseInterface::MIN_DEFENSE - 1);
    }

    /**
     * Тест на ошибку, когда в Defense пытаются записать слишком большое значение защиты
     *
     * @throws DefenseException
     */
    public function testDefenseSetUltraMaxDefense(): void
    {
        $this->expectException(DefenseException::class);
        $this->expectExceptionMessage(
            DefenseException::INCORRECT_DEFENSE_VALUE . DefenseInterface::MIN_DEFENSE . '-' . DefenseInterface::MAX_DEFENSE
        );
        $this->createDefense()->setDefense(DefenseInterface::MAX_DEFENSE + 1);
    }

    /**
     * Тест на ошибку, когда в Defense пытаются записать слишком низкое значение магической защиты
     *
     * @throws DefenseException
     */
    public function testDefenseSetUltraMinMagicDefense(): void
    {
        $this->expectException(DefenseException::class);
        $this->expectExceptionMessage(
            DefenseException::INCORRECT_MAGIC_DEFENSE_VALUE . DefenseInterface::MIN_MAGIC_DEFENSE . '-' . DefenseInterface::MAX_MAGIC_DEFENSE
        );
        $this->createDefense()->setMagicDefense(DefenseInterface::MIN_MAGIC_DEFENSE - 1);
    }

    /**
     * Тест на ошибку, когда в Defense пытаются записать слишком большое значение магической защиты
     *
     * @throws DefenseException
     */
    public function testDefenseSetUltraMaxMagicDefense(): void
    {
        $this->expectException(DefenseException::class);
        $this->expectExceptionMessage(
            DefenseException::INCORRECT_MAGIC_DEFENSE_VALUE . DefenseInterface::MIN_MAGIC_DEFENSE . '-' . DefenseInterface::MAX_MAGIC_DEFENSE
        );
        $this->createDefense()->setMagicDefense(DefenseInterface::MAX_MAGIC_DEFENSE + 1);
    }

    /**
     * Тест на ошибку, когда в Defense пытаются записать слишком низкое значение блока
     *
     * @throws DefenseException
     */
    public function testDefenseSetUltraMinBlock(): void
    {
        $this->expectException(DefenseException::class);
        $this->expectExceptionMessage(
            DefenseException::INCORRECT_BLOCK_VALUE . DefenseInterface::MIN_BLOCK . '-' . DefenseInterface::MAX_BLOCK
        );
        $this->createDefense()->setBlock(DefenseInterface::MIN_BLOCK - 1);
    }

    /**
     * Тест на ошибку, когда в Defense пытаются записать слишком большое значение блока
     *
     * @throws DefenseException
     */
    public function testDefenseSetUltraMaxBlock(): void
    {
        $this->expectException(DefenseException::class);
        $this->expectExceptionMessage(
            DefenseException::INCORRECT_BLOCK_VALUE . DefenseInterface::MIN_BLOCK . '-' . DefenseInterface::MAX_BLOCK
        );
        $this->createDefense()->setBlock(DefenseInterface::MAX_BLOCK + 1);
    }

    /**
     * Тест на ошибку, когда в Defense пытаются записать слишком низкое значение магического блока
     *
     * @throws DefenseException
     */
    public function testDefenseSetUltraMinMagicBlock(): void
    {
        $this->expectException(DefenseException::class);
        $this->expectExceptionMessage(
            DefenseException::INCORRECT_MAGIC_BLOCK_VALUE . DefenseInterface::MIN_MAGIC_BLOCK . '-' . DefenseInterface::MAX_MAGIC_BLOCK
        );
        $this->createDefense()->setMagicBlock(DefenseInterface::MIN_MAGIC_BLOCK - 1);
    }

    /**
     * Тест на ошибку, когда в Defense пытаются записать слишком большое значение магического блока
     *
     * @throws DefenseException
     */
    public function testDefenseSetUltraMaxMagicBlock(): void
    {
        $this->expectException(DefenseException::class);
        $this->expectExceptionMessage(
            DefenseException::INCORRECT_MAGIC_BLOCK_VALUE . DefenseInterface::MIN_MAGIC_BLOCK . '-' . DefenseInterface::MAX_MAGIC_BLOCK
        );
        $this->createDefense()->setMagicBlock(DefenseInterface::MAX_MAGIC_BLOCK + 1);
    }

    /**
     * Тест на ошибку, когда в Defense пытаются записать слишком низкое значение ментального барьера
     *
     * @throws DefenseException
     */
    public function testDefenseSetUltraMinMentalBarrier(): void
    {
        $this->expectException(DefenseException::class);
        $this->expectExceptionMessage(
            DefenseException::INCORRECT_MENTAL_BARRIER_VALUE . DefenseInterface::MIN_MENTAL_BARRIER . '-' . DefenseInterface::MAX_MENTAL_BARRIER
        );
        $this->createDefense()->setMentalBarrier(DefenseInterface::MIN_MENTAL_BARRIER - 1);
    }

    /**
     * Тест на ошибку, когда в Defense пытаются записать слишком большое значение ментального барьера
     *
     * @throws DefenseException
     */
    public function testDefenseSetUltraMaxMentalBarrier(): void
    {
        $this->expectException(DefenseException::class);
        $this->expectExceptionMessage(
            DefenseException::INCORRECT_MENTAL_BARRIER_VALUE . DefenseInterface::MIN_MENTAL_BARRIER . '-' . DefenseInterface::MAX_MENTAL_BARRIER
        );
        $this->createDefense()->setMentalBarrier(DefenseInterface::MAX_MENTAL_BARRIER + 1);
    }

    /**
     * Тест на ошибку, когда в Defense пытаются записать слишком низкое значение PhysicalMaxResist
     *
     * @throws DefenseException
     */
    public function testDefenseSetUltraMinPhysicalMaxResist(): void
    {
        $this->expectException(DefenseException::class);
        $this->expectExceptionMessage(
            DefenseException::INCORRECT_MAX_PHYSICAL_RESIST_VALUE . DefenseInterface::MIN_RESISTANCE . '-' . DefenseInterface::MAX_RESISTANCE
        );
        $this->createDefense()->setPhysicalMaxResist(DefenseInterface::MIN_RESISTANCE - 1);
    }

    /**
     * Тест на ошибку, когда в Defense пытаются записать слишком большое значение PhysicalMaxResist
     *
     * @throws DefenseException
     */
    public function testDefenseSetUltraMaxPhysicalMaxResist(): void
    {
        $this->expectException(DefenseException::class);
        $this->expectExceptionMessage(
            DefenseException::INCORRECT_MAX_PHYSICAL_RESIST_VALUE . DefenseInterface::MIN_RESISTANCE . '-' . DefenseInterface::MAX_RESISTANCE
        );
        $this->createDefense()->setPhysicalMaxResist(DefenseInterface::MAX_RESISTANCE + 1);
    }

    /**
     * Тест на ошибку, когда в Defense пытаются записать слишком низкое значение FireMaxResist
     *
     * @throws DefenseException
     */
    public function testDefenseSetUltraMinFireMaxResist(): void
    {
        $this->expectException(DefenseException::class);
        $this->expectExceptionMessage(
            DefenseException::INCORRECT_MAX_FIRE_RESIST_VALUE . DefenseInterface::MIN_RESISTANCE . '-' . DefenseInterface::MAX_RESISTANCE
        );
        $this->createDefense()->setFireMaxResist(DefenseInterface::MIN_RESISTANCE - 1);
    }

    /**
     * Тест на ошибку, когда в Defense пытаются записать слишком большое значение FireMaxResist
     *
     * @throws DefenseException
     */
    public function testDefenseSetUltraMaxFireMaxResist(): void
    {
        $this->expectException(DefenseException::class);
        $this->expectExceptionMessage(
            DefenseException::INCORRECT_MAX_FIRE_RESIST_VALUE . DefenseInterface::MIN_RESISTANCE . '-' . DefenseInterface::MAX_RESISTANCE
        );
        $this->createDefense()->setFireMaxResist(DefenseInterface::MAX_RESISTANCE + 1);
    }

    /**
     * Тест на ошибку, когда в Defense пытаются записать слишком низкое значение WaterMaxResist
     *
     * @throws DefenseException
     */
    public function testDefenseSetUltraMinWaterMaxResist(): void
    {
        $this->expectException(DefenseException::class);
        $this->expectExceptionMessage(
            DefenseException::INCORRECT_MAX_WATER_RESIST_VALUE . DefenseInterface::MIN_RESISTANCE . '-' . DefenseInterface::MAX_RESISTANCE
        );
        $this->createDefense()->setWaterMaxResist(DefenseInterface::MIN_RESISTANCE - 1);
    }

    /**
     * Тест на ошибку, когда в Defense пытаются записать слишком большое значение WaterMaxResist
     *
     * @throws DefenseException
     */
    public function testDefenseSetUltraMaxWaterMaxResist(): void
    {
        $this->expectException(DefenseException::class);
        $this->expectExceptionMessage(
            DefenseException::INCORRECT_MAX_WATER_RESIST_VALUE . DefenseInterface::MIN_RESISTANCE . '-' . DefenseInterface::MAX_RESISTANCE
        );
        $this->createDefense()->setWaterMaxResist(DefenseInterface::MAX_RESISTANCE + 1);
    }

    /**
     * Тест на ошибку, когда в Defense пытаются записать слишком низкое значение AirMaxResist
     *
     * @throws DefenseException
     */
    public function testDefenseSetUltraMinAirMaxResist(): void
    {
        $this->expectException(DefenseException::class);
        $this->expectExceptionMessage(
            DefenseException::INCORRECT_MAX_AIR_RESIST_VALUE . DefenseInterface::MIN_RESISTANCE . '-' . DefenseInterface::MAX_RESISTANCE
        );
        $this->createDefense()->setAirMaxResist(DefenseInterface::MIN_RESISTANCE - 1);
    }

    /**
     * Тест на ошибку, когда в Defense пытаются записать слишком большое значение AirMaxResist
     *
     * @throws DefenseException
     */
    public function testDefenseSetUltraMaxAirMaxResist(): void
    {
        $this->expectException(DefenseException::class);
        $this->expectExceptionMessage(
            DefenseException::INCORRECT_MAX_AIR_RESIST_VALUE . DefenseInterface::MIN_RESISTANCE . '-' . DefenseInterface::MAX_RESISTANCE
        );
        $this->createDefense()->setAirMaxResist(DefenseInterface::MAX_RESISTANCE + 1);
    }

    /**
     * Тест на ошибку, когда в Defense пытаются записать слишком низкое значение EarthMaxResist
     *
     * @throws DefenseException
     */
    public function testDefenseSetUltraMinEarthMaxResist(): void
    {
        $this->expectException(DefenseException::class);
        $this->expectExceptionMessage(
            DefenseException::INCORRECT_MAX_EARTH_RESIST_VALUE . DefenseInterface::MIN_RESISTANCE . '-' . DefenseInterface::MAX_RESISTANCE
        );
        $this->createDefense()->setEarthMaxResist(DefenseInterface::MIN_RESISTANCE - 1);
    }

    /**
     * Тест на ошибку, когда в Defense пытаются записать слишком большое значение EarthMaxResist
     *
     * @throws DefenseException
     */
    public function testDefenseSetUltraMaxEarthMaxResist(): void
    {
        $this->expectException(DefenseException::class);
        $this->expectExceptionMessage(
            DefenseException::INCORRECT_MAX_EARTH_RESIST_VALUE . DefenseInterface::MIN_RESISTANCE . '-' . DefenseInterface::MAX_RESISTANCE
        );
        $this->createDefense()->setEarthMaxResist(DefenseInterface::MAX_RESISTANCE + 1);
    }

    /**
     * Тест на ошибку, когда в Defense пытаются записать слишком низкое значение LifeMaxResist
     *
     * @throws DefenseException
     */
    public function testDefenseSetUltraMinLifeMaxResist(): void
    {
        $this->expectException(DefenseException::class);
        $this->expectExceptionMessage(
            DefenseException::INCORRECT_MAX_LIFE_RESIST_VALUE . DefenseInterface::MIN_RESISTANCE . '-' . DefenseInterface::MAX_RESISTANCE
        );
        $this->createDefense()->setLifeMaxResist(DefenseInterface::MIN_RESISTANCE - 1);
    }

    /**
     * Тест на ошибку, когда в Defense пытаются записать слишком большое значение LifeMaxResist
     *
     * @throws DefenseException
     */
    public function testDefenseSetUltraMaxLifeMaxResist(): void
    {
        $this->expectException(DefenseException::class);
        $this->expectExceptionMessage(
            DefenseException::INCORRECT_MAX_LIFE_RESIST_VALUE . DefenseInterface::MIN_RESISTANCE . '-' . DefenseInterface::MAX_RESISTANCE
        );
        $this->createDefense()->setLifeMaxResist(DefenseInterface::MAX_RESISTANCE + 1);
    }

    /**
     * Тест на ошибку, когда в Defense пытаются записать слишком низкое значение DeathMaxResist
     *
     * @throws DefenseException
     */
    public function testDefenseSetUltraMinDeathMaxResist(): void
    {
        $this->expectException(DefenseException::class);
        $this->expectExceptionMessage(
            DefenseException::INCORRECT_MAX_DEATH_RESIST_VALUE . DefenseInterface::MIN_RESISTANCE . '-' . DefenseInterface::MAX_RESISTANCE
        );
        $this->createDefense()->setDeathMaxResist(DefenseInterface::MIN_RESISTANCE - 1);
    }

    /**
     * Тест на ошибку, когда в Defense пытаются записать слишком большое значение DeathMaxResist
     *
     * @throws DefenseException
     */
    public function testDefenseSetUltraMaxDeathMaxResist(): void
    {
        $this->expectException(DefenseException::class);
        $this->expectExceptionMessage(
            DefenseException::INCORRECT_MAX_DEATH_RESIST_VALUE . DefenseInterface::MIN_RESISTANCE . '-' . DefenseInterface::MAX_RESISTANCE
        );
        $this->createDefense()->setDeathMaxResist(DefenseInterface::MAX_RESISTANCE + 1);
    }

    /**
     * Тест на ошибку, когда в Defense пытаются записать слишком низкое значение GlobalResist
     *
     * @throws DefenseException
     */
    public function testDefenseSetUltraMinGlobalResist(): void
    {
        $this->expectException(DefenseException::class);
        $this->expectExceptionMessage(
            DefenseException::INCORRECT_GLOBAL_RESIST_VALUE . DefenseInterface::MIN_RESISTANCE . '-' . DefenseInterface::MAX_RESISTANCE
        );
        $this->createDefense()->setGlobalResist(DefenseInterface::MIN_RESISTANCE - 1);
    }

    /**
     * Тест на ошибку, когда в Defense пытаются записать слишком большое значение GlobalResist
     *
     * @throws DefenseException
     */
    public function testDefenseSetUltraMaxGlobalResist(): void
    {
        $this->expectException(DefenseException::class);
        $this->expectExceptionMessage(
            DefenseException::INCORRECT_GLOBAL_RESIST_VALUE . DefenseInterface::MIN_RESISTANCE . '-' . DefenseInterface::MAX_RESISTANCE
        );
        $this->createDefense()->setGlobalResist(DefenseInterface::MAX_RESISTANCE + 1);
    }

    /**
     * Тест на ошибку, когда в Defense пытаются записать слишком низкое значение Dodge
     *
     * @throws DefenseException
     */
    public function testDefenseSetUltraMinDodge(): void
    {
        $this->expectException(DefenseException::class);
        $this->expectExceptionMessage(
            DefenseException::INCORRECT_DODGE_VALUE . DefenseInterface::MIN_DODGE . '-' . DefenseInterface::MAX_DODGE
        );
        $this->createDefense()->setDodge(DefenseInterface::MIN_DODGE - 1);
    }

    /**
     * Тест на ошибку, когда в Defense пытаются записать слишком большое значение Dodge
     *
     * @throws DefenseException
     */
    public function testDefenseSetUltraMaxDodge(): void
    {
        $this->expectException(DefenseException::class);
        $this->expectExceptionMessage(
            DefenseException::INCORRECT_DODGE_VALUE . DefenseInterface::MIN_DODGE . '-' . DefenseInterface::MAX_DODGE
        );
        $this->createDefense()->setDodge(DefenseInterface::MAX_DODGE + 1);
    }

    /**
     * @return DefenseInterface
     * @throws DefenseException
     */
    private function createDefense(): DefenseInterface
    {
        return new Defense(
            0,
            0,
            0,
            0,
            0,
            0,
            0,
            10,
            10,
            10,
            5,
            0,
            75,
            75,
            75,
            75,
            75,
            75,
            75,
            0,
            0
        );
    }
}
