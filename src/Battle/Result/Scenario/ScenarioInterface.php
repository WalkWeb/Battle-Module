<?php

declare(strict_types=1);

namespace Battle\Result\Scenario;

use Battle\Action\ActionException;
use Battle\Action\ActionInterface;
use Battle\Result\Statistic\StatisticInterface;

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
     * TODO Rename to addAnimation()
     *
     * @param ActionInterface $action
     * @param StatisticInterface $statistic
     * @throws ActionException
     */
    public function addAction(ActionInterface $action, StatisticInterface $statistic): void;

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
