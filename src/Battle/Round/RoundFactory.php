<?php

declare(strict_types=1);

namespace Battle\Round;

use Battle\Result\Chat\Chat;
use Battle\Result\FullLog\FullLog;
use Battle\Command\CommandInterface;
use Battle\Statistic\Statistic;
use Battle\Stroke\StrokeFactory;
use Battle\Translation\Translation;

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
     * @param Statistic $statistics
     * @param FullLog $fullLog
     * @param Chat $chat
     * @param bool|null $debug
     * @param StrokeFactory|null $strokeFactory
     * @param Translation|null $translation
     * @return RoundInterface
     * @throws RoundException
     */
    public function create(
        CommandInterface $leftCommand,
        CommandInterface $rightCommand,
        int $actionCommand,
        Statistic $statistics,
        FullLog $fullLog,
        Chat $chat,
        ?bool $debug = false,
        ?Translation $translation = null,
        ?StrokeFactory $strokeFactory = null
    ): RoundInterface
    {
        return new Round(
            $leftCommand,
            $rightCommand,
            $actionCommand,
            $statistics,
            $fullLog,
            $chat,
            $debug,
            $strokeFactory,
            $translation
        );
    }
}
