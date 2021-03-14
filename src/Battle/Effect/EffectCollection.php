<?php

declare(strict_types=1);

namespace Battle\Effect;

// todo use Trait, implements  Iterator, Countable
class EffectCollection
{
    /**
     * @var Effect[]
     */
    private $effects;

    public function add(Effect $effect): void
    {
        $this->effects[] = $effect;
    }

    /**
     * @return Effect[]
     */
    public function getEffects(): array
    {
        return $this->effects;
    }

    public function newRound(): void
    {
        // TODO Выполняет действие эффектов с постоянным эффектом
        // TODO Уменьшает у всех эффектов длительность на 1
        // TODO Удаляет эффекты, длительность которых закончилась
    }
}
