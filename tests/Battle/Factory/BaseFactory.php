<?php

declare(strict_types=1);

namespace Tests\Battle\Factory;

use Battle\Command\CommandException;
use Battle\Command\CommandFactory;
use Battle\Unit\UnitException;

class BaseFactory
{
    /**
     * Для тестов часто необходимо получить 4 объекта: $unit, $command, $enemyCommand, $enemyUnit
     *
     * Данная фабрика сделана для того, чтобы делать это одной строчкой, а не четырьмя
     *
     * Пример использования:
     * [$unit, $command, $enemyCommand, $enemyUnit] = BaseFactory::create(1, 2);
     *
     * @param int $unitId
     * @param int $enemyUnitId
     * @return array
     * @throws UnitFactoryException
     * @throws CommandException
     * @throws UnitException
     */
    public static function create(int $unitId, int $enemyUnitId): array
    {
        $unit = UnitFactory::createByTemplate($unitId);
        $command = CommandFactory::create([$unit]);
        $enemyUnit = UnitFactory::createByTemplate($enemyUnitId);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        return [$unit, $command, $enemyCommand, $enemyUnit];
    }
}
