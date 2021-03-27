<?php

namespace Battle\Round;

use Battle\Statistic\BattleStatistic;

interface RoundInterface
{
    /**
     * Выполняет раунд
     *
     * Раундом считается выполненным, когда все живые юниты сделали свой ход. После этого, если обе команды остались
     * живы - сбрасываются параметры $action у юнитов и начинается новый раунд.
     *
     * Впрочем, раунд может вообще ничего не делать - это не сломает механику работы Battle - просто бой завершится по
     * лимиту раундов
     *
     * @return int
     * @throws RoundException
     */
    public function handle(): int;

    /**
     * Возвращает статистику дополненную информацией по текущему раунду
     *
     * @return BattleStatistic
     */
    public function getStatistics(): BattleStatistic;
}
