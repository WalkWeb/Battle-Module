<?php

declare(strict_types=1);

namespace Battle\Result\Chat\Message;

use Battle\Action\ActionInterface;

interface MessageInterface
{
    /**
     * Формирует и возвращает сообщение для чата на основе переданного Action
     *
     * @param ActionInterface $action
     * @return string
     */
    public function createMessage(ActionInterface $action): string;
}
