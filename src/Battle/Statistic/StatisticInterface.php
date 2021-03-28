<?php

namespace Battle\Statistic;

use Battle\Action\ActionInterface;

interface StatisticInterface
{
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
     * Добавляет действие юнита для расчета суммарного полученного и нанесенного урона
     *
     * @param ActionInterface $action
     */
    public function addUnitAction(ActionInterface $action): void;

    /**
     * Возвращает статистику по всем юнитам
     *
     * @return UnitStatistic[]
     */
    public function getUnitsStatistics(): array;
}
