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
        self::int($data, 'damage', OffenseException::INCORRECT_DAMAGE);
        self::existAndInt($data, 'accuracy', OffenseException::INCORRECT_ACCURACY);
        self::existAndInt($data, 'block_ignore', OffenseException::INCORRECT_BLOCK_IGNORE);

        self::intMinMaxValue(
            $data['damage'],
            OffenseInterface::MIN_DAMAGE,
            OffenseInterface::MAX_DAMAGE,
            OffenseException::INCORRECT_DAMAGE_VALUE . OffenseInterface::MIN_DAMAGE . '-' . OffenseInterface::MAX_DAMAGE
        );

        self::intMinMaxValue(
            $data['accuracy'],
            OffenseInterface::MIN_ACCURACY,
            OffenseInterface::MAX_ACCURACY,
            OffenseException::INCORRECT_ACCURACY_VALUE . OffenseInterface::MIN_ACCURACY . '-' . OffenseInterface::MAX_ACCURACY
        );

        self::intMinMaxValue(
            $data['block_ignore'],
            OffenseInterface::MIN_BLOCK_IGNORE,
            OffenseInterface::MAX_BLOCK_IGNORE,
            OffenseException::INCORRECT_BLOCK_IGNORE_VALUE . OffenseInterface::MIN_BLOCK_IGNORE . '-' . OffenseInterface::MAX_BLOCK_IGNORE
        );

        if (!array_key_exists('attack_speed', $data) || (!is_float($data['attack_speed']) && !is_int($data['attack_speed']))) {
            throw new OffenseException(OffenseException::INCORRECT_ATTACK_SPEED);
        }

        if ($data['attack_speed'] < OffenseInterface::MIN_ATTACK_SPEED || $data['attack_speed'] > OffenseInterface::MAX_ATTACK_SPEED) {
            throw new UnitException(
                OffenseException::INCORRECT_ATTACK_SPEED_VALUE . OffenseInterface::MIN_ATTACK_SPEED . '-' . OffenseInterface::MAX_ATTACK_SPEED
            );
        }

        return new Offense($data['damage'], $data['attack_speed'], $data['accuracy'], $data['block_ignore']);
    }
}
