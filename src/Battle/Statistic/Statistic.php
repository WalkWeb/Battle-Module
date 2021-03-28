<?php

declare(strict_types=1);

namespace Battle\Statistic;

use Battle\Action\ActionInterface;
use Battle\Action\DamageAction;
use Battle\Statistic\UnitStatistic\UnitStatistic;

/**
 * @package Battle\Statistic
 */
class Statistic implements StatisticInterface
{
    /**
     * Дублируется со счетчиком в Battle по той причине, что статистики в бою может и не быть, но подсчет раундов в
     * любом случае нужен
     *
     * @var int - Количество раундов
     */
    private $roundNumber = 1;

    /***
     * Дублируется со счетчиком в Battle по той причине, что статистики в бою может и не быть, но подсчет ходов в любом
     * случае нужен
     *
     * @var int - Количество ходов
     */
    private $strokeNumber = 1;

    /**
     * TODO Переделать на UnitStatisticCollection
     *
     * @var array - Статистика по юнитам
     */
    private $unitsStatistics = [];

    /**
     * Время начала боя
     *
     * @var float
     */
    private $startTime;

    /**
     * Затраченное время на выполнения боя
     *
     * @var float
     */
    private $runtime;

    /**
     * Затраченная память на начало боя
     *
     * @var int
     */
    private $startMemory;

    /**
     * Количество байт памяти затраченных на выполнение боя
     *
     * @var int
     */
    private $memoryCost;

    public function __construct()
    {
        $this->startTime = microtime(true);
        $this->startMemory = memory_get_peak_usage();
    }

    public function increasedRound(): void
    {
        $this->roundNumber++;
    }

    public function getRoundNumber(): int
    {
        return $this->roundNumber;
    }

    public function increasedStroke(): void
    {
        $this->strokeNumber++;
    }

    public function getStrokeNumber(): int
    {
        return $this->strokeNumber;
    }

    /**
     * Добавляет действие юнита для расчета суммарного полученного и нанесенного урона
     *
     * TODO Добавить подсчет суммарного лечения
     *
     * @param ActionInterface $action
     */
    public function addUnitAction(ActionInterface $action): void
    {
        if ($action instanceof DamageAction) {
            $this->countingCausedDamage($action);
            $this->countingTakenDamage($action);
        }
    }

    /**
     * Возвращает статистику по всем юнитам
     *
     * @return UnitStatistic[]
     */
    public function getUnitsStatistics(): array
    {
        return $this->unitsStatistics;
    }

    /**
     * @return float
     */
    public function getRuntime(): float
    {
        if ($this->runtime === null) {
            $this->runtime = round((microtime(true) - $this->startTime) * 1000, 2);
        }

        return $this->runtime;
    }

    /**
     * @return int
     */
    public function getMemoryCost(): int
    {
        if ($this->memoryCost === null) {
            $this->memoryCost = memory_get_peak_usage() - $this->startMemory;
        }

        return $this->memoryCost;
    }

    /**
     * @return string
     */
    public function getMemoryCostClipped(): string
    {
        return $this->convert($this->getMemoryCost());
    }

    /**
     * Возвращает статистику по конкретному юниту
     *
     * @param string $name
     * @return UnitStatistic
     */
    private function getUnitStatistics(string $name): UnitStatistic
    {
        // todo Проверка на существование

        return $this->unitsStatistics[$name];
    }

    /**
     * Суммирует нанесенный юнитом урон
     *
     * @param ActionInterface $action
     */
    private function countingCausedDamage(ActionInterface $action): void
    {
        if (!array_key_exists($action->getActionUnit()->getName(), $this->unitsStatistics)) {
            $unit = new UnitStatistic($action->getActionUnit()->getName());
            $unit->addCausedDamage($action->getFactualPower());

            $defendUnit = $action->getTargetUnit();
            if (!$defendUnit->isAlive()) {
                // todo В подсчете убитых юнитов есть ошибка - убитых получается больше, чем юнитов в команде противников
                $unit->addKillingUnit();
            }

            $this->unitsStatistics[$action->getActionUnit()->getName()] = $unit;
        } else {
            $unit = $this->getUnitStatistics($action->getActionUnit()->getName());

            $defendUnit = $action->getTargetUnit();
            if (!$defendUnit->isAlive()) {
                $unit->addKillingUnit();
            }

            $unit->addCausedDamage($action->getFactualPower());
        }
    }

    /**
     * Суммирует полученный урон юнитом
     *
     * @param ActionInterface $action
     */
    private function countingTakenDamage(ActionInterface $action): void
    {
        if (!array_key_exists($action->getTargetUnit()->getName(), $this->unitsStatistics)) {
            $unit = new UnitStatistic($action->getTargetUnit()->getName());
            $unit->addTakenDamage($action->getFactualPower());
            $this->unitsStatistics[$action->getTargetUnit()->getName()] = $unit;
        } else {
            $unit = $this->getUnitStatistics($action->getTargetUnit()->getName());
            $unit->addTakenDamage($action->getFactualPower());
        }
    }

    /**
     * @param int $size
     * @return string
     */
    private function convert(int $size): string
    {
        $unit = ['byte', 'kb', 'mb', 'gb', 'tb', 'pb'];
        $i = (int)floor(log($size, 1024));
        return round($size / 1024**$i, 2) . ' ' . $unit[$i];
    }
}
