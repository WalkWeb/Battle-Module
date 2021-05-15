<?php

declare(strict_types=1);

namespace Battle\Stroke;

use Battle\Result\FullLog\FullLog;
use Battle\Command\CommandInterface;
use Battle\Statistic\Statistic;
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
     * @param Statistic $statistics
     * @param FullLog $fullLog
     * @param bool|null $debug
     * @return StrokeInterface
     */
    public function create(
        int $actionCommand,
        UnitInterface $actionUnit,
        CommandInterface $leftCommand,
        CommandInterface $rightCommand,
        Statistic $statistics,
        FullLog $fullLog,
        ?bool $debug = false
    ): StrokeInterface
    {
        return new Stroke(
            $actionCommand,
            $actionUnit,
            $leftCommand,
            $rightCommand,
            $statistics,
            $fullLog,
            $debug
        );
    }
}
