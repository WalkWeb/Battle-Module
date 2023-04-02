<?php

declare(strict_types=1);

namespace Battle\Response\FullLog;

class FullLog implements FullLogInterface
{
    private const LINE = '<hr>';

    /**
     * @var string[]
     */
    private array $log = [];

    public function add(string $log): void
    {
        $this->log[] = $log;
    }

    public function addText(string $text): void
    {
        if ($text !== '') {
            $this->log[] = '<p class="chat_message">' . $text . '</p>';
        }
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
