<?php

declare(strict_types=1);

namespace Battle\Unit\Offense\MultipleOffense;

use Exception;

class MultipleOffenseException extends Exception
{
    public const INVALID_DAMAGE                    = 'MultipleOffenseFactory: Incorrect "damage", it empty or type float';
    public const INVALID_DAMAGE_VALUE              = 'MultipleOffenseFactory: Incorrect "damage", should be min-max value: ';
    public const INVALID_ATTACK_SPEED              = 'MultipleOffenseFactory: Incorrect "attack_speed", it empty or type float';
    public const INVALID_ATTACK_SPEED_VALUE        = 'MultipleOffenseFactory: Incorrect "attack_speed", it empty or type float';
    public const INVALID_CAST_SPEED                = 'MultipleOffenseFactory: Incorrect "cast_speed", it empty or type float';
    public const INVALID_CAST_SPEED_VALUE          = 'MultipleOffenseFactory: Incorrect "cast_speed", it empty or type float';
    public const INVALID_ACCURACY                  = 'MultipleOffenseFactory: Incorrect "accuracy", it empty or type float';
    public const INVALID_ACCURACY_VALUE            = 'MultipleOffenseFactory: Incorrect "accuracy", it empty or type float';
    public const INVALID_MAGIC_ACCURACY            = 'MultipleOffenseFactory: Incorrect "magic_accuracy", it empty or type float';
    public const INVALID_MAGIC_ACCURACY_VALUE      = 'MultipleOffenseFactory: Incorrect "magic_accuracy", it empty or type float';
    public const INVALID_CRITICAL_CHANCE           = 'MultipleOffenseFactory: Incorrect "critical_chance", it empty or type float';
    public const INVALID_CRITICAL_CHANCE_VALUE     = 'MultipleOffenseFactory: Incorrect "critical_chance", it empty or type float';
    public const INVALID_CRITICAL_MULTIPLIER       = 'MultipleOffenseFactory: Incorrect "critical_multiplier", it empty or type float';
    public const INVALID_CRITICAL_MULTIPLIER_VALUE = 'MultipleOffenseFactory: Incorrect "critical_multiplier", it empty or type float';
}
