<?php

declare(strict_types=1);

namespace Battle\Unit\Offense;

use Battle\Unit\Defense\DefenseInterface;
use Battle\Weapon\Type\WeaponType;
use Battle\Weapon\Type\WeaponTypeInterface;
use Exception;

class Offense implements OffenseInterface
{
    private int $typeDamage;
    protected WeaponTypeInterface $weaponType;
    private int $physicalDamage;
    private float $attackSpeed;
    private int $accuracy;
    private int $magicAccuracy;
    private int $blockIgnore;
    private int $criticalChance;
    private int $criticalMultiplier;
    private int $vampire;

    /**
     * @param int $typeDamage
     * @param int $weaponTypeId
     * @param int $physicalDamage
     * @param float $attackSpeed
     * @param int $accuracy
     * @param int $magicAccuracy
     * @param int $blockIgnore
     * @param int $criticalChance
     * @param int $criticalMultiplier
     * @param int $vampire
     * @throws Exception
     */
    public function __construct(
        int $typeDamage,
        int $weaponTypeId,
        int $physicalDamage,
        float $attackSpeed,
        int $accuracy,
        int $magicAccuracy,
        int $blockIgnore,
        int $criticalChance,
        int $criticalMultiplier,
        int $vampire
    )
    {
        $this->setTypeDamage($typeDamage);
        $this->weaponType = new WeaponType($weaponTypeId);
        $this->setPhysicalDamage($physicalDamage);
        $this->setAttackSpeed($attackSpeed);
        $this->setAccuracy($accuracy);
        $this->setMagicAccuracy($magicAccuracy);
        $this->setBlockIgnore($blockIgnore);
        $this->setCriticalChance($criticalChance);
        $this->setCriticalMultiplier($criticalMultiplier);
        $this->setVampire($vampire);
    }

    /**
     * @return int
     */
    public function getTypeDamage(): int
    {
        return $this->typeDamage;
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
        return (int)($this->physicalDamage * ((100 - $defense->getPhysicalResist()) / 100));
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
    public function getBlockIgnore(): int
    {
        return $this->blockIgnore;
    }

    /**
     * @param int $blockIgnore
     * @throws OffenseException
     */
    public function setBlockIgnore(int $blockIgnore): void
    {
        if ($blockIgnore < self::MIN_BLOCK_IGNORE || $blockIgnore > self::MAX_BLOCK_IGNORE) {
            throw new OffenseException(
                OffenseException::INCORRECT_BLOCK_IGNORE_VALUE . OffenseInterface::MIN_BLOCK_IGNORE . '-' . OffenseInterface::MAX_BLOCK_IGNORE
            );
        }

        $this->blockIgnore = $blockIgnore;
    }

    /**
     * @return int
     */
    public function getVampire(): int
    {
        return $this->vampire;
    }

    /**
     * @param int $vampire
     * @throws OffenseException
     */
    public function setVampire(int $vampire): void
    {
        if ($vampire < self::MIN_VAMPIRE || $vampire > self::MAX_VAMPIRE) {
            throw new OffenseException(
                OffenseException::INCORRECT_VAMPIRE_VALUE . OffenseInterface::MIN_VAMPIRE . '-' . OffenseInterface::MAX_VAMPIRE
            );
        }

        $this->vampire = $vampire;
    }

    /**
     * @return float
     */
    public function getDPS(): float
    {
        return round($this->physicalDamage * $this->attackSpeed, 1);
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
     * Задает тип урона. В отличие от других параметров тип урона не может меняться, по этому метод приватный
     *
     * @param int $typeDamage
     * @throws OffenseException
     */
    private function setTypeDamage(int $typeDamage): void
    {
        if ($typeDamage !== self::TYPE_ATTACK && $typeDamage !== self::TYPE_SPELL) {
            throw new OffenseException(
                OffenseException::INCORRECT_TYPE_DAMAGE_VALUE
            );
        }

        $this->typeDamage = $typeDamage;
    }
}
