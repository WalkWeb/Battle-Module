<?php

declare(strict_types=1);

namespace Battle\Chat;

class Chat
{
    /** @var array */
    private $messages = [];

    public function add(string $message): void
    {
        $this->messages[] = $message;
    }

    public function getAll(): array
    {
        return $this->messages;
    }
}
