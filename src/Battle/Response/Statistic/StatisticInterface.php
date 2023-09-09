<?php

namespace Battle\Response\Statistic;

use Battle\Action\ActionInterface;
use Battle\Response\Statistic\UnitStatistic\UnitStatisticCollection;

interface StatisticInterface
{
    // Добавить статистику по критическим ударам

    /**
     * Увеличивает количество раундов в бою на 1
     */
    public function increasedRound(): void;

    /**
     * Возвращает суммарное количество раундов в бою
     *
     * @return int
     */
    public function getRoundNumber(): int;

    /**
     * Увеличивает количество ходов в бою на 1
     */
    public function increasedStroke(): void;

    /**
     * Возвращает суммарное количество ходов в бою
     *
     * @return int
     */
    public function getStrokeNumber(): int;

    /**
     * Добавляет действие юнита для подсчета статистики атак, полученного и нанесенного урона, и прочего
     *
     * @param ActionInterface $action
     */
    public function addUnitAction(ActionInterface $action): void;

    /**
     * Возвращает статистику по всем юнитам
     *
     * @return UnitStatisticCollection
     */
    public function getUnitsStatistics(): UnitStatisticCollection;

    /**
     * Возвращает количество миллисекунд ушедших на обработку боя
     *
     * @return float
     */
    public function getRuntime(): float;

    /**
     * Возвращает количество байт памяти затраченной на обработку боя
     *
     * @return int
     */
    public function getMemoryCost(): int;

    /**
     * Возвращает количество затраченной памяти в сокращенном варианте (т.е. не 440808, а 430 kb)
     *
     * Т.е. в удобном виде для восприятия человеком
     *
     * @return string
     */
    public function getMemoryCostClipped(): string;
}
