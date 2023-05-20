<?php

declare(strict_types=1);

namespace Battle\Unit;

use Battle\Traits\CollectionTrait;
use Countable;
use Iterator;

class UnitCollection implements Iterator, Countable
{
    use CollectionTrait;

    /**
     * @var UnitInterface[]
     */
    private array $elements = [];

    /**
     * Добавляет юнита в коллекцию
     *
     * @param UnitInterface $unit
     * @throws UnitException
     */
    public function add(UnitInterface $unit): void
    {
        if ($this->exist($unit->getId())) {
            throw new UnitException(UnitException::DOUBLE_UNIT_ID . ': ' . $unit->getId());
        }

        $this->elements[$unit->getId()] = $unit;
    }

    /**
     * Добавляет юнита в коллекцию если его там нет
     *
     * @param UnitInterface $unit
     * @throws UnitException
     */
    public function addIfMissing(UnitInterface $unit): void
    {
        if (!$this->exist($unit->getId())) {
            $this->add($unit);
        }
    }
    
    /**
     * @return UnitInterface
     */
    public function current(): UnitInterface
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
}
