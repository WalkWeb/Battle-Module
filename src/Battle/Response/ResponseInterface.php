<?php

namespace Battle\Response;

use Battle\Container\ContainerInterface;
use Battle\Response\Chat\ChatInterface;
use Battle\Command\CommandInterface;
use Battle\Response\FullLog\FullLogInterface;
use Battle\Response\Scenario\ScenarioInterface;
use Battle\Response\Statistic\StatisticInterface;
use Battle\Translation\TranslationInterface;

interface ResponseInterface
{
    public const string LEFT_COMMAND_WIN  = 'Left command win';
    public const string RIGHT_COMMAND_WIN = 'Right command win';

    /**
     * Возвращает левую команду с характеристиками на начало боя
     */
    public function getStartLeftCommand(): CommandInterface;

    /**
     * Возвращает правую команду с характеристиками на начало боя
     */
    public function getStartRightCommand(): CommandInterface;

    /**
     * Возвращает левую команду с характеристиками на конец боя
     */
    public function getEndLeftCommand(): CommandInterface;

    /**
     * Возвращает правую команду с характеристиками на конец боя
     */
    public function getEndRightCommand(): CommandInterface;

    /**
     * Возвращает номер победившей команды: 1 - левая команда, 2 - правая команда
     */
    public function getWinner(): int;

    /**
     * Возвращает название победившей команды текстом
     */
    public function getWinnerText(): string;

    /**
     * Возвращает детальных лог боя
     */
    public function getFullLog(): FullLogInterface;

    /**
     * Возвращает итоговый чат по бою
     */
    public function getChat(): ChatInterface;

    /**
     * Возвращает статистику по бою
     */
    public function getStatistic(): StatisticInterface;

    /**
     * Возвращает объект отвечающий за мультиязычность
     */
    public function getTranslation(): TranslationInterface;

    /**
     * Возвращает js-скрипт для анимации боя. По сути выводит результат
     */
    public function getScenario(): ScenarioInterface;

    /**
     * Возвращает контейнер
     */
    public function getContainer(): ContainerInterface;
}
