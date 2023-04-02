<?php

declare(strict_types=1);

namespace Battle\Response\Chat;

use Exception;

class ChatException extends Exception
{
    public const UNDEFINED_MESSAGE_METHOD = 'Chat: undefined create message method';
}
