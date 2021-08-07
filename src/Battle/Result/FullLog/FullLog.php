<?php

declare(strict_types=1);

namespace Battle\Result\FullLog;

class FullLog implements FullLogInterface
{
    private const LINE = '<hr>';

    /**
     * @var string[]
     */
    private $log = [];

    public function add(string $log): void
    {
        $this->log[] = $log;
    }

    public function addText(string $text): void
    {
        $this->log[] = '<p>' . $text . '</p>';
    }

    public function addLine(): void
    {
        $this->log[] = self::LINE;
    }

    /**
     * @return string[]
     */
    public function getLog(): array
    {
        return $this->log;
    }
}
