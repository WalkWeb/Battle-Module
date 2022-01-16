<?php

declare(strict_types=1);

namespace Battle\Traits;

// TODO Временный Trait, в будущем будет удален

trait ArrayAccessTrait
{
    public function offsetExists($offset): bool
    {
        // TODO Это временный костыль для работы временного кода, задача которого (временного кода) - разбить глобальное
        // TODO изменение работы Action на несколько независимых коммитов
        if ($offset === 0) {
            return (count($this->elements) > 0);
        }

        return array_key_exists($offset, $this->elements);
    }

    public function offsetGet($offset)
    {
        // С.м. комментарий выше
        if ($offset === 0) {
            foreach ($this->elements as $element) {
                return $element;
            }
        }

        return $this->elements[$offset];
    }

    public function offsetSet($offset, $value): void
    {
        $this->elements[$offset] = $value;
    }

    public function offsetUnset($offset): void
    {
        unset($this->elements[$offset]);
    }
}
