<?php

declare(strict_types=1);

namespace Battle\Unit\Offense\MultipleOffense;

use Exception;

class MultipleOffenseException extends Exception
{
    public const INVALID_PHYSICAL_DAMAGE     = 'Incorrect "physical_damage", it empty or type float';
    public const INVALID_FIRE_DAMAGE         = 'Incorrect "fire_damage", it empty or type float';
    public const INVALID_WATER_DAMAGE        = 'Incorrect "water_damage", it empty or type float';
    public const INVALID_AIR_DAMAGE          = 'Incorrect "air_damage", it empty or type float';
    public const INVALID_EARTH_DAMAGE        = 'Incorrect "earth_damage", it empty or type float';
    public const INVALID_LIFE_DAMAGE         = 'Incorrect "life_damage", it empty or type float';
    public const INVALID_DEATH_DAMAGE        = 'Incorrect "death_damage", it empty or type float';
    public const INVALID_ATTACK_SPEED        = 'Incorrect "attack_speed", it empty or type float';
    public const INVALID_CAST_SPEED          = 'Incorrect "cast_speed", it empty or type float';
    public const INVALID_ACCURACY            = 'Incorrect "accuracy", it empty or type float';
    public const INVALID_MAGIC_ACCURACY      = 'Incorrect "magic_accuracy", it empty or type float';
    public const INVALID_CRITICAL_CHANCE     = 'Incorrect "critical_chance", it empty or type float';
    public const INVALID_CRITICAL_MULTIPLIER = 'Incorrect "critical_multiplier", it empty or type float';
}
