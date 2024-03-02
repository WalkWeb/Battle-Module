<?php

declare(strict_types=1);

namespace Battle\Unit\Classes;

use Exception;

class UnitClassException extends Exception
{
    public const UNDEFINED_CLASS_ID      = 'Undefined class ID';
    public const INCORRECT_CLASS         = 'Object no implements UnitClassInterface';
    public const INVALID_ABILITY_DATA    = 'Invalid ability data: array excepted';
    public const INVALID_ABILITIES_DATA  = 'Invalid "abilities" data: []array excepted';
    public const INVALID_ID_DATA         = 'Invalid "id" data: int excepted';
    public const INVALID_NAME_DATA       = 'Invalid "name" data: string excepted';
    public const INVALID_SMALL_ICON_DATA = 'Invalid "small_icon" data: string excepted';
}
