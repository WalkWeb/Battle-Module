<?php

declare(strict_types=1);

namespace Tests\Battle\Factory;

use Battle\Classes\UnitClassFactory;
use Battle\Container\Container;
use Battle\Container\ContainerInterface;
use Battle\Unit\Race\RaceFactory;
use Battle\Unit\UnitInterface;
use Battle\Unit\Unit;
use Exception;

class UnitFactory
{
    private static $units = [
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
            UnitClassFactory::create(self::$units[$template]['class'])
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
