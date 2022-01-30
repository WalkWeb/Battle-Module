<?php

declare(strict_types=1);

namespace Tests\Battle\Factory;

use Battle\Unit\Classes\UnitClassFactory;
use Battle\Container\Container;
use Battle\Container\ContainerInterface;
use Battle\Unit\Race\RaceFactory;
use Battle\Unit\UnitInterface;
use Battle\Unit\Unit;
use Exception;

class UnitFactory
{
    private static $units = [
        // Warrior
        1  => [
            'id'           => 'f7e84eab-e4f6-469f-b0e3-f5f965f9fbce',
            'name'         => 'unit_1',
            'level'        => 1,
            'avatar'       => '/images/avas/humans/human001.jpg',
            'damage'       => 20,
            'attack_speed' => 1.00,
            'life'         => 100,
            'total_life'   => 100,
            'melee'        => true,
            'command'      => 1,
            'class'        => 1,
            'race'         => 1,
        ],
        2  => [
            'id'           => '1aab367d-37e8-4544-9915-cb3d7779308b',
            'name'         => 'unit_2',
            'level'        => 1,
            'avatar'       => '/images/avas/humans/human002.jpg',
            'damage'       => 30,
            'attack_speed' => 1.00,
            'life'         => 250,
            'total_life'   => 250,
            'command'      => 1,
            'melee'        => true,
            'class'        => 1,
            'race'         => 1,
        ],
        3  => [
            'id'           => '72df87f5-b3a7-4574-9526-45a20aa77119',
            'name'         => 'unit_3',
            'level'        => 1,
            'avatar'       => '/images/avas/humans/human003.jpg',
            'damage'       => 15,
            'attack_speed' => 1.00,
            'life'         => 120,
            'total_life'   => 120,
            'melee'        => true,
            'command'      => 1,
            'class'        => 1,
            'race'         => 1,
        ],
        4  => [
            'id'           => 'c310ce86-7bb2-44b0-b634-ea0d28fb1180',
            'name'         => 'unit_4',
            'level'        => 1,
            'avatar'       => '/images/avas/humans/human004.jpg',
            'damage'       => 15,
            'attack_speed' => 1.00,
            'life'         => 20,
            'total_life'   => 20,
            'melee'        => true,
            'command'      => 1,
            'class'        => 1,
            'race'         => 1,
        ],
        // Priest
        5  => [
            'id'           => '46d969c1-463b-42b1-a2e0-2c64a8c34ae1',
            'name'         => 'unit_5',
            'level'        => 1,
            'avatar'       => '/images/avas/monsters/003.png',
            'damage'       => 15,
            'attack_speed' => 1.00,
            'life'         => 80,
            'total_life'   => 80,
            'melee'        => false,
            'command'      => 1,
            'class'        => 2,
            'race'         => 1,
        ],
        6 => [
            'id'           => '1e813812-9a21-4e18-b494-8d552bac0cf4',
            'name'         => 'unit_6',
            'level'        => 1,
            'avatar'       => '/images/avas/monsters/003.png',
            'damage'       => 12,
            'attack_speed' => 1.1,
            'life'         => 50,
            'total_life'   => 50,
            'melee'        => false,
            'command'      => 1,
            'class'        => 2,
            'race'         => 1,
        ],
        7  => [
            'id'           => '46d969c1-463b-42b1-a2e0-2c62a8c34ae3',
            'name'         => 'unit_7',
            'level'        => 1,
            'avatar'       => '/images/avas/monsters/003.png',
            'damage'       => 15,
            'attack_speed' => 1.00,
            'life'         => 80,
            'total_life'   => 80,
            'melee'        => false,
            'command'      => 1,
            'class'        => 4,
            'race'         => 1,
        ],
        8  => [
            'id'           => 'f7e84eab-e4f6-463f-b0e3-f2f965f4fbce',
            'name'         => 'unit_8',
            'level'        => 1,
            'avatar'       => '/images/avas/humans/human001.jpg',
            'damage'       => 20,
            'attack_speed' => 1.00,
            'life'         => 100,
            'total_life'   => 100,
            'melee'        => true,
            'command'      => 1,
            'class'        => 3,
            'race'         => 1,
        ],
        // Слегка раненый юнит
        9 => [
            'id'           => '5e5c15fb-fa29-4bf0-8a0d-f2be9f90ca9d',
            'name'         => 'small_wounded_unit',
            'level'        => 1,
            'avatar'       => '/images/avas/monsters/005.png',
            'damage'       => 35,
            'attack_speed' => 1.00,
            'life'         => 90,
            'total_life'   => 100,
            'melee'        => true,
            'command'      => 1,
            'class'        => 1,
            'race'         => 1,
        ],
        // Мертвый юнит
        10 => [
            'id'           => '92e7b39c-dbfc-4493-b563-50314c524c0c',
            'name'         => 'dead_unit',
            'level'        => 1,
            'avatar'       => '/images/avas/monsters/005.png',
            'damage'       => 35,
            'attack_speed' => 1.00,
            'life'         => 0,
            'total_life'   => 100,
            'melee'        => true,
            'command'      => 1,
            'class'        => 1,
            'race'         => 1,
        ],
        // Сильно раненый юнит
        11 => [
            'id'           => '92e7b39c-dbfc-4493-b563-50314c524c3c',
            'name'         => 'wounded_unit',
            'level'        => 1,
            'avatar'       => '/images/avas/monsters/005.png',
            'damage'       => 35,
            'attack_speed' => 1.00,
            'life'         => 1,
            'total_life'   => 100,
            'melee'        => true,
            'command'      => 1,
            'class'        => 1,
            'race'         => 1,
        ],
        12  => [
            'id'           => '1aab367d-37e8-4544-9915-cb3d7779332b',
            'name'         => 'unit_12',
            'level'        => 1,
            'avatar'       => '/images/avas/humans/human002.jpg',
            'damage'       => 3000,
            'attack_speed' => 1.00,
            'life'         => 150,
            'total_life'   => 150,
            'melee'        => true,
            'command'      => 2,
            'class'        => 1,
            'race'         => 1,
        ],
        13  => [
            'id'           => '1aab367d-37e8-4544-9915-cb3d7779332b',
            'name'         => 'unit_13',
            'level'        => 1,
            'avatar'       => '/images/avas/humans/human002.jpg',
            'damage'       => 3000,
            'attack_speed' => 5, // Не просто большой урон, но и 5 атак за ход
            'life'         => 150,
            'total_life'   => 150,
            'melee'        => true,
            'command'      => 1,
            'class'        => 1,
            'race'         => 1,
        ],
        14  => [
            'id'           => '1aab367d-37e8-4544-9915-cb3d7779323b',
            'name'         => 'unit_14',
            'level'        => 1,
            'avatar'       => '/images/avas/humans/human002.jpg',
            'damage'       => 32,
            'attack_speed' => 0, // нулевая скорость атаки
            'life'         => 150,
            'total_life'   => 150,
            'melee'        => true,
            'command'      => 1,
            'class'        => 1,
            'race'         => 1,
        ],
        // Юнит для подсчета дополнительных атак, с.м. тест testUnitCalculateAttackSpeed
        15 => [
            'id'           => '1aab367d-37e8-4544-9915-cb3d77793237',
            'name'         => 'unit_15',
            'level'        => 1,
            'avatar'       => '/images/avas/humans/human002.jpg',
            'damage'       => 32,
            'attack_speed' => 1.9999,
            'life'         => 150,
            'total_life'   => 150,
            'melee'        => true,
            'command'      => 1,
            'class'        => 1,
            'race'         => 1,
        ],
        // Юнит для подсчета дополнительных атак, с.м. тест testUnitCalculateAttackSpeed
        16 => [
            'id'           => '1aab367d-37e8-4544-9915-cb3d77793239',
            'name'         => 'unit_16',
            'level'        => 1,
            'avatar'       => '/images/avas/humans/human002.jpg',
            'damage'       => 32,
            'attack_speed' => 1.0001,
            'life'         => 150,
            'total_life'   => 150,
            'melee'        => true,
            'command'      => 1,
            'class'        => 1,
            'race'         => 1,
        ],
        17  => [
            'id'           => '57b11b16-c1fc-463a-a2a0-9dff8a7e2d5c',
            'name'         => 'Zombie',
            'level'        => 2,
            'avatar'       => '/images/avas/monsters/006.png',
            'damage'       => 23,
            'attack_speed' => 0.7,
            'life'         => 62,
            'total_life'   => 62,
            'melee'        => true,
            'class'        => null,
            'race'         => 8,
            'command'      => 2,
        ],
        // Аналог юнита из SummonImpAbility
        18  => [
            'id'           => '57b11b16-c1fc-463a-a2a0-9dff8a7e2333',
            'name'         => 'Imp',
            'level'        => 1,
            'avatar'       => '/images/avas/monsters/004.png',
            'damage'       => 10,
            'attack_speed' => 1,
            'life'         => 30,
            'total_life'   => 30,
            'melee'        => true,
            'class'        => null,
            'race'         => 9,
            'command'      => 2,
        ],
        // Аналог юнита из SummonSkeletonMageAbility
        19  => [
            'id'           => '57b11b16-c1fc-463a-a2a0-9dff8a7e2326',
            'name'         => 'Skeleton Mage',
            'level'        => 2,
            'avatar'       => '/images/avas/monsters/008.png',
            'damage'       => 13,
            'attack_speed' => 1.2,
            'life'         => 42,
            'total_life'   => 42,
            'melee'        => false,
            'class'        => null,
            'race'         => 8,
            'command'      => 2,
        ],
        // Аналог юнита из SummonSkeletonAbility
        20  => [
            'id'           => '57b11b16-c1fc-463a-a2a0-9dff8a7e2541',
            'name'         => 'Skeleton',
            'level'        => 1,
            'avatar'       => '/images/avas/monsters/003.png',
            'damage'       => 16,
            'attack_speed' => 1,
            'life'         => 38,
            'total_life'   => 38,
            'melee'        => true,
            'class'        => null,
            'race'         => 8,
            'command'      => 2,
        ],
        // Орк с классом Титан
        21  => [
            'id'           => '60f3c032-46a6-454d-ae3a-d066f150f6ef',
            'name'         => 'Titan',
            'level'        => 3,
            'avatar'       => '/images/avas/orcs/orc001.jpg',
            'damage'       => 27,
            'attack_speed' => 0.8,
            'life'         => 100,
            'total_life'   => 100,
            'melee'        => true,
            'command'      => 1,
            'class'        => 5,
            'race'         => 3,
        ],
        // Гном с классом Алхимик
        22  => [
            'id'           => '2c58854d-e0ad-4d29-86e4-62bbb4b8d3b7',
            'name'         => 'Alchemist',
            'level'        => 3,
            'avatar'       => '/images/avas/dwarfs/dwarf004.jpg',
            'damage'       => 13,
            'attack_speed' => 1.1,
            'life'         => 75,
            'total_life'   => 75,
            'melee'        => false,
            'command'      => 1,
            'class'        => 6,
            'race'         => 4,
        ],
        // Демон с классом Суккуб
        23  => [
            'id'           => '942be229-2272-45d5-94ec-4c20be94a344',
            'name'         => 'Succubus',
            'level'        => 2,
            'avatar'       => '/images/avas/demons/demon010.jpg',
            'damage'       => 11,
            'attack_speed' => 1.2,
            'life'         => 69,
            'total_life'   => 69,
            'melee'        => false,
            'command'      => 1,
            'class'        => 7,
            'race'         => 6,
        ],
        // Мертвый юнит с небольшим количеством здоровья
        24 => [
            'id'           => 'ffd35911-4bec-494a-afe8-f6492d81847f',
            'name'         => 'dead_unit_low_life',
            'level'        => 1,
            'avatar'       => '/images/avas/monsters/005.png',
            'damage'       => 35,
            'attack_speed' => 1.00,
            'life'         => 0,
            'total_life'   => 10,
            'melee'        => true,
            'command'      => 1,
            'class'        => 1,
            'race'         => 1,
        ],
        // Человек с классом Priest
        25 => [
            'id'           => 'e09b2794-5075-4186-a1ed-31e40cd3fda2',
            'name'         => 'Priest',
            'level'        => 4,
            'avatar'       => '/images/avas/humans/human004.jpg',
            'damage'       => 13,
            'attack_speed' => 1.20,
            'life'         => 140,
            'total_life'   => 140,
            'melee'        => false,
            'command'      => 1,
            'class'        => 2,
            'race'         => 1,
        ],
        // Элементаль огня
        26 => [
            'id'           => '0b23abe7-00ee-4ff4-a7c7-5d81f4abd201',
            'name'         => 'Fire Elemental',
            'level'        => 3,
            'avatar'       => '/images/avas/summon/fire-elemental.png',
            'damage'       => 17,
            'attack_speed' => 1.1,
            'life'         => 62,
            'total_life'   => 62,
            'melee'        => true,
            'command'      => 1,
            'class'        => null,
            'race'         => 10,
        ],
        // Босс Warden
        27 => [
            'id'           => 'e53b7edd-a4b5-49c4-81a2-050a1b7ecbea',
            'name'         => 'Warden',
            'level'        => 5,
            'avatar'       => '/images/avas/bosses/011.png',
            'damage'       => 40,
            'attack_speed' => 1,
            'life'         => 300,
            'total_life'   => 300,
            'melee'        => true,
            'command'      => 2,
            'class'        => 50,
            'race'         => 9,
        ],
    ];

