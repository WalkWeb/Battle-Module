<?php

declare(strict_types=1);

namespace Battle\Unit;

use Battle\Unit\Classes\UnitClassInterface;
use Battle\Container\Container;
use Battle\Container\ContainerInterface;
use Battle\Traits\ValidationTrait;
use Battle\Unit\Defense\DefenseFactory;
use Battle\Unit\Offense\OffenseFactory;
use Battle\Unit\Race\RaceInterface;
use Exception;

// TODO Уйти от статики и добавить фабрику в контейнер

class UnitFactory
{
    use ValidationTrait;

    /**
     * Создает юнита на основе массива данных по юниту. Это может быть как json сконвертированный в массив, так и массив
     * данных из базы
     *
     * Ожидаемые параметры в формате:
     *
     *     [
     *         'id'         => '60f3c032-46a6-454d-ae3a-d066f150f6ef',
     *         'name'       => 'Titan',
     *         'level'      => 3,
     *         'avatar'     => '/images/avas/orcs/orc001.jpg',
     *         'life'       => 185,
     *         'total_life' => 185,
     *         'mana'       => 50,
     *         'total_mana' => 50,
     *         'melee'      => true,
     *         'command'    => 1,
     *         'class'      => 5,
     *         'race'       => 3,
     *         'offense'    => [
     *             'type_damage'     => 1,
     *             'damage'          => 35,
     *             'physical_damage' => 0,
     *             'attack_speed'    => 1.2,
     *             'accuracy'        => 176,
     *             'magic_accuracy'  => 413,
     *             'block_ignore'    => 0,
     *         ],
     *         'defense'    => [
     *             'defense'        => 134,
     *             'magic_defense'  => 211,
     *             'block'          => 0,
     *             'magic_block'    => 0,
     *             'mental_barrier' => 0,
     *         ],
     *     ]
     *
     * @param array $data
     * @param ContainerInterface|null $container
     * @return UnitInterface
     * @throws Exception
     */
    public static function create(array $data, ?ContainerInterface $container = null): UnitInterface
    {
        $container = $container ?? new Container();

        self::string($data, 'id', UnitException::INCORRECT_ID);
        self::string($data, 'name', UnitException::INCORRECT_NAME);
        self::string($data, 'avatar', UnitException::INCORRECT_AVATAR);
        self::int($data, 'life', UnitException::INCORRECT_LIFE);
        self::int($data, 'total_life', UnitException::INCORRECT_TOTAL_LIFE);
        self::int($data, 'mana', UnitException::INCORRECT_MANA);
        self::int($data, 'total_mana', UnitException::INCORRECT_TOTAL_MANA);
        self::bool($data, 'melee', UnitException::INCORRECT_MELEE);
        self::int($data, 'level', UnitException::INCORRECT_LEVEL);
        self::int($data, 'race', UnitException::INCORRECT_RACE);
        self::int($data, 'command', UnitException::INCORRECT_COMMAND);
        self::intMinMaxValue($data['life'], UnitInterface::MIN_LIFE, UnitInterface::MAX_LIFE, UnitException::INCORRECT_LIFE_VALUE . UnitInterface::MIN_LIFE . '-' . UnitInterface::MAX_LIFE);
        self::intMinMaxValue($data['total_life'], UnitInterface::MIN_TOTAL_LIFE, UnitInterface::MAX_TOTAL_LIFE, UnitException::INCORRECT_TOTAL_LIFE_VALUE . UnitInterface::MIN_TOTAL_LIFE . '-' . UnitInterface::MAX_TOTAL_LIFE);
        self::intMinMaxValue($data['mana'], UnitInterface::MIN_MANA, UnitInterface::MAX_MANA, UnitException::INCORRECT_MANA_VALUE . UnitInterface::MIN_MANA . '-' . UnitInterface::MAX_MANA);
        self::intMinMaxValue($data['total_mana'], UnitInterface::MIN_TOTAL_MANA, UnitInterface::MAX_TOTAL_MANA, UnitException::INCORRECT_TOTAL_MANA_VALUE . UnitInterface::MIN_TOTAL_MANA . '-' . UnitInterface::MAX_TOTAL_MANA);
        self::intMinMaxValue($data['level'], UnitInterface::MIN_LEVEL, UnitInterface::MAX_LEVEL, UnitException::INCORRECT_LEVEL_VALUE . UnitInterface::MIN_LEVEL . '-' . UnitInterface::MAX_LEVEL);
        self::stringMinMaxLength($data['name'], UnitInterface::MIN_NAME_LENGTH, UnitInterface::MAX_NAME_LENGTH, UnitException::INCORRECT_NAME_VALUE . UnitInterface::MIN_NAME_LENGTH . '-' . UnitInterface::MAX_NAME_LENGTH);
        self::stringMinMaxLength($data['id'], UnitInterface::MIN_ID_LENGTH, UnitInterface::MAX_ID_LENGTH, UnitException::INCORRECT_ID_VALUE . UnitInterface::MIN_ID_LENGTH . '-' . UnitInterface::MAX_ID_LENGTH);

        self::array($data, 'offense', UnitException::INCORRECT_OFFENSE);
        self::array($data, 'defense', UnitException::INCORRECT_DEFENSE);

        if ($data['life'] > $data['total_life']) {
            throw new UnitException(UnitException::LIFE_MORE_TOTAL_LIFE);
        }

        if ($data['mana'] > $data['total_mana']) {
            throw new UnitException(UnitException::MANA_MORE_TOTAL_MANA);
        }

        return new Unit(
            $data['id'],
            htmlspecialchars($data['name']),
            $data['level'],
            $data['avatar'],
            $data['life'],
            $data['total_life'],
            $data['mana'],
            $data['total_mana'],
            $data['melee'],
            $data['command'],
            OffenseFactory::create($data['offense']),
            DefenseFactory::create($data['defense']),
            self::getRace($data['race'], $container),
            $container,
            self::getClass($data, $container)
        );
    }

    /**
     * @param array $data
     * @param ContainerInterface $container
     * @return UnitClassInterface|null
     * @throws Exception
     */
    private static function getClass(array $data, ContainerInterface $container): ?UnitClassInterface
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

        return $container->getUnitClassFactory()->create(
            $container->getClassDataProvider()->get($data['class'])
        );
    }

    /**
     * @param int $raceId
     * @param ContainerInterface $container
     * @return RaceInterface
     * @throws Exception
     */
    private static function getRace(int $raceId, ContainerInterface $container): RaceInterface
    {
        return $container->getRaceFactory()->create(
            $container->getRaceDataProvider()->get($raceId)
        );
    }
}
