<?php

declare(strict_types=1);

namespace Battle\Unit\Ability\Description;

use Battle\Translation\TranslationInterface;

class AbilityDescription implements AbilityDescriptionInterface
{
    private string $description;

    private array $values;

    private TranslationInterface $translation;

    public function __construct(string $description, array $values, TranslationInterface $translation)
    {
        $this->description = $description;
        $this->values = $values;
        $this->translation = $translation;
    }

    public function __toString(): string
    {
        return sprintf(
            $this->translation->trans($this->description),
            ...$this->values
        );
    }
}
