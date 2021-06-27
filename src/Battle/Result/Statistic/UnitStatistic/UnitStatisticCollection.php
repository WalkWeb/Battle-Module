<?php

declare(strict_types=1);

namespace Battle\Result\Statistic\UnitStatistic;

use Battle\Result\Statistic\StatisticException;
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

    /**
     * @param UnitStatisticInterface $unitStatistic
     * @throws StatisticException
     */
    public function add(UnitStatisticInterface $unitStatistic): void
    {
        if ($this->exist($unitStatistic->getUnit()->getId())) {
            throw new StatisticException(StatisticException::DOUBLE_ID);
        }

        $this->elements[$unitStatistic->getUnit()->getId()] = $unitStatistic;
    }

    /**
     * @return UnitStatisticInterface
     */
    public function current(): UnitStatisticInterface
    {
        return current($this->elements);
    }

    /**
     * @param string $id
     * @return bool
     */
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
