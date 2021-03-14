<?php

declare(strict_types=1);

namespace Battle;

use Battle\Chat\Chat;
use Battle\Command\CommandInterface;
use Battle\Exception\BattleException;
use Battle\Exception\CommandException;
use Battle\Exception\RoundException;
use Battle\Exception\ActionCollectionException;
use Battle\Statistic\BattleStatistic;
use Exception;
use Battle\Exception\ResultException;

class Battle
{
    /** @var CommandInterface */
    private $leftCommand;

    /** @var CommandInterface */
    private $rightCommand;

    /** @var int - Команда, которая совершает ход: 1 - leftCommand, 2 - rightCommand */
    private $actionCommand;

    /** @var int */
    private $maxRound = 50;

    /** @var bool */
    private $debug;

    /** @var BattleStatistic */
    private $statistics;

    /** @var Chat */
    private $chat;

    /**
     * @param CommandInterface $leftCommand
     * @param CommandInterface $rightCommand
     * @param BattleStatistic $statistics
     * @param Chat $chat
     * @param bool $debug
     * @throws Exception
     */
    public function __construct(
        CommandInterface $leftCommand,
        CommandInterface $rightCommand,
        BattleStatistic $statistics,
        Chat $chat,
        bool $debug = true
    )
    {
        $this->leftCommand = $leftCommand;
        $this->rightCommand = $rightCommand;
        $this->statistics = $statistics;
        $this->chat = $chat;
        $this->actionCommand = random_int(1, 2);
        $this->debug = $debug;
    }

    /**
     * Обрабатывает бой, возвращая массив итоговых характеристик юнитов
     *
     * @return Result
     * @throws ActionCollectionException
     * @throws BattleException
     * @throws CommandException
     * @throws ResultException
     * @throws RoundException
     */
    public function handle(): Result
    {
        $i = 0;

        while ($i < $this->maxRound) {
            $round = new Round(
                $this->leftCommand,
                $this->rightCommand,
                $this->actionCommand,
                $this->statistics,
                $this->chat,
                $this->debug
            );

            // Выполняем раунд, получая номер команды, которая будет ходить следующей
            $this->actionCommand = $round->handle();

            // Проверяем живых в командах
            if (!$this->leftCommand->isAlive() || !$this->rightCommand->isAlive()) {
                $winner = !$this->leftCommand->isAlive() ? 2 : 1;
                return new Result($this->leftCommand, $this->rightCommand, $winner);
            }

            $this->statistics->increasedRound();
            $i++;
        }

        throw new BattleException(BattleException::UNEXPECTED_ENDING_BATTLE);
    }

    /**
     * @return BattleStatistic
     */
    public function getStatistics(): BattleStatistic
    {
        return $this->statistics;
    }
}
