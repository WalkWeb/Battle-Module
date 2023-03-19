<?php

declare(strict_types=1);

namespace Battle\Unit\Defense;

use Battle\Traits\ValidationTrait;
use Exception;

class DefenseFactory
{
    use ValidationTrait;

    /**
     * Создает Defense на основе массива с данными
     *
     * @param array $data
     * @return Defense
     * @throws Exception
     */
    public static function create(array $data): DefenseInterface
    {
        self::int($data, 'physical_resist', DefenseException::INCORRECT_PHYSICAL_RESIST);
        self::int($data, 'fire_resist', DefenseException::INCORRECT_FIRE_RESIST);
        self::int($data, 'water_resist', DefenseException::INCORRECT_WATER_RESIST);
        self::int($data, 'air_resist', DefenseException::INCORRECT_AIR_RESIST);
        self::int($data, 'earth_resist', DefenseException::INCORRECT_EARTH_RESIST);
        self::int($data, 'life_resist', DefenseException::INCORRECT_LIFE_RESIST);
        self::int($data, 'death_resist', DefenseException::INCORRECT_DEATH_RESIST);
        self::int($data, 'defense', DefenseException::INCORRECT_DEFENSE);
        self::int($data, 'magic_defense', DefenseException::INCORRECT_MAGIC_DEFENSE);
        self::int($data, 'block', DefenseException::INCORRECT_BLOCK);
        self::int($data, 'magic_block', DefenseException::INCORRECT_MAGIC_BLOCK);
        self::int($data, 'mental_barrier', DefenseException::INCORRECT_MENTAL_BARRIER);
        self::int($data, 'max_physical_resist', DefenseException::INCORRECT_MAX_PHYSICAL_RESIST);
        self::int($data, 'max_fire_resist', DefenseException::INCORRECT_MAX_FIRE_RESIST);
        self::int($data, 'max_water_resist', DefenseException::INCORRECT_MAX_WATER_RESIST);
        self::int($data, 'max_air_resist', DefenseException::INCORRECT_MAX_AIR_RESIST);
        self::int($data, 'max_earth_resist', DefenseException::INCORRECT_MAX_EARTH_RESIST);
        self::int($data, 'max_life_resist', DefenseException::INCORRECT_MAX_LIFE_RESIST);
        self::int($data, 'max_death_resist', DefenseException::INCORRECT_MAX_DEATH_RESIST);
        self::int($data, 'global_resist', DefenseException::INCORRECT_GLOBAL_RESIST);
        self::int($data, 'dodge', DefenseException::INCORRECT_DODGE);

        return new Defense(
            $data['physical_resist'],
            $data['fire_resist'],
            $data['water_resist'],
            $data['air_resist'],
            $data['earth_resist'],
            $data['life_resist'],
            $data['death_resist'],
            $data['defense'],
            $data['magic_defense'],
            $data['block'],
            $data['magic_block'],
            $data['mental_barrier'],
            $data['max_physical_resist'],
            $data['max_fire_resist'],
            $data['max_water_resist'],
            $data['max_air_resist'],
            $data['max_earth_resist'],
            $data['max_life_resist'],
            $data['max_death_resist'],
            $data['global_resist'],
            $data['dodge']
        );
    }
}
