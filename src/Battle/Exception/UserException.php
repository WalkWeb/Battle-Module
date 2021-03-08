<?php

declare(strict_types=1);

namespace Battle\Exception;

use Exception;

class UserException extends Exception
{
    public const UNDEFINED_ACTION = 'Undefined Action';
}
