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
    private $elements = [];

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
            $this->elements[] = $action;
        }
    }
}
