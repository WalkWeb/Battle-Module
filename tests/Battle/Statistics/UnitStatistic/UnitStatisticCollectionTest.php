<?php

declare(strict_types=1);

namespace Tests\Battle\Statistics\UnitStatistic;

use Battle\Statistic\UnitStatistic\UnitStatistic;
use Battle\Statistic\UnitStatistic\UnitStatisticCollection;
use PHPUnit\Framework\TestCase;

class UnitStatisticCollectionTest extends TestCase
{
    public function testStatisticsExistUnit(): void
    {
        $collection = new UnitStatisticCollection();

        $collection->add(new UnitStatistic('name_1'));
        $collection->add(new UnitStatistic('name_2'));

        self::assertTrue($collection->existUnitByName('name_1'));
        self::assertFalse($collection->existUnitByName('name_3'));
    }
}
