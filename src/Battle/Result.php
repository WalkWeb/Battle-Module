<?php

declare(strict_types=1);

namespace Battle;

use Battle\Exception\ResultException;

class Result
{
    private const LEFT_COMMAND_WIN = 'Left command win';
    private const RIGHT_COMMAND_WIN = 'Right command win';

    /** @var Command */
    private $leftCommand;

    /** @var Command */
    private $rightCommand;

    /** @var int - Победившая команда: 1 - левая команда, 2 - правая команда */
    private $winner;

    /**
     * @param Command $leftCommand
     * @param Command $rightCommand
     * @param int $winner
     * @throws ResultException
     */
    public function __construct(Command $leftCommand, Command $rightCommand, int $winner)
    {
        if ($winner !== 1 && $winner !== 2) {
            throw new ResultException(ResultException::INCORRECT_WINNER);
        }

        $this->winner = $winner;
        $this->leftCommand = $leftCommand;
        $this->rightCommand = $rightCommand;
    }

    public function getLeftCommand(): Command
    {
        return $this->leftCommand;
    }

    public function getRightCommand(): Command
    {
        return $this->rightCommand;
    }

    public function getWinner(): int
    {
        return $this->winner;
    }

    public function getWinnerTest(): string
    {
        return $this->winner === 1 ? self::LEFT_COMMAND_WIN : self::RIGHT_COMMAND_WIN;
    }
}
