<?php

declare(strict_types=1);

namespace Battle;

use Battle\Chat\Chat;
use Battle\Command\CommandInterface;
use Battle\Round\RoundFactory;
use Battle\Statistic\BattleStatistic;
use Exception;

class BattleFactory
{
    /**
     * TODO Переделать фабрику следующим образом:
     * TODO - из обязательных параметров - только массив юнитов
     * TODO - все остальные параметры - не обязательные
     *
     * @param CommandInterface $leftCommand
     * @param CommandInterface $rightCommand
     * @param BattleStatistic $statistics
     * @param Chat $chat
     * @param bool|null $debug
     * @param RoundFactory|null $roundFactory
     * @return BattleInterface
     * @throws Exception
     */
    public static function create(
        CommandInterface $leftCommand,
        CommandInterface $rightCommand,
        BattleStatistic $statistics,
        Chat $chat,
        ?bool $debug = true,
        ?RoundFactory $roundFactory = null
    ): BattleInterface
    {
        return new Battle(
            $leftCommand,
            $rightCommand,
            $statistics,
            $chat,
            $debug,
            $roundFactory
        );
    }

    public function createLeftCommand(array $data): CommandInterface
    {
        // todo
    }

    public function createRightCommand(): CommandInterface
    {
        // todo
    }
}
