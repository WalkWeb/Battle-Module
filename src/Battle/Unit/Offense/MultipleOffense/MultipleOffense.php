<?php

declare(strict_types=1);

namespace Battle\Unit\Offense\MultipleOffense;

class MultipleOffense implements MultipleOffenseInterface
{
    private float $damageMultiplier;
    private float $speedMultiplier;
    private float $accuracyMultiplier;
    private float $criticalChanceMultiplier;
    private float $criticalMultiplierMultiplier;
    private string $damageConvertTo;

    // Этот массив используется для валидации параметра при создании способности
    private static array $convertMap = [
        self::CONVERT_NONE,
        self::CONVERT_PHYSICAL,
        self::CONVERT_FIRE,
        self::CONVERT_WATER,
        self::CONVERT_AIR,
        self::CONVERT_EARTH,
        self::CONVERT_LIFE,
        self::CONVERT_DEATH,
    ];

    /**
     * @param float $damageMultiplier
     * @param float $speedMultiplier
     * @param float $accuracyMultiplier
     * @param float $criticalChanceMultiplier
     * @param float $criticalMultiplierMultiplier
     * @param string $damageConvertTo
     * @throws MultipleOffenseException
     */
    public function __construct(
        float $damageMultiplier,
        float $speedMultiplier,
        float $accuracyMultiplier,
        float $criticalChanceMultiplier,
        float $criticalMultiplierMultiplier,
        string $damageConvertTo
    )
    {
        $this->damageMultiplier = $damageMultiplier;
        $this->speedMultiplier = $speedMultiplier;
        $this->accuracyMultiplier = $accuracyMultiplier;
        $this->criticalChanceMultiplier = $criticalChanceMultiplier;
        $this->criticalMultiplierMultiplier = $criticalMultiplierMultiplier;
        $this->setDamageConvertTo($damageConvertTo);
    }

    /**
     * @return float
     */
    public function getDamageMultiplier(): float
    {
        return $this->damageMultiplier;
    }

    /**
     * @return float
     */
    public function getSpeedMultiplier(): float
    {
        return $this->speedMultiplier;
    }

    /**
     * @return float
     */
    public function getAccuracyMultiplier(): float
    {
        return $this->accuracyMultiplier;
    }

    /**
     * @return float
     */
    public function getCriticalChanceMultiplier(): float
    {
        return $this->criticalChanceMultiplier;
    }

    /**
     * @return float
     */
    public function getCriticalMultiplierMultiplier(): float
    {
        return $this->criticalMultiplierMultiplier;
    }

    public function getDamageConvertTo(): string
    {
        return $this->damageConvertTo;
    }

    /**
     * @param string $damageConvertTo
     * @throws MultipleOffenseException
     */
    private function setDamageConvertTo(string $damageConvertTo): void
    {
        if (!in_array($damageConvertTo, self::$convertMap, true)) {
            throw new MultipleOffenseException(MultipleOffenseException::INVALID_CRITICAL_DAMAGE_CONVERT_VALUE . ': ' . $damageConvertTo);
        }

        $this->damageConvertTo = $damageConvertTo;
    }
}
