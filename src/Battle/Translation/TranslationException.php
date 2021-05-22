<?php

declare(strict_types=1);

namespace Battle\Translation;

use Exception;

class TranslationException extends Exception
{
    public const DEFAULT_MESSAGES_NOT_FOUND = 'Default language messages file not found';
    public const MESSAGE_SHOULD_BE_STRING   = 'Message should be a string';
}
