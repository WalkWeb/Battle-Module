<?php

declare(strict_types=1);

namespace Battle\Statistic;

use Battle\Action\ActionInterface;
use Battle\Action\DamageAction;
use Battle\Action\HealAction;
use Battle\Statistic\UnitStatistic\UnitStatistic;
use Battle\Statistic\UnitStatistic\UnitStatisticCollection;
use Battle\Statistic\UnitStatistic\UnitStatisticInterface;

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
     * @var UnitStatisticCollection - Статистика по юнитам
     */
    private $unitsStatistics;

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

    public function __construct(?UnitStatisticCollection $collection = null)
    {
        $this->unitsStatistics = $collection ?? new UnitStatisticCollection();
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
     * @param ActionInterface $action
     * @throws StatisticException
     */
    public function addUnitAction(ActionInterface $action): void
    {
        if ($action instanceof DamageAction) {
            $this->countingCausedDamage($action);
            $this->countingTakenDamage($action);
        }
        if ($action instanceof HealAction) {
            $this->countingHeal($action);
        }
    }

    /**
     * Возвращает статистику по всем юнитам
     *
     * @return UnitStatisticCollection
     */
    public function getUnitsStatistics(): UnitStatisticCollection
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
     * @return UnitStatisticInterface
     * @throws StatisticException
     */
    private function getUnitStatistics(string $name): UnitStatisticInterface
    {
        return $this->unitsStatistics->getUnitByName($name);
    }

    /**
     * Суммирует нанесенный юнитом урон
     *
     * @param ActionInterface $action
     * @throws StatisticException
     */
    private function countingCausedDamage(ActionInterface $action): void
    {
        if (!$this->unitsStatistics->existUnitByName($action->getActionUnit()->getName())) {
            $unit = new UnitStatistic($action->getActionUnit()->getName());
            $unit->addCausedDamage($action->getFactualPower());

            $defendUnit = $action->getTargetUnit();
            if (!$defendUnit->isAlive()) {
                // todo В подсчете убитых юнитов есть ошибка - убитых получается больше, чем юнитов в команде противников
                $unit->addKillingUnit();
            }

            $this->unitsStatistics->add($unit);
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
     * @throws StatisticException
     */
    private function countingTakenDamage(ActionInterface $action): void
    {
        if (!$this->unitsStatistics->existUnitByName($action->getTargetUnit()->getName())) {
            $unit = new UnitStatistic($action->getTargetUnit()->getName());
            $unit->addTakenDamage($action->getFactualPower());
            $this->unitsStatistics->add($unit);
        } else {
            $unit = $this->getUnitStatistics($action->getTargetUnit()->getName());
            $unit->addTakenDamage($action->getFactualPower());
        }
    }

    /**
     * Суммирует суммарное лечение юнитом
     *
     * @param ActionInterface $action
     * @throws StatisticException
     */
    private function countingHeal(ActionInterface $action): void
    {
        if (!$this->unitsStatistics->existUnitByName($action->getActionUnit()->getName())) {
            $unit = new UnitStatistic($action->getActionUnit()->getName());
            $unit->addHeal($action->getFactualPower());
            $this->unitsStatistics->add($unit);
        } else {
            $unit = $this->getUnitStatistics($action->getActionUnit()->getName());
            $unit->addHeal($action->getFactualPower());
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
