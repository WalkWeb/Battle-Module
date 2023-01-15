<?php

declare(strict_types=1);

namespace Tests\Factory;

use Battle\Command\CommandException;
use Battle\Command\CommandFactory;
use Battle\Container\ContainerInterface;
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
     * @param ContainerInterface|null $container
     * @return array
     * @throws CommandException
     * @throws UnitException
     * @throws UnitFactoryException
     */
    public static function create(int $unitId, int $enemyUnitId, ?ContainerInterface $container = null): array
    {
        $unit = UnitFactory::createByTemplate($unitId, $container);
        $command = CommandFactory::create([$unit]);
        $enemyUnit = UnitFactory::createByTemplate($enemyUnitId, $container);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        return [$unit, $command, $enemyCommand, $enemyUnit];
    }
}
