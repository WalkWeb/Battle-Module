<?php

declare(strict_types=1);

namespace Battle\Unit;

use Exception;

class UnitException extends Exception
{
    public const UNDEFINED_ACTION_METHOD             = 'Undefined action';
    public const INCORRECT_ID                        = 'Incorrect parameter "id", it required and type string';
    public const INCORRECT_ID_VALUE                  = 'Incorrect "id", should be min-max length: ';
    public const INCORRECT_NAME                      = 'Incorrect parameter "name", it required and type string';
    public const INCORRECT_NAME_VALUE                = 'Incorrect "name", should be min-max length: ';
    public const INCORRECT_LEVEL                     = 'Incorrect parameter "level", it required and type int';
    public const INCORRECT_LEVEL_VALUE               = 'Incorrect "level", should be min-max value: ';
    public const INCORRECT_AVATAR                    = 'Incorrect parameter "avatar", it required and type string';
    public const INCORRECT_LIFE                      = 'Incorrect "life", it required and type int';
    public const INCORRECT_LIFE_VALUE                = 'Incorrect "life", should be min-max value: ';
    public const INCORRECT_TOTAL_LIFE                = 'Incorrect "total_life", it required and type int';
    public const INCORRECT_TOTAL_LIFE_VALUE          = 'Incorrect "total_life", should be min-max value: ';
    public const LIFE_MORE_TOTAL_LIFE                = 'Parameter "life" more "total_life"';
    public const INCORRECT_MANA                      = 'Incorrect "mana", it required and type int';
    public const INCORRECT_MANA_VALUE                = 'Incorrect "mana", should be min-max value: ';
    public const INCORRECT_TOTAL_MANA                = 'Incorrect "total_mana", it required and type int';
    public const INCORRECT_TOTAL_MANA_VALUE          = 'Incorrect "total_mana", should be min-max value: ';
    public const MANA_MORE_TOTAL_MANA                = 'Parameter "mana" more "total_mana"';
    public const INCORRECT_MELEE                     = 'Incorrect "melee", it required and type bool';
    public const INCORRECT_CLASS                     = 'Incorrect "class", it empty, null or type int';
    public const INCORRECT_RACE                      = 'Incorrect "race", it required and type int';
    public const DOUBLE_UNIT_ID                      = 'Double unit ID: collection have unit with specified id';
    public const INCORRECT_COMMAND                   = 'Incorrect "command". It required int 1 or 2';
    public const INCORRECT_ADD_CONC_MULTIPLIER       = 'Incorrect unit "add_concentration_multiplier", it required and type int';
    public const INCORRECT_ADD_CONC_MULTIPLIER_VALUE = 'Incorrect unit "add_concentration_multiplier", should be min-max value: ';
    public const INCORRECT_CUNNING_MULTIPLIER        = 'Incorrect unit "cunning_multiplier", it required and type int';
    public const INCORRECT_CUNNING_MULTIPLIER_VALUE  = 'Incorrect unit "cunning_multiplier", should be min-max value: ';
    public const INCORRECT_ADD_RAGE_MULTIPLIER       = 'Incorrect unit "add_rage_multiplier", it required and type int';
    public const INCORRECT_ADD_RAGE_MULTIPLIER_VALUE = 'Incorrect unit "add_rage_multiplier", should be min-max value: ';
    public const OVER_REDUCED                        = 'Over reduced unit parameter, mim value: ';
    public const NO_REDUCED_DAMAGE                   = 'No reduced damage';
    public const NO_REDUCED_MAXIMUM_LIFE             = 'No reduced maximum life';
    public const NO_REDUCED_ATTACK_SPEED             = 'No reduced attack speed';
    public const UNDEFINED_MODIFY_METHOD             = 'Undefined modify method';
    public const INCORRECT_DEFENSE                   = 'Incorrect defense, it required and type array';
    public const INCORRECT_OFFENSE                   = 'Incorrect offense, it required and type array';
    public const CANNOT_ACTION                       = 'Unit cannot action. He died or already action';
}
