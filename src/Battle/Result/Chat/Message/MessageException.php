<?php

declare(strict_types=1);

namespace Battle\Result\Chat\Message;

use Exception;

class MessageException extends Exception
{
    public const UNDEFINED_MESSAGE_METHOD = 'Message: Undefined Message method';
}
