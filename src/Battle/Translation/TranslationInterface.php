<?php

declare(strict_types=1);

namespace Battle\Translation;

interface TranslationInterface
{
    public function trans(string $message): string;
}
