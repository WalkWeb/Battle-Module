<?php

declare(strict_types=1);

namespace Tests\Battle\Statistics\UnitStatistic;

use Battle\Statistic\UnitStatistic\UnitStatistic;
use PHPUnit\Framework\TestCase;

class UnitStatisticsTest extends TestCase
{
    public function testUnitStatistics(): void
    {
        $id = 'b2d2e0ba-f85c-4c16-91ba-6a003e153e09';
        $name = 'Unit_stats';

        $unitStatistics = new UnitStatistic($id, $name);

        self::assertEquals($id, $unitStatistics->getId());
        self::assertEquals($name, $unitStatistics->getName());

        $unitStatistics->addCausedDamage(15);
        $unitStatistics->addCausedDamage(15);
        $unitStatistics->addCausedDamage(15);

        $unitStatistics->addTakenDamage(20);
        $unitStatistics->addTakenDamage(20);
        $unitStatistics->addTakenDamage(20);

        $unitStatistics->addHeal(10);
        $unitStatistics->addHeal(10);
        $unitStatistics->addHeal(10);

        $unitStatistics->addKillingUnit();

        self::assertEquals(45, $unitStatistics->getCausedDamage());
        self::assertEquals(60, $unitStatistics->getTakenDamage());
        self::assertEquals(30, $unitStatistics->getHeal());
        self::assertEquals(1, $unitStatistics->getKilling());
        self::assertEquals($name, $unitStatistics->getName());
    }
}
