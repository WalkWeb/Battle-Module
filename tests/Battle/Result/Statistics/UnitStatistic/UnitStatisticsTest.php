<?php

declare(strict_types=1);

namespace Tests\Battle\Result\Statistics\UnitStatistic;

use Exception;
use Tests\AbstractUnitTest;
use Tests\Factory\UnitFactory;
use Battle\Result\Statistic\UnitStatistic\UnitStatistic;

class UnitStatisticsTest extends AbstractUnitTest
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

        self::assertEquals(0, $unitStatistics->getHits());
        self::assertEquals(0, $unitStatistics->getCriticalHits());
        self::assertEquals(0, $unitStatistics->getCausedDamage());
        self::assertEquals(0, $unitStatistics->getTakenDamage());
        self::assertEquals(0, $unitStatistics->getBlockedHits());
        self::assertEquals(0, $unitStatistics->getDodgedHits());
        self::assertEquals(0, $unitStatistics->getHeal());
        self::assertEquals(0, $unitStatistics->getKilling());
        self::assertEquals(0, $unitStatistics->getSummons());
        self::assertEquals(0, $unitStatistics->getResurrections());

        $unitStatistics->addHit();
        $unitStatistics->addHit();
        $unitStatistics->addHit();

        $unitStatistics->addCriticalHit();
        $unitStatistics->addCriticalHit();

        $unitStatistics->addCausedDamage(15);
        $unitStatistics->addCausedDamage(15);
        $unitStatistics->addCausedDamage(15);

        $unitStatistics->addTakenDamage(20);
        $unitStatistics->addTakenDamage(20);
        $unitStatistics->addTakenDamage(20);

        $unitStatistics->addBlockedHit();
        $unitStatistics->addBlockedHit();
        $unitStatistics->addBlockedHit();
        $unitStatistics->addBlockedHit();

        $unitStatistics->addDodgedHit();
        $unitStatistics->addDodgedHit();

        $unitStatistics->addHeal(10);
        $unitStatistics->addHeal(10);
        $unitStatistics->addHeal(10);

        $unitStatistics->addKillingUnit();

        $unitStatistics->addSummon();
        $unitStatistics->addSummon();

        $unitStatistics->addResurrection();

        self::assertEquals(3, $unitStatistics->getHits());
        self::assertEquals(2, $unitStatistics->getCriticalHits());
        self::assertEquals(45, $unitStatistics->getCausedDamage());
        self::assertEquals(60, $unitStatistics->getTakenDamage());
        self::assertEquals(4, $unitStatistics->getBlockedHits());
        self::assertEquals(2, $unitStatistics->getDodgedHits());
        self::assertEquals(30, $unitStatistics->getHeal());
        self::assertEquals(1, $unitStatistics->getKilling());
        self::assertEquals(2, $unitStatistics->getSummons());
        self::assertEquals(1, $unitStatistics->getResurrections());
        self::assertEquals($id, $unitStatistics->getUnit()->getId());
        self::assertEquals($name, $unitStatistics->getUnit()->getName());
    }
}
