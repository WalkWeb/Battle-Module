<?php

declare(strict_types=1);

namespace Battle\View;

use Exception;

class ViewException extends Exception
{
    public const MISSING_UNIT    = 'Render error: missing unit';
    public const MISSING_COMMAND = 'Render error: missing command';
    public const MISSING_RESULT  = 'Render error: missing result battle';
    public const MISSING_VIEW    = 'Render error: missing view';
}
