<?php

declare(strict_types=1);

namespace Battle\Result;

use Battle\Result\FullLog\FullLog;
use Battle\Command\CommandInterface;
use Battle\Statistic\Statistic;

class Result implements ResultInterface
{
    /** @var CommandInterface */
    private $leftCommand;

    /** @var CommandInterface */
    private $rightCommand;

    /** @var int - Победившая команда: 1 - левая команда, 2 - правая команда */
    private $winner;

    /** @var FullLog */
    private $fullLog;

    /** @var Statistic */
    private $statistic;

    /**
     * @param CommandInterface $leftCommand
     * @param CommandInterface $rightCommand
     * @param int $winner
     * @param FullLog $fullLog
     * @param Statistic $statistic
     * @throws ResultException
     */
    public function __construct(
        CommandInterface $leftCommand,
        CommandInterface $rightCommand,
        int $winner,
        FullLog $fullLog,
        Statistic $statistic
    )
    {
        if ($winner !== 1 && $winner !== 2) {
            throw new ResultException(ResultException::INCORRECT_WINNER);
        }

        $this->winner = $winner;
        $this->leftCommand = $leftCommand;
        $this->rightCommand = $rightCommand;
        $this->fullLog = $fullLog;
        $this->statistic = $statistic;
    }

    public function getLeftCommand(): CommandInterface
    {
        return $this->leftCommand;
    }

    public function getRightCommand(): CommandInterface
    {
        return $this->rightCommand;
    }

    public function getWinner(): int
    {
        return $this->winner;
    }

    public function getWinnerText(): string
    {
        return $this->winner === 1 ? self::LEFT_COMMAND_WIN : self::RIGHT_COMMAND_WIN;
    }

    public function getFullLog(): FullLog
    {
        return $this->fullLog;
    }

    public function getStatistic(): Statistic
    {
        return $this->statistic;
    }
}
