<?php

declare(strict_types=1);

namespace Battle\Unit\Offense;

class Offense implements OffenseInterface
{
    /**
     * @var int
     */
    private $damage;

    /**
     * @var float
     */
    private $attackSpeed;

    /**
     * @var int
     */
    private $accuracy;

    /**
     * @var int
     */
    private $blockIgnore;

    public function __construct(int $damage, float $attackSpeed, int $accuracy, int $blockIgnore)
    {
        $this->damage = $damage;
        $this->attackSpeed = $attackSpeed;
        $this->accuracy = $accuracy;
        $this->blockIgnore = $blockIgnore;
    }

    /**
     * @return int
     */
    public function getDamage(): int
    {
        return $this->damage;
    }

    /**
     * @param int $damage
     * @throws OffenseException
     */
    public function setDamage(int $damage): void
    {
        if ($damage < self::MIN_DAMAGE || $damage > self::MAX_DAMAGE) {
            throw new OffenseException(
                OffenseException::INCORRECT_DAMAGE_VALUE . OffenseInterface::MIN_DAMAGE . '-' . OffenseInterface::MAX_DAMAGE
            );
        }

        $this->damage = $damage;
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
     * @return float
     */
    public function getDPS(): float
    {
        return round($this->damage * $this->attackSpeed, 1);
    }
}
