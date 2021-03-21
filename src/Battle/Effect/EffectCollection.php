<?php

declare(strict_types=1);

namespace Battle\Effect;

use Battle\Traits\CollectionTrait;
use Countable;
use Iterator;

class EffectCollection implements Iterator, Countable
{
    use CollectionTrait;

    /**
     * @var Effect[]
     */
    private $elements = [];

    public function add(Effect $effect): void
    {
        $this->elements[] = $effect;
    }

    public function current(): Effect
    {
        return current($this->elements);
    }

    public function newRound(): void
    {
        // TODO Выполняет действие эффектов с постоянным эффектом
        // TODO Уменьшает у всех эффектов длительность на 1
        // TODO Удаляет эффекты, длительность которых закончилась
    }
}
