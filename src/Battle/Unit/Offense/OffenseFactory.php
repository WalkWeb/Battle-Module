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
        self::intOrFloat($data, 'attack_speed', OffenseException::INCORRECT_ATTACK_SPEED);
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

        self::intMinMaxValue(
            $data['physical_damage'],
            OffenseInterface::MIN_DAMAGE,
            OffenseInterface::MAX_DAMAGE,
            OffenseException::INCORRECT_PHYSICAL_DAMAGE_VALUE . OffenseInterface::MIN_DAMAGE . '-' . OffenseInterface::MAX_DAMAGE
        );

        if ($data['attack_speed'] < OffenseInterface::MIN_ATTACK_SPEED || $data['attack_speed'] > OffenseInterface::MAX_ATTACK_SPEED) {
            throw new UnitException(
                OffenseException::INCORRECT_ATTACK_SPEED_VALUE . OffenseInterface::MIN_ATTACK_SPEED . '-' . OffenseInterface::MAX_ATTACK_SPEED
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
            $data['attack_speed'],
            $data['accuracy'],
            $data['magic_accuracy'],
            $data['block_ignore'],
            $data['critical_chance'],
            $data['critical_multiplier'],
            $data['vampire']
        );
    }
}
