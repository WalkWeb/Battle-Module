<?php

declare(strict_types=1);

namespace Battle\Result\Chat;

class Chat implements ChatInterface
{
    /**
     * @var string[]
     */
    private $messages = [];

    public function add(string $message): void
    {
        $this->messages[] = $message;
    }

    /**
     * @return string[]
     */
    public function getMessages(): array
    {
        return $this->messages;
    }
}
