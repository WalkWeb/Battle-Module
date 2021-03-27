<?php

declare(strict_types=1);

namespace Battle\Round;

use Battle\Chat\Chat;
use Battle\Command\CommandInterface;
use Battle\Statistic\BattleStatistic;
use Battle\Stroke\StrokeFactory;

class RoundFactory
{
    /**
     * Создает раунд на основе переданных данных
     *
     * Хоть текущая фабрика и проста, она позволяет абстрагироваться в самом Battle от конкретной реализации
     *
     * @param CommandInterface $leftCommand
     * @param CommandInterface $rightCommand
     * @param int $actionCommand
     * @param BattleStatistic $statistics
     * @param Chat $chat
     * @param bool|null $debug
     * @param StrokeFactory|null $strokeFactory
     * @return RoundInterface
     * @throws RoundException
     */
    public function create(
        CommandInterface $leftCommand,
        CommandInterface $rightCommand,
        int $actionCommand,
        BattleStatistic $statistics,
        Chat $chat,
        ?bool $debug = false,
        ?StrokeFactory $strokeFactory = null
    ): RoundInterface
    {
        return new Round(
            $leftCommand,
            $rightCommand,
            $actionCommand,
            $statistics,
            $chat,
            $debug,
            $strokeFactory
        );
    }
}
