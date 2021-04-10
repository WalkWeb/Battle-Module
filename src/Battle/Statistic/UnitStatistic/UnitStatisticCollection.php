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
        $this->elements[$unitStatistic->getId()] = $unitStatistic;
    }

    public function current(): UnitStatisticInterface
    {
        return current($this->elements);
    }

    public function exist(string $id): bool
    {
        return array_key_exists($id, $this->elements);
    }

    /**
     * @param string $id
     * @return UnitStatisticInterface
     * @throws StatisticException
     */
    public function get(string $id): UnitStatisticInterface
    {
        if (!$this->exist($id)) {
            throw new StatisticException(StatisticException::NO_UNIT . ': ' . $id);
        }

        return $this->elements[$id];
    }
}
