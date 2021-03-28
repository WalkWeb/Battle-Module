<?php

namespace Battle;

use Battle\Result\ResultInterface;
use Battle\Statistic\Statistic;

interface BattleInterface
{
    public const COMMAND_PARAMETER = 'command';
    public const LEFT_COMMAND      = 'left';
    public const RIGHT_COMMAND     = 'right';

    /**
     * Обрабатывает бой, возвращая массив итоговых характеристик юнитов
     *
     * @return ResultInterface
     */
    public function handle(): ResultInterface;

    /**
     * Возвращает статистику по бою
     *
     * @return Statistic
     */
    public function getStatistics(): Statistic;
}
