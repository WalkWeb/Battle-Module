<?php

declare(strict_types=1);

namespace Battle\Result\Chat;

interface ChatInterface
{
    /**
     * Добавляет сообщение в чат
     *
     * @param string $message
     */
    public function add(string $message): void;

    /**
     * Возвращает массив сообщений
     *
     * @return array
     */
    public function getMessages(): array;
}
