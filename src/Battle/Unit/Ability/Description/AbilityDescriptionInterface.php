<?php

declare(strict_types=1);

namespace Battle\Unit\Ability\Description;

interface AbilityDescriptionInterface
{
    /**
     * Возвращает строковое описание способности.
     *
     * @return string
     */
    public function __toString(): string;
}
