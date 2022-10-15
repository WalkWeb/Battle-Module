<?php

declare(strict_types=1);

namespace Battle\Unit\Defense;

use Exception;

class DefenseException extends Exception
{
    public const INCORRECT_PHYSICAL_RESIST       = 'Incorrect "physical_resist", it required and type int';
    public const INCORRECT_PHYSICAL_RESIST_VALUE = 'Incorrect "physical_resist", should be min value: ';
    public const INCORRECT_FIRE_RESIST           = 'Incorrect "fire_resist", it required and type int';
    public const INCORRECT_FIRE_RESIST_VALUE     = 'Incorrect "fire_resist", should be min value: ';
    public const INCORRECT_WATER_RESIST          = 'Incorrect "water_resist", it required and type int';
    public const INCORRECT_WATER_RESIST_VALUE    = 'Incorrect "water_resist", should be min value: ';
    public const INCORRECT_AIR_RESIST            = 'Incorrect "air_resist", it required and type int';
    public const INCORRECT_AIR_RESIST_VALUE      = 'Incorrect "air_resist", should be min value: ';
    public const INCORRECT_EARTH_RESIST          = 'Incorrect "earth_resist", it required and type int';
    public const INCORRECT_EARTH_RESIST_VALUE    = 'Incorrect "earth_resist", should be min value: ';
    public const INCORRECT_LIFE_RESIST           = 'Incorrect "life_resist", it required and type int';
    public const INCORRECT_LIFE_RESIST_VALUE     = 'Incorrect "life_resist", should be min value: ';
    public const INCORRECT_DEATH_RESIST          = 'Incorrect "death_resist", it required and type int';
    public const INCORRECT_DEATH_RESIST_VALUE    = 'Incorrect "death_resist", should be min value: ';
    public const INCORRECT_DEFENSE               = 'Incorrect defense, it required and type int';
    public const INCORRECT_DEFENSE_VALUE         = 'Incorrect defense, should be min value: ';
    public const INCORRECT_MAGIC_DEFENSE         = 'Incorrect magic_defense, it required and type int';
    public const INCORRECT_MAGIC_DEFENSE_VALUE   = 'Incorrect magic_defense, should be min value: ';
    public const INCORRECT_BLOCK                 = 'Incorrect block, it required and type int';
    public const INCORRECT_BLOCK_VALUE           = 'Incorrect block, should be min-max value: ';
    public const INCORRECT_MAGIC_BLOCK           = 'Incorrect block, it required and type int';
    public const INCORRECT_MAGIC_BLOCK_VALUE     = 'Incorrect block, should be min-max value: ';
    public const INCORRECT_MENTAL_BARRIER        = 'Incorrect mental_barrier, it required and type int';
    public const INCORRECT_MENTAL_BARRIER_VALUE  = 'Incorrect mental_barrier, should be min-max value: ';
}
