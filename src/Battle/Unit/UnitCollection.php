<?php

declare(strict_types=1);

namespace Battle\Unit;

use ArrayAccess;
use Battle\Traits\ArrayAccessTrait;
use Battle\Traits\CollectionTrait;
use Countable;
use Iterator;

class UnitCollection implements Iterator, Countable, ArrayAccess
{
    use CollectionTrait;
    use ArrayAccessTrait;

    /**
     * @var UnitInterface[]
     */
    private $elements = [];

    /**
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
