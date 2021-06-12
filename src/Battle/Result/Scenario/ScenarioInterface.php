<?php

declare(strict_types=1);

namespace Battle\Result\Scenario;

use Battle\Action\ActionInterface;

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
     * Добавляет анимацию атаки
     *
     * @param ActionInterface $action
     */
    public function addDamage(ActionInterface $action): void;

    /**
     * Добавляет анимацию лечения
     *
     * @param ActionInterface $action
     */
    public function addHeal(ActionInterface $action): void;

    /**
     * Добавляет анимацию пропуска хода
     *
     * @param ActionInterface $action
     */
    public function addWait(ActionInterface $action): void;

    /**
     * Возвращает сформированный js-сценарий анимации боя
     *
     * @return string
     */
    public function getScenario(): string;

    /**
     * Возвращает сценарий в виде массива
     *
     * @return array
     */
    public function getScenarioArray(): array;
}
