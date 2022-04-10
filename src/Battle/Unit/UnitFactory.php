<?php

declare(strict_types=1);

namespace Battle\Unit;

use Battle\Result\Chat\ChatInterface;
use Battle\Unit\Classes\ClassFactoryException;
use Battle\Unit\Classes\UnitClassFactory;
use Battle\Unit\Classes\UnitClassInterface;
use Battle\Container\Container;
use Battle\Container\ContainerInterface;
use Battle\Traits\ValidationTrait;
use Battle\Unit\Defense\DefenseInterface;
use Battle\Unit\Offense\OffenseInterface;
use Battle\Unit\Race\RaceFactory;
use Exception;

class UnitFactory
{
    use ValidationTrait;

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
     *     'block'        => 20,
     *     'block_ignore' => 0,
     *     'life'         => 80,
     *     'total_life'   => 80,
     *     'melee'        => true,
     *     'command'      => 1,
     *     'class'        => 1,
     *     'race'         => 1,
     * ]
     *
     * @param array $data
     * @param ContainerInterface|null $container
     * @return UnitInterface
     * @throws Exception
     */
    public static function create(array $data, ?ContainerInterface $container = null): UnitInterface
    {
        $container = $container ?? new Container();

        self::existAndString($data, 'id', UnitException::INCORRECT_ID);
        self::existAndString($data, 'name', UnitException::INCORRECT_NAME);
        self::existAndString($data, 'avatar', UnitException::INCORRECT_AVATAR);
        self::existAndInt($data, 'damage', UnitException::INCORRECT_DAMAGE);
        self::existAndInt($data, 'accuracy', UnitException::INCORRECT_ACCURACY);
        self::existAndInt($data, 'defense', UnitException::INCORRECT_DEFENSE);
        self::existAndInt($data, 'life', UnitException::INCORRECT_LIFE);
        self::existAndInt($data, 'total_life', UnitException::INCORRECT_TOTAL_LIFE);
        self::existAndInt($data, 'level', UnitException::INCORRECT_LEVEL);
        self::existAndInt($data, 'race', UnitException::INCORRECT_RACE);
        self::existAndInt($data, 'command', UnitException::INCORRECT_COMMAND);
        self::existAndInt($data, 'block', UnitException::INCORRECT_BLOCK);
        self::existAndInt($data, 'block_ignore', UnitException::INCORRECT_BLOCK_IGNORE);
        self::intMinMaxValue($data['damage'], OffenseInterface::MIN_DAMAGE, OffenseInterface::MAX_DAMAGE, UnitException::INCORRECT_DAMAGE_VALUE . OffenseInterface::MIN_DAMAGE . '-' . OffenseInterface::MAX_DAMAGE);
        self::intMinValue($data['accuracy'], OffenseInterface::MIN_ACCURACY, UnitException::INCORRECT_ACCURACY_VALUE . OffenseInterface::MIN_ACCURACY);
        self::intMinValue($data['defense'], DefenseInterface::MIN_DEFENSE, UnitException::INCORRECT_DEFENSE_VALUE . DefenseInterface::MIN_DEFENSE);
        self::intMinMaxValue($data['life'], UnitInterface::MIN_LIFE, UnitInterface::MAX_LIFE, UnitException::INCORRECT_LIFE_VALUE . UnitInterface::MIN_LIFE . '-' . UnitInterface::MAX_LIFE);
        self::intMinMaxValue($data['total_life'], UnitInterface::MIN_TOTAL_LIFE, UnitInterface::MAX_TOTAL_LIFE, UnitException::INCORRECT_TOTAL_LIFE_VALUE . UnitInterface::MIN_TOTAL_LIFE . '-' . UnitInterface::MAX_TOTAL_LIFE);
        self::intMinMaxValue($data['level'], UnitInterface::MIN_LEVEL, UnitInterface::MAX_LEVEL, UnitException::INCORRECT_LEVEL_VALUE . UnitInterface::MIN_LEVEL . '-' . UnitInterface::MAX_LEVEL);
        self::intMinMaxValue($data['block'], DefenseInterface::MIN_BLOCK, DefenseInterface::MAX_BLOCK, UnitException::INCORRECT_BLOCK_VALUE . DefenseInterface::MIN_BLOCK . '-' . DefenseInterface::MAX_BLOCK);
        self::intMinMaxValue($data['block_ignore'], OffenseInterface::MIN_BLOCK_IGNORE, OffenseInterface::MAX_BLOCK_IGNORE, UnitException::INCORRECT_BLOCK_IGNORE_VALUE . OffenseInterface::MIN_BLOCK_IGNORE . '-' . OffenseInterface::MAX_BLOCK_IGNORE);
        self::stringMinMaxLength($data['name'], UnitInterface::MIN_NAME_LENGTH, UnitInterface::MAX_NAME_LENGTH, UnitException::INCORRECT_NAME_VALUE . UnitInterface::MIN_NAME_LENGTH . '-' . UnitInterface::MAX_NAME_LENGTH);
        self::stringMinMaxLength($data['id'], UnitInterface::MIN_ID_LENGTH, UnitInterface::MAX_ID_LENGTH, UnitException::INCORRECT_ID_VALUE . UnitInterface::MIN_ID_LENGTH . '-' . UnitInterface::MAX_ID_LENGTH);

        if (!array_key_exists('attack_speed', $data) || (!is_float($data['attack_speed']) && !is_int($data['attack_speed']))) {
            throw new UnitException(UnitException::INCORRECT_ATTACK_SPEED);
        }

        if ($data['attack_speed'] < OffenseInterface::MIN_ATTACK_SPEED || $data['attack_speed'] > OffenseInterface::MAX_ATTACK_SPEED) {
            throw new UnitException(
                UnitException::INCORRECT_ATTACK_SPEED_VALUE . OffenseInterface::MIN_ATTACK_SPEED . '-' . OffenseInterface::MAX_ATTACK_SPEED
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
            $data['accuracy'],
            $data['defense'],
            $data['block'],
            $data['block_ignore'],
            $data['life'],
            $data['total_life'],
            $data['melee'],
            $data['command'],
            RaceFactory::create($data['race']),
            $container,
            self::getClass($data, $container->getChat())
        );
    }

    /**
     * @param array $data
     * @param ChatInterface $chat
     * @return UnitClassInterface|null
     * @throws UnitException
     * @throws ClassFactoryException
     */
    private static function getClass(array $data, ChatInterface $chat): ?UnitClassInterface
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

        return UnitClassFactory::create($data['class'], $chat);
    }
}
