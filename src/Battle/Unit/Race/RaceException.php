<?php

declare(strict_types=1);

namespace Battle\Unit\Race;

use Exception;

class RaceException extends Exception
{
    public const UNDEFINED_RACE_ID     = 'Undefined race id';
    public const INCORRECT_ID          = 'Incorrect parameter id, it required and type int';
    public const INCORRECT_NAME        = 'Incorrect parameter name, it required and type string';
    public const INCORRECT_SINGLE_NAME = 'Incorrect parameter single_name, it required and type string';
    public const INCORRECT_COLOR       = 'Incorrect parameter color, it required and type string';
    public const INCORRECT_ICON        = 'Incorrect parameter icon, it required and type string';
}
