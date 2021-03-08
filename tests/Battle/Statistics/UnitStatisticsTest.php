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

        $this->assertEquals(45, $unitStatistics->getCausedDamage());
        $this->assertEquals(60, $unitStatistics->getTakenDamage());
        $this->assertEquals(1, $unitStatistics->getKilling());
        $this->assertEquals($name, $unitStatistics->getName());
    }
}
