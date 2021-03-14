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
    private $elements = [];

    public function add(UnitInterface $unit): void
    {
        $this->elements[] = $unit;
    }

    public function current(): UnitInterface
    {
        return current($this->elements);
    }
}
