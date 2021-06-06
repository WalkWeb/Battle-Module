<?php

declare(strict_types=1);

namespace Battle\Unit;

use Battle\Classes\ClassFactoryException;
use Battle\Classes\UnitClassFactory;
use Battle\Classes\UnitClassInterface;
use Battle\Result\Chat\Message;
use Battle\Traits\Validation;
use Battle\Unit\Race\RaceFactory;
use Exception;

class UnitFactory
{
    use Validation;

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
     *     'race'         => 1,
     * ]
     *
     * @param array $data
     * @param Message|null $message
     * @return UnitInterface
     * @throws Exception
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
        self::existAndInt($data, 'race', UnitException::INCORRECT_RACE);
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
            RaceFactory::create($data['race']),
            $message,
            self::getClass($data, $message)
        );
    }

    /**
     * @param array $data
     * @param Message $message
     * @return UnitClassInterface|null
     * @throws UnitException
     * @throws ClassFactoryException
     */
    private static function getClass(array $data, Message $message): ?UnitClassInterface
    {
        if (!array_key_exists('class', $data)) {
            return null;
        }

        if (is_null($data['class'])) {
            return null;
        }

        if (!is_int($data['class'])) {
            throw new UnitException(UnitException::INCORRECT_CLASS);
        }

        return UnitClassFactory::create($data['class'], $message);
    }
}
