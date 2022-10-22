<?php

declare(strict_types=1);

namespace Battle\Unit\Offense;

use Battle\Traits\ValidationTrait;
use Battle\Unit\UnitException;
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
        self::int($data, 'block_ignore', OffenseException::INCORRECT_BLOCK_IGNORE);
        self::int($data, 'critical_chance', OffenseException::INCORRECT_CRITICAL_CHANCE);
        self::int($data, 'critical_multiplier', OffenseException::INCORRECT_CRITICAL_MULTIPLIER);
        self::int($data, 'vampire', OffenseException::INCORRECT_VAMPIRE);

        self::in(
            $data['damage_type'],
            [OffenseInterface::TYPE_ATTACK, OffenseInterface::TYPE_SPELL],
            OffenseException::INCORRECT_DAMAGE_TYPE_VALUE
        );

        // TODO Проверка на min-max значение проводится и в самом Offense, здесь её можно убрать

        self::intMinMaxValue(
            $data['physical_damage'],
            OffenseInterface::MIN_DAMAGE,
            OffenseInterface::MAX_DAMAGE,
            OffenseException::INCORRECT_PHYSICAL_DAMAGE_VALUE . OffenseInterface::MIN_DAMAGE . '-' . OffenseInterface::MAX_DAMAGE
        );

        self::intMinMaxValue(
            $data['fire_damage'],
            OffenseInterface::MIN_DAMAGE,
            OffenseInterface::MAX_DAMAGE,
            OffenseException::INCORRECT_FIRE_DAMAGE_VALUE . OffenseInterface::MIN_DAMAGE . '-' . OffenseInterface::MAX_DAMAGE
        );

        self::intMinMaxValue(
            $data['water_damage'],
            OffenseInterface::MIN_DAMAGE,
            OffenseInterface::MAX_DAMAGE,
            OffenseException::INCORRECT_WATER_DAMAGE_VALUE . OffenseInterface::MIN_DAMAGE . '-' . OffenseInterface::MAX_DAMAGE
        );

        self::intMinMaxValue(
            $data['air_damage'],
            OffenseInterface::MIN_DAMAGE,
            OffenseInterface::MAX_DAMAGE,
            OffenseException::INCORRECT_AIR_DAMAGE_VALUE . OffenseInterface::MIN_DAMAGE . '-' . OffenseInterface::MAX_DAMAGE
        );

        self::intMinMaxValue(
            $data['earth_damage'],
            OffenseInterface::MIN_DAMAGE,
            OffenseInterface::MAX_DAMAGE,
            OffenseException::INCORRECT_EARTH_DAMAGE_VALUE . OffenseInterface::MIN_DAMAGE . '-' . OffenseInterface::MAX_DAMAGE
        );

        self::intMinMaxValue(
            $data['life_damage'],
            OffenseInterface::MIN_DAMAGE,
            OffenseInterface::MAX_DAMAGE,
            OffenseException::INCORRECT_LIFE_DAMAGE_VALUE . OffenseInterface::MIN_DAMAGE . '-' . OffenseInterface::MAX_DAMAGE
        );

        self::intMinMaxValue(
            $data['death_damage'],
            OffenseInterface::MIN_DAMAGE,
            OffenseInterface::MAX_DAMAGE,
            OffenseException::INCORRECT_DEATH_DAMAGE_VALUE . OffenseInterface::MIN_DAMAGE . '-' . OffenseInterface::MAX_DAMAGE
        );

        // TODO Вынести в отдельный метод floatMinMaxValue

        if ($data['attack_speed'] < OffenseInterface::MIN_ATTACK_SPEED || $data['attack_speed'] > OffenseInterface::MAX_ATTACK_SPEED) {
            throw new UnitException(
                OffenseException::INCORRECT_ATTACK_SPEED_VALUE . OffenseInterface::MIN_ATTACK_SPEED . '-' . OffenseInterface::MAX_ATTACK_SPEED
            );
        }

        if ($data['cast_speed'] < OffenseInterface::MIN_CAST_SPEED || $data['cast_speed'] > OffenseInterface::MAX_CAST_SPEED) {
            throw new UnitException(
                OffenseException::INCORRECT_CAST_SPEED_VALUE . OffenseInterface::MIN_CAST_SPEED . '-' . OffenseInterface::MAX_CAST_SPEED
            );
        }

        self::intMinMaxValue(
            $data['accuracy'],
            OffenseInterface::MIN_ACCURACY,
            OffenseInterface::MAX_ACCURACY,
            OffenseException::INCORRECT_ACCURACY_VALUE . OffenseInterface::MIN_ACCURACY . '-' . OffenseInterface::MAX_ACCURACY
        );

        self::intMinMaxValue(
            $data['magic_accuracy'],
            OffenseInterface::MIN_MAGIC_ACCURACY,
            OffenseInterface::MAX_MAGIC_ACCURACY,
            OffenseException::INCORRECT_MAGIC_ACCURACY_VALUE . OffenseInterface::MIN_MAGIC_ACCURACY . '-' . OffenseInterface::MAX_MAGIC_ACCURACY
        );

        self::intMinMaxValue(
            $data['block_ignore'],
            OffenseInterface::MIN_BLOCK_IGNORE,
            OffenseInterface::MAX_BLOCK_IGNORE,
            OffenseException::INCORRECT_BLOCK_IGNORE_VALUE . OffenseInterface::MIN_BLOCK_IGNORE . '-' . OffenseInterface::MAX_BLOCK_IGNORE
        );

        self::intMinMaxValue(
            $data['critical_chance'],
            OffenseInterface::MIN_CRITICAL_CHANCE,
            OffenseInterface::MAX_CRITICAL_CHANCE,
            OffenseException::INCORRECT_CRITICAL_CHANCE_VALUE . OffenseInterface::MIN_CRITICAL_CHANCE . '-' . OffenseInterface::MAX_CRITICAL_CHANCE
        );

        self::intMinMaxValue(
            $data['critical_multiplier'],
            OffenseInterface::MIN_CRITICAL_MULTIPLIER,
            OffenseInterface::MAX_CRITICAL_MULTIPLIER,
            OffenseException::INCORRECT_CRITICAL_MULTIPLIER_VALUE . OffenseInterface::MIN_CRITICAL_MULTIPLIER . '-' . OffenseInterface::MAX_CRITICAL_MULTIPLIER
        );

        self::intMinMaxValue(
            $data['vampire'],
            OffenseInterface::MIN_VAMPIRE,
            OffenseInterface::MAX_VAMPIRE,
            OffenseException::INCORRECT_VAMPIRE_VALUE . OffenseInterface::MIN_VAMPIRE . '-' . OffenseInterface::MAX_VAMPIRE
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
            $data['block_ignore'],
            $data['critical_chance'],
            $data['critical_multiplier'],
            $data['vampire']
        );
    }
}
