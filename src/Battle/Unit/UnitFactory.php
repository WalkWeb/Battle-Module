<?php

declare(strict_types=1);

namespace Battle\Unit;

use Battle\Classes\UnitClassFactory;
use Battle\Classes\ClassFactoryException;

class UnitFactory
{
    /**
     * Создает юнита на основе массива данных по юниту. Это может быть как json сконвертированный в массив, так и массив
     * данных из базы
     *
     * Ожидаемые параметры в формате:
     *
     * [
     *     'id'           => 'a2763c19-7ec5-48f3-9242-2ea6c6d80c56',
     *     'name'         => 'Skeleton',
     *     'avatar'       => '/images/avas/monsters/003.png',
     *     'damage'       => 15,
     *     'attack_speed' => 1.2,
     *     'life'         => 80,
     *     'total_life'   => 80,
     *     'melee'        => true,
     *     'class'        => 1,
     * ]
     *
     * @param array $data
     * @return UnitInterface
     * @throws UnitException
     * @throws ClassFactoryException
     */
    public static function create(array $data): UnitInterface
    {
        // todo проверка на отрицательное здоровье
        // todo проверка на слишком большое количество урона или здоровья
        // todo проверка на слишком длинное имя
        // todo проверка на корректную скорость атаки

        if (!array_key_exists('id', $data) || !is_string($data['id']) || $data['id'] === '') {
            throw new UnitException(UnitException::INCORRECT_ID);
        }

        if (!array_key_exists('name', $data) || !is_string($data['name'])) {
            throw new UnitException(UnitException::INCORRECT_NAME);
        }

        if (!array_key_exists('avatar', $data) || !is_string($data['avatar'])) {
            throw new UnitException(UnitException::INCORRECT_AVATAR);
        }

        if (!array_key_exists('damage', $data) || !is_int($data['damage'])) {
            throw new UnitException(UnitException::INCORRECT_DAMAGE);
        }

        if ($data['damage'] < UnitInterface::MIN_DAMAGE || $data['damage'] > UnitInterface::MAX_DAMAGE) {
            throw new UnitException(
                UnitException::INCORRECT_DAMAGE_VALUE . UnitInterface::MIN_DAMAGE . '-' . UnitInterface::MAX_DAMAGE
            );
        }

        if (!array_key_exists('attack_speed', $data) || (!is_float($data['attack_speed']) && !is_int($data['attack_speed']))) {
            throw new UnitException(UnitException::INCORRECT_ATTACK_SPEED);
        }

        if (!array_key_exists('life', $data) || !is_int($data['life'])) {
            throw new UnitException(UnitException::INCORRECT_LIFE);
        }

        if (!array_key_exists('total_life', $data) || !is_int($data['total_life'])) {
            throw new UnitException(UnitException::INCORRECT_TOTAL_LIFE);
        }

        if ($data['life'] > $data['total_life']) {
            throw new UnitException(UnitException::LIFE_MORE_TOTAL_LIFE);
        }

        if (!array_key_exists('melee', $data) || !is_bool($data['melee'])) {
            throw new UnitException(UnitException::INCORRECT_MELEE);
        }

        if (!array_key_exists('class', $data) || !is_int($data['class'])) {
            throw new UnitException(UnitException::INCORRECT_CLASS);
        }

        return new Unit(
            $data['id'],
            htmlspecialchars($data['name']),
            $data['avatar'],
            $data['damage'],
            $data['attack_speed'],
            $data['life'],
            $data['total_life'],
            $data['melee'],
            UnitClassFactory::create($data['class'])
        );
    }
}
