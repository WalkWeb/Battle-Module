<?php

declare(strict_types=1);

namespace Battle\Unit\Defense;

use Exception;

class DefenseException extends Exception
{
    public const INCORRECT_PHYSICAL_RESIST           = 'Incorrect "physical_resist", it required and type int';
    public const INCORRECT_PHYSICAL_RESIST_VALUE     = 'Incorrect "physical_resist", should be min value: ';
    public const INCORRECT_FIRE_RESIST               = 'Incorrect "fire_resist", it required and type int';
    public const INCORRECT_FIRE_RESIST_VALUE         = 'Incorrect "fire_resist", should be min value: ';
    public const INCORRECT_WATER_RESIST              = 'Incorrect "water_resist", it required and type int';
    public const INCORRECT_WATER_RESIST_VALUE        = 'Incorrect "water_resist", should be min value: ';
    public const INCORRECT_AIR_RESIST                = 'Incorrect "air_resist", it required and type int';
    public const INCORRECT_AIR_RESIST_VALUE          = 'Incorrect "air_resist", should be min value: ';
    public const INCORRECT_EARTH_RESIST              = 'Incorrect "earth_resist", it required and type int';
    public const INCORRECT_EARTH_RESIST_VALUE        = 'Incorrect "earth_resist", should be min value: ';
    public const INCORRECT_LIFE_RESIST               = 'Incorrect "life_resist", it required and type int';
    public const INCORRECT_LIFE_RESIST_VALUE         = 'Incorrect "life_resist", should be min value: ';
    public const INCORRECT_DEATH_RESIST              = 'Incorrect "death_resist", it required and type int';
    public const INCORRECT_DEATH_RESIST_VALUE        = 'Incorrect "death_resist", should be min value: ';
    public const INCORRECT_DEFENSE                   = 'Incorrect "defense", it required and type int';
    public const INCORRECT_DEFENSE_VALUE             = 'Incorrect "defense", should be min value: ';
    public const INCORRECT_MAGIC_DEFENSE             = 'Incorrect "magic_defense", it required and type int';
    public const INCORRECT_MAGIC_DEFENSE_VALUE       = 'Incorrect "magic_defense", should be min value: ';
    public const INCORRECT_BLOCK                     = 'Incorrect "block", it required and type int';
    public const INCORRECT_BLOCK_VALUE               = 'Incorrect "block", should be min-max value: ';
    public const INCORRECT_MAGIC_BLOCK               = 'Incorrect "magic_block", it required and type int';
    public const INCORRECT_MAGIC_BLOCK_VALUE         = 'Incorrect "magic_block", should be min-max value: ';
    public const INCORRECT_MENTAL_BARRIER            = 'Incorrect "mental_barrier", it required and type int';
    public const INCORRECT_MENTAL_BARRIER_VALUE      = 'Incorrect "mental_barrier", should be min-max value: ';
    public const INCORRECT_MAX_PHYSICAL_RESIST       = 'Incorrect "max_physical_resist", it required and type int';
    public const INCORRECT_MAX_PHYSICAL_RESIST_VALUE = 'Incorrect "max_physical_resist", should be min-max value: ';
    public const INCORRECT_MAX_FIRE_RESIST           = 'Incorrect "max_fire_resist", it required and type int';
    public const INCORRECT_MAX_FIRE_RESIST_VALUE     = 'Incorrect "max_fire_resist", should be min-max value: ';
    public const INCORRECT_MAX_WATER_RESIST          = 'Incorrect "max_water_resist", it required and type int';
    public const INCORRECT_MAX_WATER_RESIST_VALUE    = 'Incorrect "max_water_resist", should be min-max value: ';
    public const INCORRECT_MAX_AIR_RESIST            = 'Incorrect "max_air_resist", it required and type int';
    public const INCORRECT_MAX_AIR_RESIST_VALUE      = 'Incorrect "max_air_resist", should be min-max value: ';
    public const INCORRECT_MAX_EARTH_RESIST          = 'Incorrect "max_earth_resist", it required and type int';
    public const INCORRECT_MAX_EARTH_RESIST_VALUE    = 'Incorrect "max_earth_resist", should be min-max value: ';
    public const INCORRECT_MAX_LIFE_RESIST           = 'Incorrect "max_life_resist", it required and type int';
    public const INCORRECT_MAX_LIFE_RESIST_VALUE     = 'Incorrect "max_life_resist", should be min-max value: ';
    public const INCORRECT_MAX_DEATH_RESIST          = 'Incorrect "max_death_resist", it required and type int';
    public const INCORRECT_MAX_DEATH_RESIST_VALUE    = 'Incorrect "max_death_resist", should be min-max value: ';
}
