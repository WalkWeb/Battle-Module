<?php

declare(strict_types=1);

namespace Battle\Stroke;

use Battle\Container\ContainerInterface;
use Battle\Command\CommandInterface;
use Battle\Unit\UnitInterface;

class StrokeFactory
{
    /**
     * Создает Stroke на основе переданных данных
     *
     * Хоть текущая фабрика и проста, она позволяет абстрагироваться в самом Round от конкретной реализации хода
     *
     * @param int $actionCommand
     * @param UnitInterface $actionUnit
     * @param CommandInterface $leftCommand
     * @param CommandInterface $rightCommand
     * @param ContainerInterface $container
     * @return StrokeInterface
     */
    public function create(
        int $actionCommand,
        UnitInterface $actionUnit,
        CommandInterface $leftCommand,
        CommandInterface $rightCommand,
        ContainerInterface $container
    ): StrokeInterface
    {
        return new Stroke(
            $actionCommand,
            $actionUnit,
            $leftCommand,
            $rightCommand,
            $container
        );
    }
}
