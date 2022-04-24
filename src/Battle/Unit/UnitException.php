<?php

declare(strict_types=1);

namespace Battle\Unit;

use Exception;

class UnitException extends Exception
{
    public const UNDEFINED_ACTION_METHOD      = 'Undefined action';
    public const INCORRECT_ID                 = 'Incorrect parameter id, it required and type string';
    public const INCORRECT_ID_VALUE           = 'Incorrect id, should be min-max length: ';
    public const INCORRECT_NAME               = 'Incorrect parameter name, it required and type string';
    public const INCORRECT_NAME_VALUE         = 'Incorrect name, should be min-max length: ';
    public const INCORRECT_LEVEL              = 'Incorrect parameter level, it required and type int';
    public const INCORRECT_LEVEL_VALUE        = 'Incorrect level, should be min-max value: ';
    public const INCORRECT_AVATAR             = 'Incorrect parameter avatar, it required and type string';
    public const INCORRECT_LIFE               = 'Incorrect life, it required and type int';
    public const INCORRECT_LIFE_VALUE         = 'Incorrect life, should be min-max value: ';
    public const INCORRECT_TOTAL_LIFE         = 'Incorrect total life, it required and type int';
    public const INCORRECT_TOTAL_LIFE_VALUE   = 'Incorrect total life, should be min-max value: ';
    public const LIFE_MORE_TOTAL_LIFE         = 'Life more total life';
    public const INCORRECT_MELEE              = 'Incorrect melee, it required and type bool';
    public const INCORRECT_CLASS              = 'Incorrect class, it empty, null or type int';
    public const INCORRECT_RACE               = 'Incorrect race, it required and type int';
    public const DOUBLE_UNIT_ID               = 'Double unit ID: collection have unit with specified id';
    public const INCORRECT_COMMAND            = 'Incorrect unit command. It required int 1 or 2';
    public const NO_REDUCED_DAMAGE            = 'No reduced damage';
    public const NO_REDUCED_MAXIMUM_LIFE      = 'No reduced maximum life';
    public const NO_REDUCED_ATTACK_SPEED      = 'No reduced attack speed';
    public const UNDEFINED_MODIFY_METHOD      = 'Undefined modify method';
    public const INCORRECT_DEFENSE            = 'Incorrect defense, it required and type array';
    public const INCORRECT_OFFENSE            = 'Incorrect offense, it required and type array';
    public const INCORRECT_RACE_ABILITY       = 'Incorrect race ability';
}
