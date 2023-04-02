<?php

namespace Battle\Response;

use Battle\Response\Chat\ChatInterface;
use Battle\Command\CommandInterface;
use Battle\Response\FullLog\FullLogInterface;
use Battle\Response\Scenario\ScenarioInterface;
use Battle\Response\Statistic\StatisticInterface;
use Battle\Translation\TranslationInterface;

interface ResponseInterface
{
    public const LEFT_COMMAND_WIN  = 'Left command win';
    public const RIGHT_COMMAND_WIN = 'Right command win';

    /**
     * Возвращает левую команду с характеристиками на начало боя
     *
     * @return CommandInterface
     */
    public function getStartLeftCommand(): CommandInterface;

    /**
     * Возвращает правую команду с характеристиками на начало боя
     *
     * @return CommandInterface
     */
    public function getStartRightCommand(): CommandInterface;

    /**
     * Возвращает левую команду с характеристиками на конец боя
     *
     * @return CommandInterface
     */
    public function getEndLeftCommand(): CommandInterface;

    /**
     * Возвращает правую команду с характеристиками на конец боя
     *
     * @return CommandInterface
     */
    public function getEndRightCommand(): CommandInterface;

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
     * @return FullLogInterface
     */
    public function getFullLog(): FullLogInterface;

    /**
     * Возвращает итоговый чат по бою
     *
     * @return ChatInterface
     */
    public function getChat(): ChatInterface;

    /**
     * Возвращает статистику по бою
     *
     * @return StatisticInterface
     */
    public function getStatistic(): StatisticInterface;

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
