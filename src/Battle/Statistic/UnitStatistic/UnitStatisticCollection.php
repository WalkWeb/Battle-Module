<?php

declare(strict_types=1);

namespace Battle\Statistic\UnitStatistic;

use Battle\Statistic\StatisticException;
use Battle\Traits\CollectionTrait;
use Countable;
use Iterator;

class UnitStatisticCollection implements Iterator, Countable
{
    use CollectionTrait;

    /**
     * @var UnitStatisticInterface[]
     */
    private $elements = [];

    public function add(UnitStatisticInterface $unitStatistic): void
    {
        $this->elements[$unitStatistic->getName()] = $unitStatistic;
    }

    public function current(): UnitStatisticInterface
    {
        return current($this->elements);
    }

    public function existUnitByName(string $name): bool
    {
        return array_key_exists($name, $this->elements);
    }

    /**
     * @param string $name
     * @return UnitStatisticInterface
     * @throws StatisticException
     */
    public function getUnitByName(string $name): UnitStatisticInterface
    {
        if (!$this->existUnitByName($name)) {
            throw new StatisticException(StatisticException::NO_UNIT . ': ' . $name);
        }

        return $this->elements[$name];
    }
}
