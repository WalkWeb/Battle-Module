<?php

declare(strict_types=1);

namespace Battle\Result;

use Battle\Chat\Chat;
use Battle\Command\CommandInterface;

class Result implements ResultInterface
{
    /** @var CommandInterface */
    private $leftCommand;

    /** @var CommandInterface */
    private $rightCommand;

    /** @var int - Победившая команда: 1 - левая команда, 2 - правая команда */
    private $winner;

    /** @var Chat */
    private $chat;

    /**
     * @param CommandInterface $leftCommand
     * @param CommandInterface $rightCommand
     * @param int $winner
     * @param Chat $chat
     * @throws ResultException
     */
    public function __construct(CommandInterface $leftCommand, CommandInterface $rightCommand, int $winner, Chat $chat)
    {
        if ($winner !== 1 && $winner !== 2) {
            throw new ResultException(ResultException::INCORRECT_WINNER);
        }

        $this->winner = $winner;
        $this->leftCommand = $leftCommand;
        $this->rightCommand = $rightCommand;
        $this->chat = $chat;
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

    public function getChat(): Chat
    {
        return $this->chat;
    }
}
