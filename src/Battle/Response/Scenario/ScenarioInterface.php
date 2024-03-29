<?php

declare(strict_types=1);

namespace Battle\Response\Scenario;

use Battle\Action\ActionException;
use Battle\Action\ActionInterface;
use Battle\Response\Statistic\StatisticInterface;

/**
 * Для анимации боя на фронте используется js-сценарий.
 *
 * Scenario формирует его.
 *
 * @package Battle\Scenario
 */
interface ScenarioInterface
{
    /**
     * Добавляет анимацию действия
     *
     * @param ActionInterface $action
     * @param StatisticInterface $statistic
     * @throws ActionException
     */
    public function addAnimation(ActionInterface $action, StatisticInterface $statistic): void;

    /**
     * Возвращает js-сценарий анимации боя
     *
     * @return string
     */
    public function getJson(): string;

    /**
     * Возвращает сценарий в виде массива
     *
     * @return array
     */
    public function getArray(): array;
}
