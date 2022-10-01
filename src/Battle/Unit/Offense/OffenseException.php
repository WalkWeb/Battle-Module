<?php

declare(strict_types=1);

namespace Battle\Unit\Offense;

use Exception;

class OffenseException extends Exception
{
    public const INCORRECT_DAMAGE_TYPE               = 'Incorrect "damage_type", it required and type int';
    public const INCORRECT_DAMAGE_TYPE_VALUE         = 'Incorrect "damage_type", should be 1 or 2';
    public const INCORRECT_WEAPON_TYPE               = 'Incorrect "weapon_type", it required and type int';
    public const INCORRECT_PHYSICAL_DAMAGE           = 'Incorrect "physical_damage", it required and type int';
    public const INCORRECT_PHYSICAL_DAMAGE_VALUE     = 'Incorrect "physical_damage", should be min-max value: ';
    public const INCORRECT_FIRE_DAMAGE               = 'Incorrect "fire_damage", it required and type int';
    public const INCORRECT_FIRE_DAMAGE_VALUE         = 'Incorrect "fire_damage", should be min-max value: ';
    public const INCORRECT_WATER_DAMAGE              = 'Incorrect "water_damage", it required and type int';
    public const INCORRECT_WATER_DAMAGE_VALUE        = 'Incorrect "water_damage", should be min-max value: ';
    public const INCORRECT_AIR_DAMAGE                = 'Incorrect "air_damage", it required and type int';
    public const INCORRECT_AIR_DAMAGE_VALUE          = 'Incorrect "air_damage", should be min-max value: ';
    public const INCORRECT_EARTH_DAMAGE              = 'Incorrect "earth_damage", it required and type int';
    public const INCORRECT_EARTH_DAMAGE_VALUE        = 'Incorrect "earth_damage", should be min-max value: ';
    public const INCORRECT_LIFE_DAMAGE               = 'Incorrect "life_damage", it required and type int';
    public const INCORRECT_LIFE_DAMAGE_VALUE         = 'Incorrect "life_damage", should be min-max value: ';
    public const INCORRECT_DEATH_DAMAGE              = 'Incorrect "death_damage", it required and type int';
    public const INCORRECT_DEATH_DAMAGE_VALUE        = 'Incorrect "death_damage", should be min-max value: ';
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
