<?php

declare(strict_types=1);

namespace Battle\Unit\Offense;

use Exception;

class OffenseException extends Exception
{
    public const INCORRECT_TYPE_DAMAGE               = 'Incorrect "type_damage", it required and type int';
    public const INCORRECT_TYPE_DAMAGE_VALUE         = 'Incorrect "type_damage", should be 1 or 2';
    public const INCORRECT_PHYSICAL_DAMAGE           = 'Incorrect "physical_damage", it required and type int';
    public const INCORRECT_PHYSICAL_DAMAGE_VALUE     = 'Incorrect "physical_damage", should be min-max value: ';
    public const INCORRECT_ACCURACY                  = 'Incorrect "accuracy", it required and type int';
    public const INCORRECT_ACCURACY_VALUE            = 'Incorrect "accuracy", should be min value: ';
    public const INCORRECT_MAGIC_ACCURACY            = 'Incorrect "magic_accuracy", it required and type int';
    public const INCORRECT_MAGIC_ACCURACY_VALUE      = 'Incorrect "magic_accuracy", should be min value: ';
    public const INCORRECT_ATTACK_SPEED              = 'Incorrect "attack speed", it required and type float or int';
    public const INCORRECT_ATTACK_SPEED_VALUE        = 'Incorrect "attack speed", should be min-max value: ';
    public const INCORRECT_BLOCK_IGNORE              = 'Incorrect "block_ignore", it required and type int';
    public const INCORRECT_BLOCK_IGNORE_VALUE        = 'Incorrect "block_ignore", should be min-max value: ';
    public const INCORRECT_CRITICAL_CHANCE           = 'Incorrect "block_ignore", it required and type int';
    public const INCORRECT_CRITICAL_CHANCE_VALUE     = 'Incorrect "block_ignore", should be min-max value: ';
    public const INCORRECT_CRITICAL_MULTIPLIER       = 'Incorrect "block_ignore", it required and type int';
    public const INCORRECT_CRITICAL_MULTIPLIER_VALUE = 'Incorrect "block_ignore", should be min-max value: ';
    public const INCORRECT_VAMPIRE                   = 'Incorrect "vampire", it required and type int';
    public const INCORRECT_VAMPIRE_VALUE             = 'Incorrect "vampire", should be min-max value: ';
}
