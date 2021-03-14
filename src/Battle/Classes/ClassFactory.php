<?php

declare(strict_types=1);

namespace Battle\Classes;

class ClassFactory
{
    /**
     * TODO добавить массив-маппинг классов в формате ['id' => 'className'] и создавать классы без if проверок
     *
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
