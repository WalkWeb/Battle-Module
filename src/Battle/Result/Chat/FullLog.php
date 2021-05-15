<?php

declare(strict_types=1);

namespace Battle\Result\Chat;

class FullLog
{
    /** @var array */
    private $log = [];

    public function add(string $log): void
    {
        $this->log[] = $log;
    }

    public function getLog(): array
    {
        return $this->log;
    }
}
