<?php

declare(strict_types=1);

namespace Battle\Unit;

use Battle\Classes\ClassFactory;
use Exception;

class UnitFactory
{
    /**
     * Создает юнита на основе массива данных по юниту. Это может быть как json сконвертированный в массив, так и массив
     * данных из базы
     *
     * Ожидаемые параметры в формате:
     *
     * [
     *     'name'         => 'Skeleton',
     *     'damage'       => 15,
     *     'attack_speed' => 1.2,
     *     'life'         => 80,
     *     'melee'        => true,
     *     'class'        => 1,
     * ]
     *
     * @param array $data
     * @return UnitInterface
     * @throws Exception
     */
    public static function create(array $data): UnitInterface
    {
        if (!array_key_exists('name', $data) || !is_string($data['name'])) {
            throw new UnitException(UnitException::INCORRECT_NAME);
        }

        if (!array_key_exists('damage', $data) || !is_int($data['damage'])) {
            throw new UnitException(UnitException::INCORRECT_DAMAGE);
        }

        // todo normalize attack speed - 1.0 value convert to 1 json value. Need change 1 => 1.0

        if (!array_key_exists('attack_speed', $data) || !is_float($data['attack_speed'])) {
            throw new UnitException(UnitException::INCORRECT_ATTACK_SPEED);
        }

        if (!array_key_exists('life', $data) || !is_int($data['life'])) {
            throw new UnitException(UnitException::INCORRECT_LIFE);
        }

        if (!array_key_exists('melee', $data) || !is_bool($data['melee'])) {
            throw new UnitException(UnitException::INCORRECT_MELEE);
        }

        if (!array_key_exists('class', $data) || !is_int($data['class'])) {
            throw new UnitException(UnitException::INCORRECT_CLASS);
        }

        return new Unit(
            $data['name'],
            $data['damage'],
            $data['attack_speed'],
            $data['life'],
            $data['melee'],
            ClassFactory::create($data['class'])
        );
    }
}
