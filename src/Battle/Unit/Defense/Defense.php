<?php

declare(strict_types=1);

namespace Battle\Unit\Defense;

class Defense implements DefenseInterface
{
    /**
     * @var int
     */
    private int $physicalResist;

    /**
     * @var int
     */
    private int $fireResist;

    /**
     * @var int
     */
    private int $waterResist;

    /**
     * @var int
     */
    private int $airResist;

    /**
     * @var int
     */
    private int $earthResist;

    /**
     * @var int
     */
    private int $lifeResist;

    /**
     * @var int
     */
    private int $deathResist;

    /**
     * @var int
     */
    private int $defense;

    /**
     * @var int
     */
    private int $magicDefense;

    /**
     * @var int - Блок (0-100%)
     */
    private int $block;

    /**
     * @var int - Магический блок (0-100%)
     */
    private int $magicBlock;

    /**
     * @var int - Ментальный барьер (0-100%)
     */
    private int $mentalBarrier;

    /**
     * @var int - Максимальное сопротивление физическому урону
     */
    private int $physicalMaxResist;

    /**
     * @var int - Максимальное сопротивление урону огнем
     */
    private int $fireMaxResist;

    /**
     * @var int - Максимальное сопротивление урону водой
     */
    private int $waterMaxResist;

    /**
     * @var int - Максимальное сопротивление урону воздухом
     */
    private int $airMaxResist;

    /**
     * @var int - Максимальное сопротивление урону землей
     */
    private int $earthMaxResist;

    /**
     * @var int - Максимальное сопротивление урону магией жизни
     */
    private int $lifeMaxResist;

    /**
     * @var int - Максимальное сопротивление урону магией смерти
     */
    private int $deathMaxResist;

    /**
     * @var int - Общий множитель получаемого урона
     */
    private int $globalResist;

    /**
     * @var int - Dodge. Вероятность уклониться от атаки/заклинания противника, не зависящее от меткости противника
     */
    private int $dodge;

    /**
     * @param int $physicalResist
     * @param int $fireResist
     * @param int $waterResist
     * @param int $airResist
     * @param int $earthResist
     * @param int $lifeResist
     * @param int $deathResist
     * @param int $defense
     * @param int $magicDefense
     * @param int $block
     * @param int $magicBlock
     * @param int $mentalBarrier
     * @param int $physicalMaxResist
     * @param int $fireMaxResist
     * @param int $waterMaxResist
     * @param int $airMaxResist
     * @param int $earthMaxResist
     * @param int $lifeMaxResist
     * @param int $deathMaxResist
     * @param int $globalResist
     * @param int $dodge
     * @throws DefenseException
     */
    public function __construct(
        int $physicalResist,
        int $fireResist,
        int $waterResist,
        int $airResist,
        int $earthResist,
        int $lifeResist,
        int $deathResist,
        int $defense,
        int $magicDefense,
        int $block,
        int $magicBlock,
        int $mentalBarrier,
        int $physicalMaxResist,
        int $fireMaxResist,
        int $waterMaxResist,
        int $airMaxResist,
        int $earthMaxResist,
        int $lifeMaxResist,
        int $deathMaxResist,
        int $globalResist,
        int $dodge
    )
    {
        $this->setPhysicalResist($physicalResist);
        $this->setFireResist($fireResist);
        $this->setWaterResist($waterResist);
        $this->setAirResist($airResist);
        $this->setEarthResist($earthResist);
        $this->setLifeResist($lifeResist);
        $this->setDeathResist($deathResist);
        $this->setDefense($defense);
        $this->setMagicDefense($magicDefense);
        $this->setBlock($block);
        $this->setMagicBlock($magicBlock);
        $this->setMentalBarrier($mentalBarrier);
        $this->setPhysicalMaxResist($physicalMaxResist);
        $this->setFireMaxResist($fireMaxResist);
        $this->setWaterMaxResist($waterMaxResist);
        $this->setAirMaxResist($airMaxResist);
        $this->setEarthMaxResist($earthMaxResist);
        $this->setLifeMaxResist($lifeMaxResist);
        $this->setDeathMaxResist($deathMaxResist);
        $this->setGlobalResist($globalResist);
        $this->setDodge($dodge);
    }

    /**
     * TODO Добавить проверки, что текущее сопротивление ниже максимального, если выше - возвращаем максимальное сопротивление, оставляя текущее больше максимального
     *
     * @return int
     */
    public function getPhysicalResist(): int
    {
        return $this->physicalResist;
    }

