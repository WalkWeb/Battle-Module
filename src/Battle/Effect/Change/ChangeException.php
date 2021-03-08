<?php

declare(strict_types=1);

namespace Battle\Effect\Change;

use Exception;

class ChangeException extends Exception
{
    public const INVALID_TYPE_DATA       = 'Invalid change type data';
    public const INVALID_INCREASED_DATA  = 'Invalid change increased data';
    public const INVALID_MULTIPLIER_DATA = 'Invalid change multiplier data';
    public const INVALID_POWER_DATA      = 'Invalid power data';
}
