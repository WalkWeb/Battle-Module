<?php

declare(strict_types=1);

namespace Battle\Unit\Ability;

use Exception;

class AbilityException extends Exception
{
    public const INVALID_ACTION_DATA = 'Invalid action data: array expected';
}
