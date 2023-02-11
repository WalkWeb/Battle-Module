<?php

declare(strict_types=1);

namespace Battle\Unit\Offense\MultipleOffense;

class MultipleOffense implements MultipleOffenseInterface
{
    private float $physicalDamageMultiplier;
    private float $fireDamageMultiplier;
    private float $waterDamageMultiplier;
    private float $airDamageMultiplier;
    private float $earthDamageMultiplier;
    private float $lifeDamageMultiplier;
    private float $deathDamageMultiplier;
    private float $attackSpeedMultiplier;
    private float $castSpeedMultiplier;
    private float $accuracyMultiplier;
    private float $magicAccuracyMultiplier;
    private float $criticalChanceMultiplier;
    private float $criticalMultiplierMultiplier;

    public function __construct(
        float $physicalDamageMultiplier,
        float $fireDamageMultiplier,
        float $waterDamageMultiplier,
        float $airDamageMultiplier,
        float $earthDamageMultiplier,
        float $lifeDamageMultiplier,
        float $deathDamageMultiplier,
        float $attackSpeedMultiplier,
        float $castSpeedMultiplier,
        float $accuracyMultiplier,
        float $magicAccuracyMultiplier,
        float $criticalChanceMultiplier,
        float $criticalMultiplierMultiplier
    )
    {
        $this->physicalDamageMultiplier = $physicalDamageMultiplier;
        $this->fireDamageMultiplier = $fireDamageMultiplier;
        $this->waterDamageMultiplier = $waterDamageMultiplier;
        $this->airDamageMultiplier = $airDamageMultiplier;
        $this->earthDamageMultiplier = $earthDamageMultiplier;
        $this->lifeDamageMultiplier = $lifeDamageMultiplier;
        $this->deathDamageMultiplier = $deathDamageMultiplier;
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
    public function getPhysicalDamageMultiplier(): float
    {
        return $this->physicalDamageMultiplier;
    }

    /**
     * @return float
     */
    public function getFireDamageMultiplier(): float
    {
        return $this->fireDamageMultiplier;
    }

    /**
     * @return float
     */
    public function getWaterDamageMultiplier(): float
    {
        return $this->waterDamageMultiplier;
    }

    /**
     * @return float
     */
    public function getAirDamageMultiplier(): float
    {
        return $this->airDamageMultiplier;
    }

    /**
     * @return float
     */
    public function getEarthDamageMultiplier(): float
    {
        return $this->earthDamageMultiplier;
    }

    /**
     * @return float
     */
    public function getLifeDamageMultiplier(): float
    {
        return $this->lifeDamageMultiplier;
    }

    /**
     * @return float
     */
    public function getDeathDamageMultiplier(): float
    {
        return $this->deathDamageMultiplier;
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
