<?php

declare(strict_types=1);

namespace Battle\Unit\Offense;

use Battle\Traits\ValidationTrait;
use Exception;

class OffenseFactory
{
    use ValidationTrait;

    /**
     * Создает Offense на основе массива с данными
     *
     * @param array $data
     * @return Offense
     * @throws Exception
     */
    public static function create(array $data): Offense
    {
        self::int($data, 'damage_type', OffenseException::INCORRECT_DAMAGE_TYPE);
        self::int($data, 'weapon_type', OffenseException::INCORRECT_WEAPON_TYPE);
        self::int($data, 'physical_damage', OffenseException::INCORRECT_PHYSICAL_DAMAGE);
        self::int($data, 'fire_damage', OffenseException::INCORRECT_FIRE_DAMAGE);
        self::int($data, 'water_damage', OffenseException::INCORRECT_WATER_DAMAGE);
        self::int($data, 'air_damage', OffenseException::INCORRECT_AIR_DAMAGE);
        self::int($data, 'earth_damage', OffenseException::INCORRECT_EARTH_DAMAGE);
        self::int($data, 'life_damage', OffenseException::INCORRECT_LIFE_DAMAGE);
        self::int($data, 'death_damage', OffenseException::INCORRECT_DEATH_DAMAGE);
        self::intOrFloat($data, 'attack_speed', OffenseException::INCORRECT_ATTACK_SPEED);
        self::intOrFloat($data, 'cast_speed', OffenseException::INCORRECT_CAST_SPEED);
        self::int($data, 'accuracy', OffenseException::INCORRECT_ACCURACY);
        self::int($data, 'magic_accuracy', OffenseException::INCORRECT_MAGIC_ACCURACY);
        self::int($data, 'block_ignoring', OffenseException::INCORRECT_BLOCK_IGNORING);
        self::int($data, 'critical_chance', OffenseException::INCORRECT_CRITICAL_CHANCE);
        self::int($data, 'critical_multiplier', OffenseException::INCORRECT_CRITICAL_MULTIPLIER);
        self::int($data, 'vampire', OffenseException::INCORRECT_VAMPIRE);

        self::in(
            $data['damage_type'],
            [OffenseInterface::TYPE_ATTACK, OffenseInterface::TYPE_SPELL],
            OffenseException::INCORRECT_DAMAGE_TYPE_VALUE
        );

        return new Offense(
            $data['damage_type'],
            $data['weapon_type'],
            $data['physical_damage'],
            $data['fire_damage'],
            $data['water_damage'],
            $data['air_damage'],
            $data['earth_damage'],
            $data['life_damage'],
            $data['death_damage'],
            $data['attack_speed'],
            $data['cast_speed'],
            $data['accuracy'],
            $data['magic_accuracy'],
            $data['block_ignoring'],
            $data['critical_chance'],
            $data['critical_multiplier'],
            $data['vampire']
        );
    }
}
