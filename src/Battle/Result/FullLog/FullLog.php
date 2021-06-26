<?php

declare(strict_types=1);

namespace Battle\Result\FullLog;

// todo Добавить интерфейс

class FullLog
{
    /**
     * @var string[]
     */
    private $log = [];

    public function add(string $log): void
    {
        $this->log[] = $log;
    }

    /**
     * @return string[]
     */
    public function getLog(): array
    {
        return $this->log;
    }
}
