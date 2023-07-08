<?php

declare(strict_types=1);

namespace Tests\Battle\Response\Statistics\UnitStatistic;

use Battle\Response\Statistic\StatisticException;
use Battle\Response\Statistic\UnitStatistic\UnitStatistic;
use Battle\Response\Statistic\UnitStatistic\UnitStatisticCollection;
use Battle\Response\Statistic\UnitStatistic\UnitStatisticInterface;
use Exception;
use Tests\AbstractUnitTest;
use Tests\Factory\UnitFactory;

class UnitStatisticCollectionTest extends AbstractUnitTest
{
    /**
     * @throws Exception
     */
    public function testStatisticsExistUnit(): void
    {
        $unit = UnitFactory::createByTemplate(1);

        $collection = new UnitStatisticCollection();

        $collection->add(new UnitStatistic($unit));

        self::assertTrue($collection->exist('f7e84eab-e4f6-469f-b0e3-f5f965f9fbce'));
        self::assertFalse($collection->exist('id_3'));

        foreach ($collection as $unitStatistic) {
            self::assertInstanceOf(UnitStatisticInterface::class, $unitStatistic);
        }
    }

    /**
     * @throws Exception
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

    public function testStatisticUndefinedUnit(): void
    {
        $collection = new UnitStatisticCollection();

        $this->expectException(StatisticException::class);
        $this->expectExceptionMessage(StatisticException::NO_UNIT);
        $collection->get('sdfsdf');
    }
}
