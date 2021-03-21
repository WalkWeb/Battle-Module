<?php

declare(strict_types=1);

namespace Tests\Battle\Factory;

use Battle\Classes\ClassFactory;
use Battle\Classes\ClassFactoryException;
use Battle\Unit\UnitInterface;
use Battle\Unit\Unit;

class UnitFactory
{
    private static $units = [
        1  => [
            'name'         => 'unit_1',
            'avatar'       => '/images/avas/humans/human001.jpg',
            'damage'       => 20,
            'attack_speed' => 1.00,
            'life'         => 100,
            'melee'        => true,
            'class'        => 1,
        ],
        2  => [
            'name'         => 'unit_2',
            'avatar'       => '/images/avas/humans/human002.jpg',
            'damage'       => 30,
            'attack_speed' => 1.00,
            'life'         => 150,
            'melee'        => true,
            'class'        => 1,
        ],
        3  => [
            'name'         => 'unit_3',
            'avatar'       => '/images/avas/humans/human003.jpg',
            'damage'       => 15,
            'attack_speed' => 1.00,
            'life'         => 120,
            'melee'        => true,
            'class'        => 1,
        ],
        4  => [
            'name'         => 'unit_4',
            'avatar'       => '/images/avas/humans/human004.jpg',
            'damage'       => 15,
            'attack_speed' => 1.00,
            'life'         => 20,
            'melee'        => true,
            'class'        => 1,
        ],
        5  => [
            'name'         => 'unit_5',
            'avatar'       => '/images/avas/monsters/003.png',
            'damage'       => 15,
            'attack_speed' => 1.00,
            'life'         => 80,
            'melee'        => false,
            'class'        => 2,
        ],
        10 => [
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
            self::$units[$template]['name'],
            self::$units[$template]['avatar'],
            self::$units[$template]['damage'],
            self::$units[$template]['attack_speed'],
            self::$units[$template]['life'],
            self::$units[$template]['melee'],
            ClassFactory::create(self::$units[$template]['class'])
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
