<?php

declare(strict_types=1);

namespace Battle\Unit\Offense;

use Battle\Container\ContainerInterface;
use Battle\Unit\Defense\DefenseInterface;
use Battle\Unit\Offense\MultipleOffense\MultipleOffenseInterface;
use Battle\Weapon\Type\WeaponType;
use Battle\Weapon\Type\WeaponTypeInterface;
use Exception;

class Offense implements OffenseInterface
{
    private int $damageType;
    protected WeaponTypeInterface $weaponType;
    private int $physicalDamage;
    private int $fireDamage;
    private int $waterDamage;
    private int $airDamage;
    private int $earthDamage;
    private int $lifeDamage;
    private int $deathDamage;
    private float $attackSpeed;
    private float $castSpeed;
    private int $accuracy;
    private int $magicAccuracy;
    private int $blockIgnoring;
    private int $criticalChance;
    private int $criticalMultiplier;
    private int $damageMultiplier;
    private int $vampirism;
    private int $magicVampirism;

    // Этот массив используется для механики конвертации урона
    private static array $convertMap = [
        MultipleOffenseInterface::CONVERT_PHYSICAL => 'physicalDamage',
        MultipleOffenseInterface::CONVERT_FIRE     => 'fireDamage',
        MultipleOffenseInterface::CONVERT_WATER    => 'waterDamage',
        MultipleOffenseInterface::CONVERT_AIR      => 'airDamage',
        MultipleOffenseInterface::CONVERT_EARTH    => 'earthDamage',
        MultipleOffenseInterface::CONVERT_LIFE     => 'lifeDamage',
        MultipleOffenseInterface::CONVERT_DEATH    => 'deathDamage',
    ];

    /**
     * @param ContainerInterface $container
     * @param int $damageType
     * @param int $weaponTypeId
     * @param int $physicalDamage
     * @param int $fireDamage
     * @param int $waterDamage
     * @param int $airDamage
     * @param int $earthDamage
     * @param int $lifeDamage
     * @param int $deathDamage
     * @param float $attackSpeed
     * @param float $castSpeed
     * @param int $accuracy
     * @param int $magicAccuracy
     * @param int $blockIgnoring
     * @param int $criticalChance
     * @param int $criticalMultiplier
     * @param int $damageMultiplier
     * @param int $vampirism
     * @param int $magicVampirism
     * @throws Exception
     */
    public function __construct(
        ContainerInterface $container,
        int $damageType,
        int $weaponTypeId,
        int $physicalDamage,
        int $fireDamage,
        int $waterDamage,
        int $airDamage,
        int $earthDamage,
        int $lifeDamage,
        int $deathDamage,
        float $attackSpeed,
        float $castSpeed,
        int $accuracy,
        int $magicAccuracy,
        int $blockIgnoring,
        int $criticalChance,
        int $criticalMultiplier,
        int $damageMultiplier,
        int $vampirism,
        int $magicVampirism
    )
    {
        $this->setDamageType($damageType);
        $this->weaponType = new WeaponType($weaponTypeId, $container);
        $this->setPhysicalDamage($physicalDamage);
        $this->setFireDamage($fireDamage);
        $this->setWaterDamage($waterDamage);
        $this->setAirDamage($airDamage);
        $this->setEarthDamage($earthDamage);
        $this->setLifeDamage($lifeDamage);
        $this->setDeathDamage($deathDamage);
        $this->setAttackSpeed($attackSpeed);
        $this->setCastSpeed($castSpeed);
        $this->setAccuracy($accuracy);
        $this->setMagicAccuracy($magicAccuracy);
        $this->setBlockIgnoring($blockIgnoring);
        $this->setCriticalChance($criticalChance);
        $this->setCriticalMultiplier($criticalMultiplier);
        $this->setDamageMultiplier($damageMultiplier);
        $this->setVampirism($vampirism);
        $this->setMagicVampirism($magicVampirism);
    }

    /**
     * @return int
     */
    public function getDamageType(): int
    {
        return $this->damageType;
    }

    /**
     * @return WeaponTypeInterface
     */
    public function getWeaponType(): WeaponTypeInterface
    {
        return $this->weaponType;
    }

    /**
     * @param DefenseInterface $defense
     * @return int
     */
    public function getDamage(DefenseInterface $defense): int
    {
        return (int)(
            (
                $this->physicalDamage * ((100 - $defense->getPhysicalResist()) / 100)
                +
                $this->fireDamage * ((100 - $defense->getFireResist()) / 100)
                +
                $this->waterDamage * ((100 - $defense->getWaterResist()) / 100)
                +
                $this->airDamage * ((100 - $defense->getAirResist()) / 100)
                +
                $this->earthDamage * ((100 - $defense->getEarthResist()) / 100)
                +
                $this->lifeDamage * ((100 - $defense->getLifeResist()) / 100)
                +
                $this->deathDamage * ((100 - $defense->getDeathResist()) / 100)
            ) * ((100 - $defense->getGlobalResist()) / 100) * $this->damageMultiplier / 100
        );
    }

