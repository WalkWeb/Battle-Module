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

    public function __construct(
        float $damageMultiplier,
        float $speedMultiplier,
        float $accuracyMultiplier,
        float $criticalChanceMultiplier,
        float $criticalMultiplierMultiplier
    )
    {
        $this->damageMultiplier = $damageMultiplier;
        $this->speedMultiplier = $speedMultiplier;
        $this->accuracyMultiplier = $accuracyMultiplier;
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
}
