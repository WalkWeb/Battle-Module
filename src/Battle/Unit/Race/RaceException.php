<?php

declare(strict_types=1);

namespace Battle\Unit\Race;

use Exception;

class RaceException extends Exception
{
    public const string UNDEFINED_RACE_ID     = 'Undefined race id';
    public const string INCORRECT_ID          = 'Incorrect parameter id, it required and type int';
    public const string INCORRECT_NAME        = 'Incorrect parameter name, it required and type string';
    public const string INCORRECT_SINGLE_NAME = 'Incorrect parameter single_name, it required and type string';
    public const string INCORRECT_COLOR       = 'Incorrect parameter color, it required and type string';
    public const string INCORRECT_ICON        = 'Incorrect parameter icon, it required and type string';
    public const string INCORRECT_ABILITIES   = 'Incorrect parameter icon, it required and type array';
}
