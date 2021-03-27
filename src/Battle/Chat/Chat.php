<?php

declare(strict_types=1);

namespace Battle\Chat;

// TODO По факту это никакой не чат, а хранилище html кода - переделать
class Chat
{
    /** @var array */
    private $messages = [];

    public function add(string $message): void
    {
        $this->messages[] = $message;
    }

    public function getMessages(): array
    {
        return $this->messages;
    }
}
