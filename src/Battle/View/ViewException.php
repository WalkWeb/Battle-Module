<?php

declare(strict_types=1);

namespace Battle\View;

use Exception;

class ViewException extends Exception
{
    public const MISSING_UNIT    = 'Render error: missing unit';
    public const MISSING_COMMAND = 'Render error: missing command';
}
