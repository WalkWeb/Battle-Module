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
        $damageMultiplier = 1.1;
        $attackSpeedMultiplier = 1.8;
        $castSpeedMultiplier = 1.9;
        $accuracyMultiplier = 2.0;
        $magicAccuracyMultiplier = 2.1;
        $criticalChanceMultiplier = 2.2;
        $criticalMultiplierMultiplier = 2.2;

        $multipleOffense = new MultipleOffense(
            $damageMultiplier,
            $attackSpeedMultiplier,
            $castSpeedMultiplier,
            $accuracyMultiplier,
            $magicAccuracyMultiplier,
            $criticalChanceMultiplier,
            $criticalMultiplierMultiplier,
        );

        self::assertEquals($damageMultiplier, $multipleOffense->getDamageMultiplier());
        self::assertEquals($attackSpeedMultiplier, $multipleOffense->getAttackSpeedMultiplier());
        self::assertEquals($castSpeedMultiplier, $multipleOffense->getCastSpeedMultiplier());
        self::assertEquals($accuracyMultiplier, $multipleOffense->getAccuracyMultiplier());
        self::assertEquals($magicAccuracyMultiplier, $multipleOffense->getMagicAccuracyMultiplier());
        self::assertEquals($criticalChanceMultiplier, $multipleOffense->getCriticalChanceMultiplier());
        self::assertEquals($criticalMultiplierMultiplier, $multipleOffense->getCriticalMultiplierMultiplier());
    }
}
