<?php

declare(strict_types=1);

namespace Tests\Battle\Statistics;

use Battle\Statistic\UnitStatistic;
use PHPUnit\Framework\TestCase;

class UnitStatisticsTest extends TestCase
{
    public function testBase(): void
    {
        $name = 'Unit_stats';
        $unitStatistics = new UnitStatistic($name);

        $unitStatistics->addCausedDamage(15);
        $unitStatistics->addCausedDamage(15);
        $unitStatistics->addCausedDamage(15);

        $unitStatistics->addTakenDamage(20);
        $unitStatistics->addTakenDamage(20);
        $unitStatistics->addTakenDamage(20);

        $unitStatistics->addKillingUnit();

        self::assertEquals(45, $unitStatistics->getCausedDamage());
        self::assertEquals(60, $unitStatistics->getTakenDamage());
        self::assertEquals(1, $unitStatistics->getKilling());
        self::assertEquals($name, $unitStatistics->getName());
    }
}
