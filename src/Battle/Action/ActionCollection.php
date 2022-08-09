<?php

declare(strict_types=1);

namespace Battle\Action;

use Battle\Traits\CollectionTrait;
use Countable;
use Iterator;

class ActionCollection implements Iterator, Countable
{
    use CollectionTrait;

    /**
     * @var ActionInterface[]
     */
    private array $elements = [];

    public function add(ActionInterface $action): void
    {
        $this->elements[] = $action;
    }

    public function current(): ActionInterface
    {
        return current($this->elements);
    }

    public function addCollection(ActionCollection $collection): void
    {
        foreach ($collection as $action) {
            $this->add($action);
        }
    }

    public function __clone()
    {
        $elements = [];

        foreach ($this->elements as $action) {
            $elements[] = clone $action;
        }

        $this->elements = $elements;
    }
}
