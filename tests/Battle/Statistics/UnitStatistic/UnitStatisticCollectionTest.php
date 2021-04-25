<?php

declare(strict_types=1);

namespace Tests\Battle\Statistics\UnitStatistic;

use Battle\Classes\ClassFactoryException;
use Battle\Statistic\StatisticException;
use Battle\Statistic\UnitStatistic\UnitStatistic;
use Battle\Statistic\UnitStatistic\UnitStatisticCollection;
use PHPUnit\Framework\TestCase;
use Tests\Battle\Factory\UnitFactory;
use Tests\Battle\Factory\UnitFactoryException;

class UnitStatisticCollectionTest extends TestCase
{
    /**
     * @throws StatisticException
     * @throws ClassFactoryException
     * @throws UnitFactoryException
     */
    public function testStatisticsExistUnit(): void
    {
        $unit = UnitFactory::createByTemplate(1);

        $collection = new UnitStatisticCollection();

        $collection->add(new UnitStatistic($unit));

        self::assertTrue($collection->exist('f7e84eab-e4f6-469f-b0e3-f5f965f9fbce'));
        self::assertFalse($collection->exist('id_3'));
    }

    /**
     * @throws ClassFactoryException
     * @throws StatisticException
     * @throws UnitFactoryException
     */
    public function testStatisticsDoubleId(): void
    {
        $unit = UnitFactory::createByTemplate(1);

        $collection = new UnitStatisticCollection();

        $collection->add(new UnitStatistic($unit));

        $this->expectException(StatisticException::class);
        $this->expectExceptionMessage(StatisticException::DOUBLE_ID);
        $collection->add(new UnitStatistic($unit));
    }
}
