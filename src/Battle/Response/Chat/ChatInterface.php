<?php

declare(strict_types=1);

namespace Battle\Response\Chat;

use Battle\Action\ActionInterface;

interface ChatInterface
{
    /**
     * Формирует сообщение (строку) на основании переданного action, сохраняет его в чате и возвращает его, чтобы его
     * можно было использовать где-то еще (например, сохранить в логе боя)
     *
     * @param ActionInterface $action
     * @return string
     * @throws ChatException
     */
    public function addMessage(ActionInterface $action): string;

    /**
     * Возвращает массив сообщений (массив строк)
     *
     * @return array
     */
    public function getMessages(): array;
}
