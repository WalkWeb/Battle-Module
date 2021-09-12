<?php

declare(strict_types=1);

namespace Battle\Unit\Classes;

use Battle\Unit\Classes\Demon\Succubus;
use Battle\Unit\Classes\Dwarf\Alchemist;
use Battle\Unit\Classes\Human\Priest;
use Battle\Unit\Classes\Human\Warrior;
use Battle\Unit\Classes\Orc\Titan;
use Battle\Unit\Classes\Other\IncorrectUnitClassForTest;
use Battle\Unit\Classes\Undead\DarkMage;
use Battle\Unit\Classes\Undead\DeadKnight;
use Battle\Result\Chat\Message\Message;
use Battle\Result\Chat\Message\MessageInterface;

class UnitClassFactory
{
    private static $map = [
        1   => Warrior::class,
        2   => Priest::class,
        3   => DeadKnight::class,
        4   => DarkMage::class,
        5   => Titan::class,
        6   => Alchemist::class,
        7   => Succubus::class,
        100 => IncorrectUnitClassForTest::class,
    ];

    /**
     * Создает класс юнита на основе ID класса
     *
     * От класса зависят способности, которые юнит будет применять в бою
     *
     * @param int $classId
     * @param MessageInterface|null $message
     * @return UnitClassInterface
     * @throws ClassFactoryException
     */
    public static function create(int $classId, ?MessageInterface $message = null): UnitClassInterface
    {
        $message = $message ?? new Message();

        if (!array_key_exists($classId, self::$map)) {
            throw new ClassFactoryException(ClassFactoryException::UNDEFINED_CLASS_ID . ': ' . $classId);
        }

        $className = self::$map[$classId];
        $class = new $className($message);

        if (!($class instanceof UnitClassInterface)) {
            throw new ClassFactoryException(ClassFactoryException::INCORRECT_CLASS);
        }

        return $class;
    }
}
