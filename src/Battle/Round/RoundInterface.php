<?php

namespace Battle\Round;

use Battle\Response\Statistic\StatisticInterface;
use Exception;

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
     * @throws Exception
     */
    public function handle(): int;

    /**
     * Возвращает статистику дополненную информацией по текущему раунду
     *
     * @return StatisticInterface
     */
    public function getStatistics(): StatisticInterface;
}