    /**
     * @return int
     */
    public function getPhysicalDamage(): int
    {
        return $this->physicalDamage;
    }

    /**
     * @param int $physicalDamage
     * @throws OffenseException
     */
    public function setPhysicalDamage(int $physicalDamage): void
    {
        if ($physicalDamage < self::MIN_DAMAGE || $physicalDamage > self::MAX_DAMAGE) {
            throw new OffenseException(
                OffenseException::INCORRECT_PHYSICAL_DAMAGE_VALUE . OffenseInterface::MIN_DAMAGE . '-' . OffenseInterface::MAX_DAMAGE
            );
        }

        $this->physicalDamage = $physicalDamage;
    }

    /**
     * @return int
     */
    public function getFireDamage(): int
    {
        return $this->fireDamage;
    }

    /**
     * @param int $fireDamage
     * @throws OffenseException
     */
    public function setFireDamage(int $fireDamage): void
    {
        if ($fireDamage < self::MIN_DAMAGE || $fireDamage > self::MAX_DAMAGE) {
            throw new OffenseException(
                OffenseException::INCORRECT_FIRE_DAMAGE_VALUE . OffenseInterface::MIN_DAMAGE . '-' . OffenseInterface::MAX_DAMAGE
            );
        }

        $this->fireDamage = $fireDamage;
    }

    /**
     * @return int
     */
    public function getWaterDamage(): int
    {
        return $this->waterDamage;
    }

    /**
     * @param int $waterDamage
     * @throws OffenseException
     */
    public function setWaterDamage(int $waterDamage): void
    {
        if ($waterDamage < self::MIN_DAMAGE || $waterDamage > self::MAX_DAMAGE) {
            throw new OffenseException(
                OffenseException::INCORRECT_WATER_DAMAGE_VALUE . OffenseInterface::MIN_DAMAGE . '-' . OffenseInterface::MAX_DAMAGE
            );
        }

        $this->waterDamage = $waterDamage;
    }

    /**
     * @return int
     */
    public function getAirDamage(): int
    {
        return $this->airDamage;
    }

    /**
     * @param int $airDamage
     * @throws OffenseException
     */
    public function setAirDamage(int $airDamage): void
    {
        if ($airDamage < self::MIN_DAMAGE || $airDamage > self::MAX_DAMAGE) {
            throw new OffenseException(
                OffenseException::INCORRECT_AIR_DAMAGE_VALUE . OffenseInterface::MIN_DAMAGE . '-' . OffenseInterface::MAX_DAMAGE
            );
        }

        $this->airDamage = $airDamage;
    }

    /**
     * @return int
     */
    public function getEarthDamage(): int
    {
        return $this->earthDamage;
    }

    /**
     * @param int $earthDamage
     * @throws OffenseException
     */
    public function setEarthDamage(int $earthDamage): void
    {
        if ($earthDamage < self::MIN_DAMAGE || $earthDamage > self::MAX_DAMAGE) {
            throw new OffenseException(
                OffenseException::INCORRECT_EARTH_DAMAGE_VALUE . OffenseInterface::MIN_DAMAGE . '-' . OffenseInterface::MAX_DAMAGE
            );
        }

        $this->earthDamage = $earthDamage;
    }

    /**
     * @return int
     */
    public function getLifeDamage(): int
    {
        return $this->lifeDamage;
    }

    /**
     * @param int $lifeDamage
     * @throws OffenseException
     */
    public function setLifeDamage(int $lifeDamage): void
    {
        if ($lifeDamage < self::MIN_DAMAGE || $lifeDamage > self::MAX_DAMAGE) {
            throw new OffenseException(
                OffenseException::INCORRECT_LIFE_DAMAGE_VALUE . OffenseInterface::MIN_DAMAGE . '-' . OffenseInterface::MAX_DAMAGE
            );
        }

        $this->lifeDamage = $lifeDamage;
    }

    /**
     * @return int
     */
    public function getDeathDamage(): int
    {
        return $this->deathDamage;
    }

    /**
     * @param int $deathDamage
     * @throws OffenseException
     */
    public function setDeathDamage(int $deathDamage): void
    {
        if ($deathDamage < self::MIN_DAMAGE || $deathDamage > self::MAX_DAMAGE) {
            throw new OffenseException(
                OffenseException::INCORRECT_DEATH_DAMAGE_VALUE . OffenseInterface::MIN_DAMAGE . '-' . OffenseInterface::MAX_DAMAGE
            );
        }

        $this->deathDamage = $deathDamage;
    }

