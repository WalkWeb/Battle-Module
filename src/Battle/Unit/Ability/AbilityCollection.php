<?php

declare(strict_types=1);

namespace Battle\Unit\Ability;

use Battle\Traits\CollectionTrait;
use Battle\Unit\UnitInterface;
use Countable;
use Iterator;

class AbilityCollection implements Iterator, Countable
{
    use CollectionTrait;

    /**
     * @var AbilityInterface[]
     */
    private $elements = [];

    /**
     * @param AbilityInterface $ability
     */
    public function add(AbilityInterface $ability): void
    {
        $this->elements[] = $ability;
    }

    /**
     * @return AbilityInterface
     */
    public function current(): AbilityInterface
    {
        return current($this->elements);
    }

    /**
     * Сообщает всем способностям, что юнит изменился - способности проверят, должны ли они активироваться
     *
     * @param UnitInterface $unit
     */
    public function update(UnitInterface $unit): void
    {
        foreach ($this->elements as $ability) {
            $ability->update($unit);
        }
    }
}
