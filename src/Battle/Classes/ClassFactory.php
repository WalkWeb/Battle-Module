<?php

declare(strict_types=1);

namespace Battle\Classes;

class ClassFactory
{
    /**
     * @param int $classId
     * @return UnitClassInterface
     * @throws ClassFactoryException
     */
    public static function create(int $classId): UnitClassInterface
    {
        if ($classId === UnitClassInterface::WARRIOR) {
            return new Warrior();
        }
        if ($classId === UnitClassInterface::PRIEST) {
            return new Priest();
        }

        throw new ClassFactoryException(ClassFactoryException::UNDEFINED_CLASS_ID);
    }
}
