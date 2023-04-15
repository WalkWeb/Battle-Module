<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Offense\MultipleOffense;

use Battle\Unit\Offense\MultipleOffense\MultipleOffense;
use Battle\Unit\Offense\MultipleOffense\MultipleOffenseException;
use Tests\AbstractUnitTest;

class MultipleOffenseTest extends AbstractUnitTest
{
    /**
     * Тест на создание MultipleOffense
     *
     * @throws MultipleOffenseException
     */
    public function testMultipleOffenseCreate(): void
    {
        $damageMultiplier = 1.1;
        $speedMultiplier = 1.8;
        $accuracyMultiplier = 2.0;
        $criticalChanceMultiplier = 2.2;
        $criticalMultiplierMultiplier = 2.2;
        $damageConvertTo = '';

        $multipleOffense = new MultipleOffense(
            $damageMultiplier,
            $speedMultiplier,
            $accuracyMultiplier,
            $criticalChanceMultiplier,
            $criticalMultiplierMultiplier,
            $damageConvertTo
        );

        self::assertEquals($damageMultiplier, $multipleOffense->getDamageMultiplier());
        self::assertEquals($speedMultiplier, $multipleOffense->getSpeedMultiplier());
        self::assertEquals($accuracyMultiplier, $multipleOffense->getAccuracyMultiplier());
        self::assertEquals($criticalChanceMultiplier, $multipleOffense->getCriticalChanceMultiplier());
        self::assertEquals($criticalMultiplierMultiplier, $multipleOffense->getCriticalMultiplierMultiplier());
        self::assertEquals($damageConvertTo, $multipleOffense->getDamageConvertTo());
    }
}
