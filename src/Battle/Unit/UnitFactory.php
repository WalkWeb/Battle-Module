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
     * [
     *     'id'                           => 'f7e84eab-e4f6-469f-b0e3-f5f965f9fbce',
     *     'name'                         => 'name',
     *     'level'                        => 1,
     *     'avatar'                       => '/images/avas/humans/human001.jpg',
     *     'life'                         => 100,
     *     'total_life'                   => 100,
     *     'mana'                         => 50,
     *     'total_mana'                   => 50,
     *     'melee'                        => true,
     *     'command'                      => 1,
     *     'add_concentration_multiplier' => 0,
     *     'add_rage_multiplier'          => 0,
     *     'class'                        => 1,
     *     'race'                         => 1,
     *     'offense'                      => [
     *         'damage_type'         => 2,
     *         'weapon_type'         => 1,
     *         'physical_damage'     => 20,
     *         'fire_damage'         => 30,
     *         'water_damage'        => 0,
     *         'air_damage'          => 0,
     *         'earth_damage'        => 0,
     *         'life_damage'         => 0,
     *         'death_damage'        => 0,
     *         'attack_speed'        => 0,
     *         'cast_speed'          => 3,
     *         'accuracy'            => 200,
     *         'magic_accuracy'      => 100,
     *         'block_ignoring'      => 0,
     *         'critical_chance'     => 5,
     *         'critical_multiplier' => 200,
     *         'vampirism'           => 0,
     *     ],
     *     'defense'                      => [
     *         'physical_resist'     => 0,
     *         'fire_resist'         => 0,
     *         'water_resist'        => 0,
     *         'air_resist'          => 0,
     *         'earth_resist'        => 0,
     *         'life_resist'         => 0,
     *         'death_resist'        => 0,
     *         'defense'             => 100,
     *         'magic_defense'       => 50,
     *         'block'               => 0,
     *         'magic_block'         => 0,
     *         'mental_barrier'      => 0,
     *         'max_physical_resist' => 75,
     *         'max_fire_resist'     => 75,
     *         'max_water_resist'    => 75,
     *         'max_air_resist'      => 75,
     *         'max_earth_resist'    => 75,
     *         'max_life_resist'     => 75,
     *         'max_death_resist'    => 75,
     *         'global_resist'       => 0,
     *     ],
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
        self::int($data, 'add_concentration_multiplier', UnitException::INCORRECT_ADD_CONC_MULTIPLIER);
        self::int($data, 'add_rage_multiplier', UnitException::INCORRECT_ADD_RAGE_MULTIPLIER);

        self::intMinMaxValue(
            $data['life'],
            UnitInterface::MIN_LIFE,
            UnitInterface::MAX_LIFE,
            UnitException::INCORRECT_LIFE_VALUE . UnitInterface::MIN_LIFE . '-' . UnitInterface::MAX_LIFE
        );

        self::intMinMaxValue(
            $data['total_life'],
            UnitInterface::MIN_TOTAL_LIFE,
            UnitInterface::MAX_TOTAL_LIFE,
            UnitException::INCORRECT_TOTAL_LIFE_VALUE . UnitInterface::MIN_TOTAL_LIFE . '-' . UnitInterface::MAX_TOTAL_LIFE
        );

        self::intMinMaxValue(
            $data['mana'],
            UnitInterface::MIN_MANA,
            UnitInterface::MAX_MANA,
            UnitException::INCORRECT_MANA_VALUE . UnitInterface::MIN_MANA . '-' . UnitInterface::MAX_MANA
        );

        self::intMinMaxValue(
            $data['total_mana'],
            UnitInterface::MIN_TOTAL_MANA,
            UnitInterface::MAX_TOTAL_MANA,
            UnitException::INCORRECT_TOTAL_MANA_VALUE . UnitInterface::MIN_TOTAL_MANA . '-' . UnitInterface::MAX_TOTAL_MANA
        );

        self::intMinMaxValue(
            $data['level'],
            UnitInterface::MIN_LEVEL,
            UnitInterface::MAX_LEVEL,
            UnitException::INCORRECT_LEVEL_VALUE . UnitInterface::MIN_LEVEL . '-' . UnitInterface::MAX_LEVEL
        );

        self::intMinMaxValue(
            $data['add_concentration_multiplier'],
            UnitInterface::MIN_RESOURCE_MULTIPLIER,
            UnitInterface::MAX_RESOURCE_MULTIPLIER,
            UnitException::INCORRECT_ADD_CONC_MULTIPLIER_VALUE . UnitInterface::MIN_RESOURCE_MULTIPLIER . ' - ' . UnitInterface::MAX_RESOURCE_MULTIPLIER
        );

        self::intMinMaxValue(
            $data['add_rage_multiplier'],
            UnitInterface::MIN_RESOURCE_MULTIPLIER,
            UnitInterface::MAX_RESOURCE_MULTIPLIER,
            UnitException::INCORRECT_ADD_RAGE_MULTIPLIER_VALUE . UnitInterface::MIN_RESOURCE_MULTIPLIER . ' - ' . UnitInterface::MAX_RESOURCE_MULTIPLIER
        );

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
            $data['add_concentration_multiplier'],
            $data['add_rage_multiplier'],
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
