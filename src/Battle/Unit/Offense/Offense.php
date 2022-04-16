<?php

declare(strict_types=1);

namespace Battle\Unit\Offense;

// TODO В сеттерах добавить проверки на допустимое значение

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
     */
    public function setDamage(int $damage): void
    {
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
     */
    public function setAttackSpeed(float $attackSpeed): void
    {
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
     */
    public function setAccuracy(int $accuracy): void
    {
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
     */
    public function setBlockIgnore(int $blockIgnore): void
    {
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
