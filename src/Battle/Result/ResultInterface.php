<?php

namespace Battle\Result;

use Battle\Result\FullLog\FullLog;
use Battle\Command\CommandInterface;
use Battle\Statistic\Statistic;

interface ResultInterface
{
    public const LEFT_COMMAND_WIN  = 'Left command win';
    public const RIGHT_COMMAND_WIN = 'Right command win';

    /**
     * Возвращает левую команду
     *
     * @return CommandInterface
     */
    public function getLeftCommand(): CommandInterface;

    /**
     * Возвращает правую команду
     *
     * @return CommandInterface
     */
    public function getRightCommand(): CommandInterface;

    /**
     * Возвращает номер победившей команды: 1 - левая команда, 2 - правая команда
     *
     * @return int
     */
    public function getWinner(): int;

    /**
     * Возвращает название победившей команды текстом
     *
     * @return string
     */
    public function getWinnerText(): string;

    /**
     * Возвращает детальных лог боя
     *
     * @return FullLog
     */
    public function getFullLog(): FullLog;

    /**
     * Возвращает статистику по бою
     *
     * @return Statistic
     */
    public function getStatistic(): Statistic;
}
