<?php

declare(strict_types=1);

namespace Battle\Unit\Offense;

use Battle\Traits\ValidationTrait;
use Battle\Unit\UnitException;
use Battle\Unit\UnitInterface;
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
    public function create(array $data): Offense
    {
        self::int($data, 'damage', OffenseException::INCORRECT_DAMAGE);
        self::existAndInt($data, 'accuracy', OffenseException::INCORRECT_ACCURACY);
        self::existAndInt($data, 'block_ignore', OffenseException::INCORRECT_BLOCK_IGNORE);

        self::intMinMaxValue(
            $data['damage'],
            UnitInterface::MIN_DAMAGE,
            UnitInterface::MAX_DAMAGE,
            OffenseException::INCORRECT_DAMAGE_VALUE . UnitInterface::MIN_DAMAGE . '-' . UnitInterface::MAX_DAMAGE
        );

        self::intMinValue(
            $data['accuracy'],
            UnitInterface::MIN_ACCURACY,
            OffenseException::INCORRECT_ACCURACY_VALUE . UnitInterface::MIN_ACCURACY
        );

        self::intMinMaxValue(
            $data['block_ignore'],
            UnitInterface::MIN_BLOCK_IGNORE,
            UnitInterface::MAX_BLOCK_IGNORE,
            OffenseException::INCORRECT_BLOCK_IGNORE_VALUE . UnitInterface::MIN_BLOCK_IGNORE . '-' . UnitInterface::MAX_BLOCK_IGNORE
        );

        if (!array_key_exists('attack_speed', $data) || (!is_float($data['attack_speed']) && !is_int($data['attack_speed']))) {
            throw new OffenseException(OffenseException::INCORRECT_ATTACK_SPEED);
        }

        if ($data['attack_speed'] < UnitInterface::MIN_ATTACK_SPEED || $data['attack_speed'] > UnitInterface::MAX_ATTACK_SPEED) {
            throw new UnitException(
                OffenseException::INCORRECT_ATTACK_SPEED_VALUE . UnitInterface::MIN_ATTACK_SPEED . '-' . UnitInterface::MAX_ATTACK_SPEED
            );
        }

        return new Offense($data['damage'], $data['attack_speed'], $data['accuracy'], $data['block_ignore']);
    }
}
