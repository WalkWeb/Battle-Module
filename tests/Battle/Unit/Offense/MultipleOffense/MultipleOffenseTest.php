<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Offense\MultipleOffense;

use Battle\Unit\Offense\MultipleOffense\MultipleOffense;
use Tests\AbstractUnitTest;

class MultipleOffenseTest extends AbstractUnitTest
{
    /**
     * Тест на создание MultipleOffense
     */
    public function testMultipleOffenseCreate(): void
    {
        $physicalDamageMultiplier = 1.1;
        $fireDamageMultiplier = 1.2;
        $waterDamageMultiplier = 1.3;
        $airDamageMultiplier = 1.4;
        $earthDamageMultiplier = 1.5;
        $lifeDamageMultiplier = 1.6;
        $deathDamageMultiplier = 1.7;
        $attackSpeedMultiplier = 1.8;
        $castSpeedMultiplier = 1.9;
        $accuracyMultiplier = 2.0;
        $magicAccuracyMultiplier = 2.1;
        $criticalChanceMultiplier = 2.2;
        $criticalMultiplierMultiplier = 2.2;

        $multipleOffense = new MultipleOffense(
            $physicalDamageMultiplier,
            $fireDamageMultiplier,
            $waterDamageMultiplier,
            $airDamageMultiplier,
            $earthDamageMultiplier,
            $lifeDamageMultiplier,
            $deathDamageMultiplier,
            $attackSpeedMultiplier,
            $castSpeedMultiplier,
            $accuracyMultiplier,
            $magicAccuracyMultiplier,
            $criticalChanceMultiplier,
            $criticalMultiplierMultiplier,
        );

        self::assertEquals($physicalDamageMultiplier, $multipleOffense->getPhysicalDamageMultiplier());
        self::assertEquals($fireDamageMultiplier, $multipleOffense->getFireDamageMultiplier());
        self::assertEquals($waterDamageMultiplier, $multipleOffense->getWaterDamageMultiplier());
        self::assertEquals($airDamageMultiplier, $multipleOffense->getAirDamageMultiplier());
        self::assertEquals($earthDamageMultiplier, $multipleOffense->getEarthDamageMultiplier());
        self::assertEquals($lifeDamageMultiplier, $multipleOffense->getLifeDamageMultiplier());
        self::assertEquals($deathDamageMultiplier, $multipleOffense->getDeathDamageMultiplier());
        self::assertEquals($attackSpeedMultiplier, $multipleOffense->getAttackSpeedMultiplier());
        self::assertEquals($castSpeedMultiplier, $multipleOffense->getCastSpeedMultiplier());
        self::assertEquals($accuracyMultiplier, $multipleOffense->getAccuracyMultiplier());
        self::assertEquals($magicAccuracyMultiplier, $multipleOffense->getMagicAccuracyMultiplier());
        self::assertEquals($criticalChanceMultiplier, $multipleOffense->getCriticalChanceMultiplier());
        self::assertEquals($criticalMultiplierMultiplier, $multipleOffense->getCriticalMultiplierMultiplier());
    }
}
