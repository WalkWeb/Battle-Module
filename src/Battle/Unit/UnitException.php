<?php

declare(strict_types=1);

namespace Battle\Unit;

use Exception;

class UnitException extends Exception
{
    public const UNDEFINED_ACTION_METHOD      = 'Undefined action';
    public const INCORRECT_ID                 = 'Incorrect parameter id, it required and type string';
    public const INCORRECT_NAME               = 'Incorrect parameter name, it required and type string';
    public const INCORRECT_NAME_VALUE         = 'Incorrect name, should be min-max length: ';
    public const INCORRECT_LEVEL              = 'Incorrect parameter level, it required and type int';
    public const INCORRECT_LEVEL_VALUE        = 'Incorrect level, should be min-max value: ';
    public const INCORRECT_AVATAR             = 'Incorrect parameter avatar, it required and type string';
    public const INCORRECT_DAMAGE             = 'Incorrect damage, it required and type  int';
    public const INCORRECT_DAMAGE_VALUE       = 'Incorrect damage, should be min-max value: ';
    public const INCORRECT_ATTACK_SPEED       = 'Incorrect attack speed, it required and type float or int';
    public const INCORRECT_ATTACK_SPEED_VALUE = 'Incorrect attack speed, should be min-max value: ';
    public const INCORRECT_LIFE               = 'Incorrect life, it required and type  int';
    public const INCORRECT_LIFE_VALUE         = 'Incorrect life, should be min-max value: ';
    public const INCORRECT_TOTAL_LIFE         = 'Incorrect total life, it required and type int';
    public const INCORRECT_TOTAL_LIFE_VALUE   = 'Incorrect total life, should be min-max value: ';
    public const LIFE_MORE_TOTAL_LIFE         = 'Life more total life';
    public const INCORRECT_MELEE              = 'Incorrect melee, it required and type  bool';
    public const INCORRECT_CLASS              = 'Incorrect class, it required and type  int';
    public const DOUBLE_UNIT_ID               = 'Double unit ID: collection have unit with specified id';
}