    /**
     * @param int $template
     * @param ContainerInterface|null $container
     * @return UnitInterface
     * @throws UnitFactoryException
     * @throws Exception
     */
    public static function createByTemplate(int $template, ?ContainerInterface $container = null): UnitInterface
    {
        if (empty(self::$units[$template])) {
            throw new UnitFactoryException(UnitFactoryException::NO_TEMPLATE);
        }

        $class = isset(self::$units[$template]['class']) ? UnitClassFactory::create(self::$units[$template]['class']) : null;

        return new Unit(
            self::$units[$template]['id'],
            self::$units[$template]['name'],
            self::$units[$template]['level'],
            self::$units[$template]['avatar'],
            self::$units[$template]['damage'],
            self::$units[$template]['attack_speed'],
            self::$units[$template]['life'],
            self::$units[$template]['total_life'],
            self::$units[$template]['melee'],
            self::$units[$template]['command'],
            RaceFactory::create(self::$units[$template]['race']),
            $container ?? new Container(),
            $class
        );
    }

    /**
     * @param int $template
     * @return array
     * @throws UnitFactoryException
     */
    public static function getData(int $template): array
    {
        if (empty(self::$units[$template])) {
            throw new UnitFactoryException(UnitFactoryException::NO_TEMPLATE);
        }

        return self::$units[$template];
    }
}