    /**
     * @param int $physicalResist
     * @throws DefenseException
     */
    public function setPhysicalResist(int $physicalResist): void
    {
        if ($physicalResist < self::MIN_RESISTANCE || $physicalResist > self::MAX_RESISTANCE) {
            throw new DefenseException(
                DefenseException::INCORRECT_PHYSICAL_RESIST_VALUE . DefenseInterface::MIN_RESISTANCE . '-' . DefenseInterface::MAX_RESISTANCE
            );
        }

        $this->physicalResist = $physicalResist;
    }

    /**
     * @return int
     */
    public function getFireResist(): int
    {
        return $this->fireResist;
    }

    /**
     * @param int $fireResist
     * @throws DefenseException
     */
    public function setFireResist(int $fireResist): void
    {
        if ($fireResist < self::MIN_RESISTANCE || $fireResist > self::MAX_RESISTANCE) {
            throw new DefenseException(
                DefenseException::INCORRECT_FIRE_RESIST_VALUE . DefenseInterface::MIN_RESISTANCE . '-' . DefenseInterface::MAX_RESISTANCE
            );
        }

        $this->fireResist = $fireResist;
    }

    /**
     * @return int
     */
    public function getWaterResist(): int
    {
        return $this->waterResist;
    }

    /**
     * @param int $waterResist
     * @throws DefenseException
     */
    public function setWaterResist(int $waterResist): void
    {
        if ($waterResist < self::MIN_RESISTANCE || $waterResist > self::MAX_RESISTANCE) {
            throw new DefenseException(
                DefenseException::INCORRECT_WATER_RESIST_VALUE . DefenseInterface::MIN_RESISTANCE . '-' . DefenseInterface::MAX_RESISTANCE
            );
        }

        $this->waterResist = $waterResist;
    }

    /**
     * @return int
     */
    public function getAirResist(): int
    {
        return $this->airResist;
    }

    /**
     * @param int $airResist
     * @throws DefenseException
     */
    public function setAirResist(int $airResist): void
    {
        if ($airResist < self::MIN_RESISTANCE || $airResist > self::MAX_RESISTANCE) {
            throw new DefenseException(
                DefenseException::INCORRECT_AIR_RESIST_VALUE . DefenseInterface::MIN_RESISTANCE . '-' . DefenseInterface::MAX_RESISTANCE
            );
        }

        $this->airResist = $airResist;
    }

    /**
     * @return int
     */
    public function getEarthResist(): int
    {
        return $this->earthResist;
    }

    /**
     * @param int $earthResist
     * @throws DefenseException
     */
    public function setEarthResist(int $earthResist): void
    {
        if ($earthResist < self::MIN_RESISTANCE || $earthResist > self::MAX_RESISTANCE) {
            throw new DefenseException(
                DefenseException::INCORRECT_EARTH_RESIST_VALUE . DefenseInterface::MIN_RESISTANCE . '-' . DefenseInterface::MAX_RESISTANCE
            );
        }

        $this->earthResist = $earthResist;
    }

    /**
     * @return int
     */
    public function getLifeResist(): int
    {
        return $this->lifeResist;
    }

    /**
     * @param int $lifeResist
     * @throws DefenseException
     */
    public function setLifeResist(int $lifeResist): void
    {
        if ($lifeResist < self::MIN_RESISTANCE || $lifeResist > self::MAX_RESISTANCE) {
            throw new DefenseException(
                DefenseException::INCORRECT_LIFE_RESIST_VALUE . DefenseInterface::MIN_RESISTANCE . '-' . DefenseInterface::MAX_RESISTANCE
            );
        }

        $this->lifeResist = $lifeResist;
    }

    /**
     * @return int
     */
    public function getDeathResist(): int
    {
        return $this->deathResist;
    }

    /**
     * @param int $deathResist
     * @throws DefenseException
     */
    public function setDeathResist(int $deathResist): void
    {
        if ($deathResist < self::MIN_RESISTANCE || $deathResist > self::MAX_RESISTANCE) {
            throw new DefenseException(
                DefenseException::INCORRECT_DEATH_RESIST_VALUE . DefenseInterface::MIN_RESISTANCE . '-' . DefenseInterface::MAX_RESISTANCE
            );
        }

        $this->deathResist = $deathResist;
    }

    /**
     * @return int
     */
    public function getDefense(): int
    {
        return $this->defense;
    }

