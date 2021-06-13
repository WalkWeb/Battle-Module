<?php

namespace Battle\Result;

use Battle\Result\Chat\Chat;
use Battle\Result\FullLog\FullLog;
use Battle\Command\CommandInterface;
use Battle\Result\Scenario\ScenarioInterface;
use Battle\Statistic\Statistic;
use Battle\Translation\TranslationInterface;

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
     * Возвращает итоговый чат по бою
     *
     * @return Chat
     */
    public function getChat(): Chat;

    /**
     * Возвращает статистику по бою
     *
     * @return Statistic
     */
    public function getStatistic(): Statistic;

    /**
     * Возвращает объект отвечающий за мультиязычность
     *
     * @return TranslationInterface
     */
    public function getTranslation(): TranslationInterface;

    /**
     * Возвращает js-скрипт для анимации боя. По сути выводит результат
     *
     * @return ScenarioInterface
     */
    public function getScenario(): ScenarioInterface;
}
