<?php

declare(strict_types=1);

namespace Battle\View;

use Exception;

class ViewException extends Exception
{
    public const string MISSING_UNIT     = 'Render error: missing unit';
    public const string MISSING_COMMAND  = 'Render error: missing command';
    public const string MISSING_RESULT   = 'Render error: missing response battle';
    public const string MISSING_VIEW     = 'Render error: missing view';
    public const string MISSING_TEMPLATE = 'Missing template';
}
