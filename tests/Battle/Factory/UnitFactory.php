<?php

declare(strict_types=1);

namespace Tests\Battle\Factory;

use Battle\Classes\UnitClassFactory;
use Battle\Classes\ClassFactoryException;
use Battle\Unit\UnitInterface;
use Battle\Unit\Unit;

class UnitFactory
{
    private static $units = [
        1  => [
            'id'           => 'f7e84eab-e4f6-469f-b0e3-f5f965f9fbce',
            'name'         => 'unit_1',
            'avatar'       => '/images/avas/humans/human001.jpg',
            'damage'       => 20,
            'attack_speed' => 1.00,
            'life'         => 100,
            'melee'        => true,
            'class'        => 1,
        ],
        2  => [
            'id'           => '1aab367d-37e8-4544-9915-cb3d7779308b',
            'name'         => 'unit_2',
            'avatar'       => '/images/avas/humans/human002.jpg',
            'damage'       => 30,
            'attack_speed' => 1.00,
            'life'         => 150,
            'melee'        => true,
            'class'        => 1,
        ],
        3  => [
            'id'           => '72df87f5-b3a7-4574-9526-45a20aa77119',
            'name'         => 'unit_3',
            'avatar'       => '/images/avas/humans/human003.jpg',
            'damage'       => 15,
            'attack_speed' => 1.00,
            'life'         => 120,
            'melee'        => true,
            'class'        => 1,
        ],
        4  => [
            'id'           => 'c310ce86-7bb2-44b0-b634-ea0d28fb1180',
            'name'         => 'unit_4',
            'avatar'       => '/images/avas/humans/human004.jpg',
            'damage'       => 15,
            'attack_speed' => 1.00,
            'life'         => 20,
            'melee'        => true,
            'class'        => 1,
        ],
        5  => [
            'id'           => '46d969c1-463b-42b1-a2e0-2c64a8c34ae1',
            'name'         => 'unit_5',
            'avatar'       => '/images/avas/monsters/003.png',
            'damage'       => 15,
            'attack_speed' => 1.00,
            'life'         => 80,
            'melee'        => false,
            'class'        => 2,
        ],
        6 => [
            'id'           => '1e813812-9a21-4e18-b494-8d552bac0cf4',
            'name'         => 'unit_6',
            'avatar'       => '/images/avas/monsters/003.png',
            'damage'       => 12,
            'attack_speed' => 1.1,
            'life'         => 50,
            'melee'        => false,
            'class'        => 2,
        ],
        7  => [
            'id'           => '46d969c1-463b-42b1-a2e0-2c62a8c34ae3',
            'name'         => 'unit_7',
            'avatar'       => '/images/avas/monsters/003.png',
            'damage'       => 15,
            'attack_speed' => 1.00,
            'life'         => 80,
            'melee'        => false,
            'class'        => 4,
        ],
        8  => [
            'id'           => 'f7e84eab-e4f6-463f-b0e3-f2f965f4fbce',
            'name'         => 'unit_8',
            'avatar'       => '/images/avas/humans/human001.jpg',
            'damage'       => 20,
            'attack_speed' => 1.00,
            'life'         => 100,
            'melee'        => true,
            'class'        => 3,
        ],
        10 => [
            'id'           => '92e7b39c-dbfc-4493-b563-50314c524c0c',
            'name'         => 'dead_unit',
            'avatar'       => '/images/avas/monsters/005.png',
            'damage'       => 35,
            'attack_speed' => 1.00,
            'life'         => 0,
            'melee'        => true,
            'class'        => 1,
        ],
    ];

    /**
     * @param int $template
     * @return UnitInterface
     * @throws UnitFactoryException
     * @throws ClassFactoryException
     */
    public static function createByTemplate(int $template): UnitInterface
    {
        if (empty(self::$units[$template])) {
            throw new UnitFactoryException(UnitFactoryException::NO_TEMPLATE);
        }

        return new Unit(
            self::$units[$template]['id'],
            self::$units[$template]['name'],
            self::$units[$template]['avatar'],
            self::$units[$template]['damage'],
            self::$units[$template]['attack_speed'],
            self::$units[$template]['life'],
            self::$units[$template]['melee'],
            UnitClassFactory::create(self::$units[$template]['class'])
        );
    }

    /**
     * @return Unit
     * @throws ClassFactoryException
     * @throws UnitFactoryException
     */
    public static function createDeadUnit(): UnitInterface
    {
        return self::createByTemplate(10);
    }
}
