<?php

declare(strict_types=1);

namespace Battle\Stroke;

use Battle\Chat\Chat;
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
     * @param Chat $chat
     * @param bool|null $debug
     * @return StrokeInterface
     */
    public function create(
        int $actionCommand,
        UnitInterface $actionUnit,
        CommandInterface $leftCommand,
        CommandInterface $rightCommand,
        Statistic $statistics,
        Chat $chat,
        ?bool $debug = false
    ): StrokeInterface
    {
        return new Stroke(
            $actionCommand,
            $actionUnit,
            $leftCommand,
            $rightCommand,
            $statistics,
            $chat,
            $debug
        );
    }
}