    /**
     * @param int $defense
     * @throws DefenseException
     */
    public function setDefense(int $defense): void
    {
        if ($defense < self::MIN_DEFENSE || $defense > self::MAX_DEFENSE) {
            throw new DefenseException(
                DefenseException::INCORRECT_DEFENSE_VALUE . DefenseInterface::MIN_DEFENSE . '-' . DefenseInterface::MAX_DEFENSE
            );
        }

        $this->defense = $defense;
    }

    /**
     * @return int
     */
    public function getMagicDefense(): int
    {
        return $this->magicDefense;
    }

    /**
     * @param int $magicDefense
     * @throws DefenseException
     */
    public function setMagicDefense(int $magicDefense): void
    {
        if ($magicDefense < self::MIN_MAGIC_DEFENSE || $magicDefense > self::MAX_MAGIC_DEFENSE) {
            throw new DefenseException(
                DefenseException::INCORRECT_MAGIC_DEFENSE_VALUE . DefenseInterface::MIN_MAGIC_DEFENSE . '-' . DefenseInterface::MAX_MAGIC_DEFENSE
            );
        }

        $this->magicDefense = $magicDefense;
    }

    /**
     * @return int
     */
    public function getBlock(): int
    {
        return $this->block;
    }

    /**
     * @param int $block
     * @throws DefenseException
     */
    public function setBlock(int $block): void
    {
        if ($block < self::MIN_BLOCK || $block > self::MAX_BLOCK) {
            throw new DefenseException(
                DefenseException::INCORRECT_BLOCK_VALUE . DefenseInterface::MIN_BLOCK . '-' . DefenseInterface::MAX_BLOCK
            );
        }

        $this->block = $block;
    }

    /**
     * @return int
     */
    public function getMagicBlock(): int
    {
        return $this->magicBlock;
    }

    /**
     * @param int $magicBlock
     * @throws DefenseException
     */
    public function setMagicBlock(int $magicBlock): void
    {
        if ($magicBlock < self::MIN_MAGIC_BLOCK || $magicBlock > self::MAX_MAGIC_BLOCK) {
            throw new DefenseException(
                DefenseException::INCORRECT_MAGIC_BLOCK_VALUE . DefenseInterface::MIN_MAGIC_BLOCK . '-' . DefenseInterface::MAX_MAGIC_BLOCK
            );
        }

        $this->magicBlock = $magicBlock;
    }

    /**
     * @return int
     */
    public function getMentalBarrier(): int
    {
        return $this->mentalBarrier;
    }

    /**
     * @param int $mentalBarrier
     * @throws DefenseException
     */
    public function setMentalBarrier(int $mentalBarrier): void
    {
        if ($mentalBarrier < self::MIN_MENTAL_BARRIER || $mentalBarrier > self::MAX_MENTAL_BARRIER) {
            throw new DefenseException(
                DefenseException::INCORRECT_MENTAL_BARRIER_VALUE . DefenseInterface::MIN_MENTAL_BARRIER . '-' . DefenseInterface::MAX_MENTAL_BARRIER
            );
        }

        $this->mentalBarrier = $mentalBarrier;
    }

    /**
     * @return int
     */
    public function getPhysicalMaxResist(): int
    {
        return $this->physicalMaxResist;
    }

    /**
     * @param int $physicalMaxResist
     * @throws DefenseException
     */
    public function setPhysicalMaxResist(int $physicalMaxResist): void
    {
        if ($physicalMaxResist < self::MIN_RESISTANCE || $physicalMaxResist > self::MAX_RESISTANCE) {
            throw new DefenseException(
                DefenseException::INCORRECT_MAX_PHYSICAL_RESIST_VALUE . DefenseInterface::MIN_RESISTANCE . '-' . DefenseInterface::MAX_RESISTANCE
            );
        }

        $this->physicalMaxResist = $physicalMaxResist;
    }

    /**
     * @return int
     */
    public function getFireMaxResist(): int
    {
        return $this->fireMaxResist;
    }

    /**
     * @param int $fireMaxResist
     * @throws DefenseException
     */
    public function setFireMaxResist(int $fireMaxResist): void
    {
        if ($fireMaxResist < self::MIN_RESISTANCE || $fireMaxResist > self::MAX_RESISTANCE) {
            throw new DefenseException(
                DefenseException::INCORRECT_MAX_FIRE_RESIST_VALUE . DefenseInterface::MIN_RESISTANCE . '-' . DefenseInterface::MAX_RESISTANCE
            );
        }

        $this->fireMaxResist = $fireMaxResist;
    }

    /**
     * @return int
     */
    public function getWaterMaxResist(): int
    {
        return $this->waterMaxResist;
    }

