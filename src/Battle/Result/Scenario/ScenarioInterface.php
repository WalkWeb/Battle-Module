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
     * Добавляет анимацию действия
     *
     * @param ActionInterface $action
     */
    public function addAction(ActionInterface $action): void;

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
