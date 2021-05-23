<?php

declare(strict_types=1);

namespace Battle\Unit;

use Battle\Classes\UnitClassFactory;
use Battle\Classes\ClassFactoryException;
use Battle\Result\Chat\Message;

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
     *     'level'        => 3,
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
     * @param Message|null $message
     * @return UnitInterface
     * @throws ClassFactoryException
     * @throws UnitException
     */
    public static function create(array $data, ?Message $message = null): UnitInterface
    {
        $message = $message ?? new Message();

        self::existAndString($data, 'id', UnitException::INCORRECT_ID);
        self::existAndString($data, 'name', UnitException::INCORRECT_NAME);
        self::existAndString($data, 'avatar', UnitException::INCORRECT_AVATAR);
        self::existAndInt($data, 'damage', UnitException::INCORRECT_DAMAGE);
        self::existAndInt($data, 'life', UnitException::INCORRECT_LIFE);
        self::existAndInt($data, 'total_life', UnitException::INCORRECT_TOTAL_LIFE);
        self::existAndInt($data, 'level', UnitException::INCORRECT_LEVEL);
        self::existAndInt($data, 'class', UnitException::INCORRECT_CLASS);
        self::intMinMaxValue($data['damage'], UnitInterface::MIN_DAMAGE, UnitInterface::MAX_DAMAGE, UnitException::INCORRECT_DAMAGE_VALUE . UnitInterface::MIN_DAMAGE . '-' . UnitInterface::MAX_DAMAGE);
        self::intMinMaxValue($data['life'], UnitInterface::MIN_LIFE, UnitInterface::MAX_LIFE, UnitException::INCORRECT_LIFE_VALUE . UnitInterface::MIN_LIFE . '-' . UnitInterface::MAX_LIFE);
        self::intMinMaxValue($data['total_life'], UnitInterface::MIN_TOTAL_LIFE, UnitInterface::MAX_TOTAL_LIFE, UnitException::INCORRECT_TOTAL_LIFE_VALUE . UnitInterface::MIN_TOTAL_LIFE . '-' . UnitInterface::MAX_TOTAL_LIFE);
        self::intMinMaxValue($data['level'], UnitInterface::MIN_LEVEL, UnitInterface::MAX_LEVEL, UnitException::INCORRECT_LEVEL_VALUE . UnitInterface::MIN_LEVEL . '-' . UnitInterface::MAX_LEVEL);
        self::stringMinMaxLength($data['name'], UnitInterface::MIN_NAME_LENGTH, UnitInterface::MAX_NAME_LENGTH, UnitException::INCORRECT_NAME_VALUE . UnitInterface::MIN_NAME_LENGTH . '-' . UnitInterface::MAX_NAME_LENGTH);
        self::stringMinMaxLength($data['id'], UnitInterface::MIN_ID_LENGTH, UnitInterface::MAX_ID_LENGTH, UnitException::INCORRECT_ID_VALUE . UnitInterface::MIN_ID_LENGTH . '-' . UnitInterface::MAX_ID_LENGTH);

        if (!array_key_exists('attack_speed', $data) || (!is_float($data['attack_speed']) && !is_int($data['attack_speed']))) {
            throw new UnitException(UnitException::INCORRECT_ATTACK_SPEED);
        }

        if ($data['attack_speed'] < UnitInterface::MIN_ATTACK_SPEED || $data['attack_speed'] > UnitInterface::MAX_ATTACK_SPEED) {
            throw new UnitException(
                UnitException::INCORRECT_ATTACK_SPEED_VALUE . UnitInterface::MIN_ATTACK_SPEED . '-' . UnitInterface::MAX_ATTACK_SPEED
            );
        }

        if ($data['life'] > $data['total_life']) {
            throw new UnitException(UnitException::LIFE_MORE_TOTAL_LIFE);
        }

        if (!array_key_exists('melee', $data) || !is_bool($data['melee'])) {
            throw new UnitException(UnitException::INCORRECT_MELEE);
        }

        return new Unit(
            $data['id'],
            htmlspecialchars($data['name']),
            $data['level'],
            $data['avatar'],
            $data['damage'],
            $data['attack_speed'],
            $data['life'],
            $data['total_life'],
            $data['melee'],
            UnitClassFactory::create($data['class'], $message),
            $message
        );
    }

    /**
     * @param array $data
     * @param string $filed
     * @param string $error
     * @throws UnitException
     */
    private static function existAndString(array $data, string $filed, string $error): void
    {
        if (!array_key_exists($filed, $data) || !is_string($data[$filed])) {
            throw new UnitException($error);
        }
    }

    /**
     * @param array $data
     * @param string $filed
     * @param string $error
     * @throws UnitException
     */
    private static function existAndInt(array $data, string $filed, string $error): void
    {
        if (!array_key_exists($filed, $data) || !is_int($data[$filed])) {
            throw new UnitException($error);
        }
    }

    /**
     * @param int $value
     * @param int $min
     * @param int $max
     * @param string $error
     * @throws UnitException
     */
    private static function intMinMaxValue(int $value, int $min, int $max, string $error): void
    {
        if ($value < $min || $value > $max) {
            throw new UnitException($error);
        }
    }

    /**
     * @param string $string
     * @param int $minLength
     * @param int $maxLength
     * @param $error
     * @throws UnitException
     */
    public static function stringMinMaxLength(string $string, int $minLength, int $maxLength, $error): void
    {
        $length = mb_strlen($string);

        if ($length < $minLength || $length > $maxLength) {
            throw new UnitException($error);
        }
    }
}