    /**
     * @return int
     */
    public function getDamageSum(): int
    {
        return $this->physicalDamage +
            $this->fireDamage +
            $this->waterDamage +
            $this->airDamage +
            $this->earthDamage +
            $this->lifeDamage +
            $this->deathDamage;
    }

    /**
     * @param string $damageType
     * @throws OffenseException
     */
    public function convertDamageTo(string $damageType): void
    {
        if (!array_key_exists($damageType, self::$convertMap)) {
            throw new OffenseException(OffenseException::INCORRECT_CONVERT_DAMAGE . ': ' . $damageType);
        }

        $damageProperty = self::$convertMap[$damageType];
        $damageSum = $this->getDamageSum();

        $this->physicalDamage = 0;
        $this->fireDamage = 0;
        $this->waterDamage = 0;
        $this->airDamage = 0;
        $this->earthDamage = 0;
        $this->lifeDamage = 0;
        $this->deathDamage = 0;

        $this->$damageProperty = $damageSum;
    }

    /**
     * @return float
     */
    public function getAttackSpeed(): float
    {
        return $this->attackSpeed;
    }

    /**
     * @param float $attackSpeed
     * @throws OffenseException
     */
    public function setAttackSpeed(float $attackSpeed): void
    {
        if ($attackSpeed < self::MIN_ATTACK_SPEED || $attackSpeed > self::MAX_ATTACK_SPEED) {
            throw new OffenseException(
                OffenseException::INCORRECT_ATTACK_SPEED_VALUE . OffenseInterface::MIN_ATTACK_SPEED . '-' . OffenseInterface::MAX_ATTACK_SPEED
            );
        }

        $this->attackSpeed = $attackSpeed;
    }

    /**
     * @return float
     */
    public function getCastSpeed(): float
    {
        return $this->castSpeed;
    }

    /**
     * @param float $castSpeed
     * @throws OffenseException
     */
    public function setCastSpeed(float $castSpeed): void
    {
        if ($castSpeed < self::MIN_CAST_SPEED || $castSpeed > self::MAX_CAST_SPEED) {
            throw new OffenseException(
                OffenseException::INCORRECT_CAST_SPEED_VALUE . OffenseInterface::MIN_CAST_SPEED . '-' . OffenseInterface::MAX_CAST_SPEED
            );
        }

        $this->castSpeed = $castSpeed;
    }

    /**
     * @return int
     */
    public function getAccuracy(): int
    {
        return $this->accuracy;
    }

    /**
     * @param int $accuracy
     * @throws OffenseException
     */
    public function setAccuracy(int $accuracy): void
    {
        if ($accuracy < self::MIN_ACCURACY || $accuracy > self::MAX_ACCURACY) {
            throw new OffenseException(
                OffenseException::INCORRECT_ACCURACY_VALUE . OffenseInterface::MIN_ACCURACY . '-' . OffenseInterface::MAX_ACCURACY
            );
        }

        $this->accuracy = $accuracy;
    }

    /**
     * @return int
     */
    public function getMagicAccuracy(): int
    {
        return $this->magicAccuracy;
    }

    /**
     * @param int $magicAccuracy
     * @throws OffenseException
     */
    public function setMagicAccuracy(int $magicAccuracy): void
    {
        if ($magicAccuracy < self::MIN_MAGIC_ACCURACY || $magicAccuracy > self::MAX_MAGIC_ACCURACY) {
            throw new OffenseException(
                OffenseException::INCORRECT_MAGIC_ACCURACY_VALUE . OffenseInterface::MIN_MAGIC_ACCURACY . '-' . OffenseInterface::MAX_MAGIC_ACCURACY
            );
        }

        $this->magicAccuracy = $magicAccuracy;
    }

    /**
     * @return int
     */
    public function getBlockIgnoring(): int
    {
        return $this->blockIgnoring;
    }

    /**
     * @param int $blockIgnoring
     * @throws OffenseException
     */
    public function setBlockIgnoring(int $blockIgnoring): void
    {
        if ($blockIgnoring < self::MIN_BLOCK_IGNORING || $blockIgnoring > self::MAX_BLOCK_IGNORING) {
            throw new OffenseException(
                OffenseException::INCORRECT_BLOCK_IGNORING_VALUE . OffenseInterface::MIN_BLOCK_IGNORING . '-' . OffenseInterface::MAX_BLOCK_IGNORING
            );
        }

        $this->blockIgnoring = $blockIgnoring;
    }

    /**
     * @return int
     */
    public function getVampirism(): int
    {
        return $this->vampirism;
    }

