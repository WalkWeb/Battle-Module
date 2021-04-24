<?php

declare(strict_types=1);

namespace Tests\Battle\Statistics\UnitStatistic;

use Battle\Statistic\StatisticException;
use Battle\Statistic\UnitStatistic\UnitStatistic;
use Battle\Statistic\UnitStatistic\UnitStatisticCollection;
use PHPUnit\Framework\TestCase;

class UnitStatisticCollectionTest extends TestCase
{
    /**
     * @throws StatisticException
     */
    public function testStatisticsExistUnit(): void
    {
        $collection = new UnitStatisticCollection();

        $collection->add(new UnitStatistic('id_1', 'name_1'));
        $collection->add(new UnitStatistic('id_2', 'name_2'));

        self::assertTrue($collection->exist('id_1'));
        self::assertFalse($collection->exist('id_3'));
    }

    /**
     * @throws StatisticException
     */
    public function testStatisticsDoubleId(): void
    {
        $collection = new UnitStatisticCollection();

        $collection->add(new UnitStatistic('id_1', 'name_1'));

        $this->expectException(StatisticException::class);
        $this->expectExceptionMessage(StatisticException::DOUBLE_ID);
        $collection->add(new UnitStatistic('id_1', 'name_2'));
    }
}
