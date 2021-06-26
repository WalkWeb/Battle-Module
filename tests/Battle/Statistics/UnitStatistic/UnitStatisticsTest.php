<?php

declare(strict_types=1);

namespace Tests\Battle\Statistics\UnitStatistic;

use Exception;
use PHPUnit\Framework\TestCase;
use Tests\Battle\Factory\UnitFactory;
use Battle\Statistic\UnitStatistic\UnitStatistic;

class UnitStatisticsTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testUnitStatistics(): void
    {
        $unit = UnitFactory::createByTemplate(1);

        $id = 'f7e84eab-e4f6-469f-b0e3-f5f965f9fbce';
        $name = 'unit_1';

        $unitStatistics = new UnitStatistic($unit);

        self::assertEquals($id, $unitStatistics->getUnit()->getId());
        self::assertEquals($name, $unitStatistics->getUnit()->getName());

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
        self::assertEquals($name, $unitStatistics->getUnit()->getName());
    }
}
