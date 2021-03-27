<?php

namespace Battle;

use Battle\Result\ResultInterface;
use Battle\Statistic\BattleStatistic;

interface BattleInterface
{
    /**
     * Обрабатывает бой, возвращая массив итоговых характеристик юнитов
     *
     * @return ResultInterface
     */
    public function handle(): ResultInterface;

    /**
     * Возвращает статистику по бою
     *
     * @return BattleStatistic
     */
    public function getStatistics(): BattleStatistic;
}
