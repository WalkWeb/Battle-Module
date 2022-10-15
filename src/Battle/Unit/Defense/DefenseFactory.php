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

        // TODO Значения на min-max значение резиста проверяется в конструкторе самого Defense, и эти проверки можно сократить

        self::intMinMaxValue(
            $data['physical_resist'],
            DefenseInterface::MIN_RESISTANCE,
            DefenseInterface::MAX_RESISTANCE,
            DefenseException::INCORRECT_PHYSICAL_RESIST_VALUE . DefenseInterface::MIN_RESISTANCE . '-' . DefenseInterface::MAX_RESISTANCE
        );

        self::intMinMaxValue(
            $data['fire_resist'],
            DefenseInterface::MIN_RESISTANCE,
            DefenseInterface::MAX_RESISTANCE,
            DefenseException::INCORRECT_FIRE_RESIST_VALUE . DefenseInterface::MIN_RESISTANCE . '-' . DefenseInterface::MAX_RESISTANCE
        );

        self::intMinMaxValue(
            $data['water_resist'],
            DefenseInterface::MIN_RESISTANCE,
            DefenseInterface::MAX_RESISTANCE,
            DefenseException::INCORRECT_WATER_RESIST_VALUE . DefenseInterface::MIN_RESISTANCE . '-' . DefenseInterface::MAX_RESISTANCE
        );

        self::intMinMaxValue(
            $data['air_resist'],
            DefenseInterface::MIN_RESISTANCE,
            DefenseInterface::MAX_RESISTANCE,
            DefenseException::INCORRECT_AIR_RESIST_VALUE . DefenseInterface::MIN_RESISTANCE . '-' . DefenseInterface::MAX_RESISTANCE
        );

        self::intMinMaxValue(
            $data['earth_resist'],
            DefenseInterface::MIN_RESISTANCE,
            DefenseInterface::MAX_RESISTANCE,
            DefenseException::INCORRECT_EARTH_RESIST_VALUE . DefenseInterface::MIN_RESISTANCE . '-' . DefenseInterface::MAX_RESISTANCE
        );

        self::intMinMaxValue(
            $data['life_resist'],
            DefenseInterface::MIN_RESISTANCE,
            DefenseInterface::MAX_RESISTANCE,
            DefenseException::INCORRECT_LIFE_RESIST_VALUE . DefenseInterface::MIN_RESISTANCE . '-' . DefenseInterface::MAX_RESISTANCE
        );

        self::intMinMaxValue(
            $data['death_resist'],
            DefenseInterface::MIN_RESISTANCE,
            DefenseInterface::MAX_RESISTANCE,
            DefenseException::INCORRECT_DEATH_RESIST_VALUE . DefenseInterface::MIN_RESISTANCE . '-' . DefenseInterface::MAX_RESISTANCE
        );

        self::intMinMaxValue(
            $data['defense'],
            DefenseInterface::MIN_DEFENSE,
            DefenseInterface::MAX_DEFENSE,
            DefenseException::INCORRECT_DEFENSE_VALUE . DefenseInterface::MIN_DEFENSE . '-' . DefenseInterface::MAX_DEFENSE
        );

        self::intMinMaxValue(
            $data['magic_defense'],
            DefenseInterface::MIN_MAGIC_DEFENSE,
            DefenseInterface::MAX_MAGIC_DEFENSE,
            DefenseException::INCORRECT_MAGIC_DEFENSE_VALUE . DefenseInterface::MIN_MAGIC_DEFENSE . '-' . DefenseInterface::MAX_MAGIC_DEFENSE
        );

        self::intMinMaxValue(
            $data['block'],
            DefenseInterface::MIN_BLOCK,
            DefenseInterface::MAX_BLOCK,
            DefenseException::INCORRECT_BLOCK_VALUE . DefenseInterface::MIN_BLOCK . '-' . DefenseInterface::MAX_BLOCK
        );

        self::intMinMaxValue(
            $data['magic_block'],
            DefenseInterface::MIN_MAGIC_BLOCK,
            DefenseInterface::MAX_MAGIC_BLOCK,
            DefenseException::INCORRECT_MAGIC_BLOCK_VALUE . DefenseInterface::MIN_MAGIC_BLOCK . '-' . DefenseInterface::MAX_MAGIC_BLOCK
        );

        self::intMinMaxValue(
            $data['mental_barrier'],
            DefenseInterface::MIN_MENTAL_BARRIER,
            DefenseInterface::MAX_MENTAL_BARRIER,
            DefenseException::INCORRECT_MENTAL_BARRIER_VALUE . DefenseInterface::MIN_MENTAL_BARRIER . '-' . DefenseInterface::MAX_MENTAL_BARRIER
        );

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
            $data['mental_barrier']
        );
    }
}
