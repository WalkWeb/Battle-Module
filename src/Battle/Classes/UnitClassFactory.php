<?php

declare(strict_types=1);

namespace Battle\Classes;

use Battle\Classes\Human\Priest;
use Battle\Classes\Human\Warrior;
use Battle\Classes\Other\IncorrectUnitClassForTest;
use Battle\Classes\Undead\DarkMage;
use Battle\Classes\Undead\DeadKnight;
use Battle\Result\Chat\Message;

class UnitClassFactory
{
    private static $map = [
        1   => Warrior::class,
        2   => Priest::class,
        3   => DeadKnight::class,
        4   => DarkMage::class,
        100 => IncorrectUnitClassForTest::class,
    ];

    /**
     * Создает класс юнита на основе ID класса
     *
     * От класса зависят способности, которые юнит будет применять в бою
     *
     * @param int $classId
     * @param Message|null $message
     * @return UnitClassInterface
     * @throws ClassFactoryException
     */
    public static function create(int $classId, ?Message $message = null): UnitClassInterface
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