    /**
     * @param int $waterMaxResist
     * @throws DefenseException
     */
    public function setWaterMaxResist(int $waterMaxResist): void
    {
        if ($waterMaxResist < self::MIN_RESISTANCE || $waterMaxResist > self::MAX_RESISTANCE) {
            throw new DefenseException(
                DefenseException::INCORRECT_MAX_WATER_RESIST_VALUE . DefenseInterface::MIN_RESISTANCE . '-' . DefenseInterface::MAX_RESISTANCE
            );
        }

        $this->waterMaxResist = $waterMaxResist;
    }

    /**
     * @return int
     */
    public function getAirMaxResist(): int
    {
        return $this->airMaxResist;
    }

    /**
     * @param int $airMaxResist
     * @throws DefenseException
     */
    public function setAirMaxResist(int $airMaxResist): void
    {
        if ($airMaxResist < self::MIN_RESISTANCE || $airMaxResist > self::MAX_RESISTANCE) {
            throw new DefenseException(
                DefenseException::INCORRECT_MAX_AIR_RESIST_VALUE . DefenseInterface::MIN_RESISTANCE . '-' . DefenseInterface::MAX_RESISTANCE
            );
        }

        $this->airMaxResist = $airMaxResist;
    }

    /**
     * @return int
     */
    public function getEarthMaxResist(): int
    {
        return $this->earthMaxResist;
    }

    /**
     * @param int $earthMaxResist
     * @throws DefenseException
     */
    public function setEarthMaxResist(int $earthMaxResist): void
    {
        if ($earthMaxResist < self::MIN_RESISTANCE || $earthMaxResist > self::MAX_RESISTANCE) {
            throw new DefenseException(
                DefenseException::INCORRECT_MAX_EARTH_RESIST_VALUE . DefenseInterface::MIN_RESISTANCE . '-' . DefenseInterface::MAX_RESISTANCE
            );
        }

        $this->earthMaxResist = $earthMaxResist;
    }

    /**
     * @return int
     */
    public function getLifeMaxResist(): int
    {
        return $this->lifeMaxResist;
    }

    /**
     * @param int $lifeMaxResist
     * @throws DefenseException
     */
    public function setLifeMaxResist(int $lifeMaxResist): void
    {
        if ($lifeMaxResist < self::MIN_RESISTANCE || $lifeMaxResist > self::MAX_RESISTANCE) {
            throw new DefenseException(
                DefenseException::INCORRECT_MAX_LIFE_RESIST_VALUE . DefenseInterface::MIN_RESISTANCE . '-' . DefenseInterface::MAX_RESISTANCE
            );
        }

        $this->lifeMaxResist = $lifeMaxResist;
    }

    /**
     * @return int
     */
    public function getDeathMaxResist(): int
    {
        return $this->deathMaxResist;
    }

    /**
     * @param int $deathMaxResist
     * @throws DefenseException
     */
    public function setDeathMaxResist(int $deathMaxResist): void
    {
        if ($deathMaxResist < self::MIN_RESISTANCE || $deathMaxResist > self::MAX_RESISTANCE) {
            throw new DefenseException(
                DefenseException::INCORRECT_MAX_DEATH_RESIST_VALUE . DefenseInterface::MIN_RESISTANCE . '-' . DefenseInterface::MAX_RESISTANCE
            );
        }

        $this->deathMaxResist = $deathMaxResist;
    }

    /**
     * @return int
     */
    public function getGlobalResist(): int
    {
        return $this->globalResist;
    }

    /**
     * @param int $globalResist
     * @throws DefenseException
     */
    public function setGlobalResist(int $globalResist): void
    {
        if ($globalResist < self::MIN_RESISTANCE || $globalResist > self::MAX_RESISTANCE) {
            throw new DefenseException(
                DefenseException::INCORRECT_GLOBAL_RESIST_VALUE . DefenseInterface::MIN_RESISTANCE . '-' . DefenseInterface::MAX_RESISTANCE
            );
        }

        $this->globalResist = $globalResist;
    }

    /**
     * @return int
     */
    public function getDodge(): int
    {
        return $this->dodge;
    }

    /**
     * @param int $dodge
     * @throws DefenseException
     */
    public function setDodge(int $dodge): void
    {
        if ($dodge < self::MIN_DODGE || $dodge > self::MAX_DODGE) {
            throw new DefenseException(
                DefenseException::INCORRECT_DODGE_VALUE . DefenseInterface::MIN_DODGE . '-' . DefenseInterface::MAX_DODGE
            );
        }

        $this->dodge = $dodge;
    }
}
