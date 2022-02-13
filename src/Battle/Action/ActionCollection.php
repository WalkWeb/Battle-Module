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
        // TODO Так работает тест testEffectChangeMultipleActionUnit, но падают другие
        $this->elements[] = clone $action;
        // TODO А так все тесты выполняются, но падает testEffectChangeMultipleActionUnit
        //$this->elements[] = $action;
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
}
