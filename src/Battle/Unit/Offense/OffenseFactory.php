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
        self::intOrFloat($data, 'attack_speed', OffenseException::INCORRECT_ATTACK_SPEED);
        self::int($data, 'accuracy', OffenseException::INCORRECT_ACCURACY);
        self::int($data, 'magic_accuracy', OffenseException::INCORRECT_MAGIC_ACCURACY);
        self::int($data, 'block_ignore', OffenseException::INCORRECT_BLOCK_IGNORE);

        self::intMinMaxValue(
            $data['damage'],
            OffenseInterface::MIN_DAMAGE,
            OffenseInterface::MAX_DAMAGE,
            OffenseException::INCORRECT_DAMAGE_VALUE . OffenseInterface::MIN_DAMAGE . '-' . OffenseInterface::MAX_DAMAGE
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

        return new Offense($data['damage'], $data['attack_speed'], $data['accuracy'], $data['magic_accuracy'], $data['block_ignore']);
    }
}
