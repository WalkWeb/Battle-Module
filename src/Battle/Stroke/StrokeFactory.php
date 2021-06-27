<?php

declare(strict_types=1);

namespace Battle\Stroke;

use Battle\Result\Chat\Chat;
use Battle\Result\FullLog\FullLog;
use Battle\Command\CommandInterface;
use Battle\Result\Scenario\ScenarioInterface;
use Battle\Statistic\Statistic;
use Battle\Translation\Translation;
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
     * @param Chat $chat
     * @param ScenarioInterface $scenario
     * @param Translation $translation
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
        Chat $chat,
        ScenarioInterface $scenario,
        Translation $translation,
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
            $chat,
            $scenario,
            $translation,
            $debug
        );
    }
}
