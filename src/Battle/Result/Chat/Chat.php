<?php

declare(strict_types=1);

namespace Battle\Result\Chat;

class Chat
{
    // Сообщения в чате изначально должны быть скрыты, и появляться по ходу боя. Здесь указано название класса
    // со стилем display: hidden
    public const HIDDEN_CLASS = 'none';

    /**
     * @var string[]
     */
    private $messages = [];

    public function add(string $message): void
    {
        // TODO Подумать над тем, как убрать генерацию html-кода в templates
        $this->messages[] = '<p class="' . self::HIDDEN_CLASS . '">' . $message . '</p>';
    }

    /**
     * @return string[]
     */
    public function getMessages(): array
    {
        return $this->messages;
    }
}