    /**
     * @param int $vampirism
     * @throws OffenseException
     */
    public function setVampirism(int $vampirism): void
    {
        if ($vampirism < self::MIN_VAMPIRE || $vampirism > self::MAX_VAMPIRE) {
            throw new OffenseException(
                OffenseException::INCORRECT_VAMPIRISM_VALUE . OffenseInterface::MIN_VAMPIRE . '-' . OffenseInterface::MAX_VAMPIRE
            );
        }

        $this->vampirism = $vampirism;
    }

    /**
     * @return int
     */
    public function getMagicVampirism(): int
    {
        return $this->magicVampirism;
    }

    /**
     * @param int $magicVampirism
     * @throws OffenseException
     */
    public function setMagicVampirism(int $magicVampirism): void
    {
        if ($magicVampirism < self::MIN_VAMPIRE || $magicVampirism > self::MAX_VAMPIRE) {
            throw new OffenseException(
                OffenseException::INCORRECT_MAGIC_VAMPIRISM_VALUE . OffenseInterface::MIN_VAMPIRE . '-' . OffenseInterface::MAX_VAMPIRE
            );
        }

        $this->magicVampirism = $magicVampirism;
    }

    /**
     * @return float
     */
    public function getDPS(): float
    {
        $speed = $this->damageType === self::TYPE_ATTACK ? $this->attackSpeed : $this->castSpeed;

        return round(
            // Общий урон по всем стихиям
            (
                $this->physicalDamage +
                $this->fireDamage +
                $this->waterDamage +
                $this->airDamage +
                $this->earthDamage +
                $this->lifeDamage +
                $this->deathDamage
            )
            // Множитель от скорости атаки
            * $speed
            // Множитель общего наносимого урона
            * ($this->damageMultiplier / 100)
            // Множитель от шанса и силы крита
            * (1 + (($this->criticalChance / 100) * ($this->criticalMultiplier / 100 - 1))),
            1
        );
    }

    /**
     * @return int
     */
    public function getCriticalChance(): int
    {
        return $this->criticalChance;
    }

    /**
     * @param int $criticalChance
     * @throws OffenseException
     */
    public function setCriticalChance(int $criticalChance): void
    {
        if ($criticalChance < self::MIN_CRITICAL_CHANCE || $criticalChance > self::MAX_CRITICAL_CHANCE) {
            throw new OffenseException(
                OffenseException::INCORRECT_CRITICAL_CHANCE_VALUE . OffenseInterface::MIN_CRITICAL_CHANCE . '-' . OffenseInterface::MAX_CRITICAL_CHANCE
            );
        }

        $this->criticalChance = $criticalChance;
    }

    /**
     * @return int
     */
    public function getCriticalMultiplier(): int
    {
        return $this->criticalMultiplier;
    }

    /**
     * @param int $criticalMultiplier
     * @throws OffenseException
     */
    public function setCriticalMultiplier(int $criticalMultiplier): void
    {
        if ($criticalMultiplier < self::MIN_CRITICAL_MULTIPLIER || $criticalMultiplier > self::MAX_CRITICAL_MULTIPLIER) {
            throw new OffenseException(
                OffenseException::INCORRECT_CRITICAL_MULTIPLIER_VALUE . OffenseInterface::MIN_CRITICAL_MULTIPLIER . '-' . OffenseInterface::MAX_CRITICAL_MULTIPLIER
            );
        }

        $this->criticalMultiplier = $criticalMultiplier;
    }

    /**
     * @return int
     */
    public function getDamageMultiplier(): int
    {
        return $this->damageMultiplier;
    }

    /**
     * @param int $damageMultiplier
     * @throws OffenseException
     */
    public function setDamageMultiplier(int $damageMultiplier): void
    {
        if ($damageMultiplier < self::MIN_DAMAGE_MULTIPLIER || $damageMultiplier > self::MAX_DAMAGE_MULTIPLIER) {
            throw new OffenseException(
                OffenseException::INCORRECT_DAMAGE_MULTIPLIER_VALUE . OffenseInterface::MIN_DAMAGE_MULTIPLIER . '-' . OffenseInterface::MAX_DAMAGE_MULTIPLIER
            );
        }

        $this->damageMultiplier = $damageMultiplier;
    }

    /**
     * Задает тип урона. В отличие от других параметров тип урона не может меняться, по этому метод приватный
     *
     * @param int $typeDamage
     * @throws OffenseException
     */
    private function setDamageType(int $typeDamage): void
    {
        if ($typeDamage !== self::TYPE_ATTACK && $typeDamage !== self::TYPE_SPELL) {
            throw new OffenseException(
                OffenseException::INCORRECT_DAMAGE_TYPE_VALUE
            );
        }

        $this->damageType = $typeDamage;
    }
}
