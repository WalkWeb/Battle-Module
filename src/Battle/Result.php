<?php

declare(strict_types=1);

namespace Battle;

use Battle\Command\CommandInterface;
use Battle\Exception\ResultException;

class Result
{
    private const LEFT_COMMAND_WIN = 'Left command win';
    private const RIGHT_COMMAND_WIN = 'Right command win';

    /** @var CommandInterface */
    private $leftCommand;

    /** @var CommandInterface */
    private $rightCommand;

    /** @var int - Победившая команда: 1 - левая команда, 2 - правая команда */
    private $winner;

    /**
     * @param CommandInterface $leftCommand
     * @param CommandInterface $rightCommand
     * @param int $winner
     * @throws ResultException
     */
    public function __construct(CommandInterface $leftCommand, CommandInterface $rightCommand, int $winner)
    {
        if ($winner !== 1 && $winner !== 2) {
            throw new ResultException(ResultException::INCORRECT_WINNER);
        }

        $this->winner = $winner;
        $this->leftCommand = $leftCommand;
        $this->rightCommand = $rightCommand;
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
}
