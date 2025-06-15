<?php

declare(strict_types=1);

namespace Battle\Unit\Classes;

use Exception;

class UnitClassException extends Exception
{
    public const string UNDEFINED_CLASS_ID      = 'Undefined class ID';
    public const string INVALID_ABILITY_DATA    = 'Invalid ability data: array excepted';
    public const string INVALID_ABILITIES_DATA  = 'Invalid "abilities" data: []array excepted';
    public const string INVALID_ID_DATA         = 'Invalid "id" data: int excepted';
    public const string INVALID_NAME_DATA       = 'Invalid "name" data: string excepted';
    public const string INVALID_SMALL_ICON_DATA = 'Invalid "small_icon" data: string excepted';
}
