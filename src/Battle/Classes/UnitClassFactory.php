<?php

declare(strict_types=1);

namespace Battle\Classes;

class UnitClassFactory
{
    private static $map = [
        1 => Warrior::class,
        2 => Priest::class,
    ];

    /**
     * Создает класс юнита на основе ID класса
     *
     * От класса зависят способности, которые юнит будет применять в бою
     *
     * @param int $classId
     * @return UnitClassInterface
     * @throws ClassFactoryException
     */
    public static function create(int $classId): UnitClassInterface
    {
        if (!array_key_exists($classId, self::$map)) {
            throw new ClassFactoryException(ClassFactoryException::UNDEFINED_CLASS_ID);
        }

        $className = self::$map[$classId];
        $class = new $className;

        if (!($class instanceof UnitClassInterface)) {
            throw new ClassFactoryException(ClassFactoryException::INCORRECT_CLASS);
        }

        return $class;
    }
}
