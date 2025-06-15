<?php

declare(strict_types=1);

namespace Battle\Unit\Offense\MultipleOffense;

use Exception;

class MultipleOffenseException extends Exception
{
    public const string EMPTY_DATA                            = 'MultipleOffenseFactory: empty data. Skip "multiple_offense" parameter for use default Unit Offense';
    public const string INVALID_DAMAGE                        = 'MultipleOffenseFactory: Incorrect "damage", it empty or type float';
    public const string INVALID_DAMAGE_VALUE                  = 'MultipleOffenseFactory: Incorrect "damage", should be min-max value: ';
    public const string INVALID_SPEED                         = 'MultipleOffenseFactory: Incorrect "speed", it empty or type float';
    public const string INVALID_SPEED_VALUE                   = 'MultipleOffenseFactory: Incorrect "speed", it empty or type float';
    public const string INVALID_ACCURACY                      = 'MultipleOffenseFactory: Incorrect "accuracy", it empty or type float';
    public const string INVALID_ACCURACY_VALUE                = 'MultipleOffenseFactory: Incorrect "accuracy", it empty or type float';
    public const string INVALID_CRITICAL_CHANCE               = 'MultipleOffenseFactory: Incorrect "critical_chance", it empty or type float';
    public const string INVALID_CRITICAL_CHANCE_VALUE         = 'MultipleOffenseFactory: Incorrect "critical_chance", it empty or type float';
    public const string INVALID_CRITICAL_MULTIPLIER           = 'MultipleOffenseFactory: Incorrect "critical_multiplier", it empty or type float';
    public const string INVALID_CRITICAL_MULTIPLIER_VALUE     = 'MultipleOffenseFactory: Incorrect "critical_multiplier", it empty or type float';
    public const string INVALID_VAMPIRISM                     = 'MultipleOffenseFactory: Incorrect "vampirism", it empty or type int';
    public const string INVALID_VAMPIRISM_VALUE               = 'MultipleOffenseFactory: Incorrect "vampirism", invalid value';
    public const string INVALID_BLOCK_IGNORING                = 'MultipleOffenseFactory: Incorrect "block_ignoring", it empty or type int';
    public const string INVALID_BLOCK_IGNORING_VALUE          = 'MultipleOffenseFactory: Incorrect "block_ignoring", invalid value';
    public const string INVALID_CRITICAL_DAMAGE_CONVERT       = 'MultipleOffenseFactory: Incorrect "damage_convert", it empty or type string';
    public const string INVALID_CRITICAL_DAMAGE_CONVERT_VALUE = 'MultipleOffense: Incorrect "damage_convert", invalid value';
}
