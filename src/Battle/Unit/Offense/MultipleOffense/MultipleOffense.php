<?php

declare(strict_types=1);

namespace Battle\Unit\Offense\MultipleOffense;

class MultipleOffense implements MultipleOffenseInterface
{
    private float $damageMultiplier;
    private float $attackSpeedMultiplier;
    private float $castSpeedMultiplier;
    private float $accuracyMultiplier;
    private float $magicAccuracyMultiplier;
    private float $criticalChanceMultiplier;
    private float $criticalMultiplierMultiplier;

    public function __construct(
        float $damageMultiplier,
        float $attackSpeedMultiplier,
        float $castSpeedMultiplier,
        float $accuracyMultiplier,
        float $magicAccuracyMultiplier,
        float $criticalChanceMultiplier,
        float $criticalMultiplierMultiplier
    )
    {
        $this->damageMultiplier = $damageMultiplier;
        $this->attackSpeedMultiplier = $attackSpeedMultiplier;
        $this->castSpeedMultiplier = $castSpeedMultiplier;
        $this->accuracyMultiplier = $accuracyMultiplier;
        $this->magicAccuracyMultiplier = $magicAccuracyMultiplier;
        $this->criticalChanceMultiplier = $criticalChanceMultiplier;
        $this->criticalMultiplierMultiplier = $criticalMultiplierMultiplier;
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
    public function getAttackSpeedMultiplier(): float
    {
        return $this->attackSpeedMultiplier;
    }

    /**
     * @return float
     */
    public function getCastSpeedMultiplier(): float
    {
        return $this->castSpeedMultiplier;
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
    public function getMagicAccuracyMultiplier(): float
    {
        return $this->magicAccuracyMultiplier;
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
}
