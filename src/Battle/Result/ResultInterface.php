<?php

namespace Battle\Result;

use Battle\Command\CommandInterface;

interface ResultInterface
{
    public const LEFT_COMMAND_WIN = 'Left command win';
    public const RIGHT_COMMAND_WIN = 'Right command win';

    public function getLeftCommand(): CommandInterface;
    public function getRightCommand(): CommandInterface;
    public function getWinner(): int;
    public function getWinnerText(): string;
}
