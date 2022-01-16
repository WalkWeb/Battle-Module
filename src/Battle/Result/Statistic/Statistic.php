<?php

declare(strict_types=1);

namespace Battle\Result\Statistic;

use Battle\Action\ActionException;
use Battle\Action\ActionInterface;
use Battle\Action\DamageAction;
use Battle\Action\HealAction;
use Battle\Action\ResurrectionAction;
use Battle\Action\SummonAction;
use Battle\Result\Statistic\UnitStatistic\UnitStatistic;
use Battle\Result\Statistic\UnitStatistic\UnitStatisticCollection;
use Battle\Result\Statistic\UnitStatistic\UnitStatisticInterface;

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
     * @throws ActionException
     * @throws StatisticException
     */
    public function addUnitAction(ActionInterface $action): void
    {
        switch ($action) {
            case $action instanceof DamageAction:
                $this->countingCausedDamage($action);
                $this->countingTakenDamage($action);
                break;
            case $action instanceof ResurrectionAction:
            case $action instanceof HealAction:
                $this->countingHeal($action);
                break;
            case $action instanceof SummonAction:
                $this->countingSummons($action);
                break;
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
        return $this->unitsStatistics->get($name);
    }

    /**
     * Суммирует нанесенный юнитом урон
     *
     * @param ActionInterface $action
     * @throws StatisticException
     * @throws ActionException
     */
    private function countingCausedDamage(ActionInterface $action): void
    {
        // TODO На данный момент механика применения Action ко множеству целей в процессе добавления
        // TODO Задача поделена на несколько этапов, и обновление формирования статистики будет сделано отдельно
        // TODO Для того, чтобы все работало как раньше - выбираем первую цель (пока нет событий с несколькими целями)
        $defendUnit = $action->getTargetUnits()[0];

        if (!$this->unitsStatistics->exist($action->getActionUnit()->getId())) {
            $unit = new UnitStatistic($action->getActionUnit());
            $unit->addCausedDamage($action->getFactualPower());
            $unit->addHit();

            if (!$defendUnit->isAlive()) {
                $unit->addKillingUnit();
            }

            $this->unitsStatistics->add($unit);
        } else {
            $unit = $this->getUnitStatistics($action->getActionUnit()->getId());

            if (!$defendUnit->isAlive()) {
                $unit->addKillingUnit();
            }

            $unit->addCausedDamage($action->getFactualPower());
            $unit->addHit();
        }
    }

    /**
     * Суммирует полученный урон юнитом
     *
     * @param ActionInterface $action
     * @throws ActionException
     * @throws StatisticException
     */
    private function countingTakenDamage(ActionInterface $action): void
    {
        // С.м. комментарий выше
        $defendUnit = $action->getTargetUnits()[0];

        if (!$this->unitsStatistics->exist($defendUnit->getId())) {
            $unit = new UnitStatistic($defendUnit);
            $unit->addTakenDamage($action->getFactualPower());
            $this->unitsStatistics->add($unit);
        } else {
            $unit = $this->getUnitStatistics($defendUnit->getId());
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
        if (!$this->unitsStatistics->exist($action->getActionUnit()->getId())) {
            $unit = new UnitStatistic($action->getActionUnit());
            $unit->addHeal($action->getFactualPower());
            $this->unitsStatistics->add($unit);
        } else {
            $unit = $this->getUnitStatistics($action->getActionUnit()->getId());
            $unit->addHeal($action->getFactualPower());
        }

        if ($action instanceof ResurrectionAction) {
            $this->countingResurrections($action);
        }
    }

    /**
     * @param ActionInterface $action
     * @throws StatisticException
     */
    private function countingSummons(ActionInterface $action): void
    {
        if (!$this->unitsStatistics->exist($action->getActionUnit()->getId())) {
            $unit = new UnitStatistic($action->getActionUnit());
            $unit->addSummon();
            $this->unitsStatistics->add($unit);
        } else {
            $unit = $this->getUnitStatistics($action->getActionUnit()->getId());
            $unit->addSummon();
        }
    }

    /**
     * Воскрешение всегда восстанавливает хотя бы 1 здоровья, и вызывается после подсчета countingHeal()
     *
     * Соответственно UnitStatistic всегда будет создан, и ему достаточно добавить количество воскрешений
     *
     * @param ActionInterface $action
     * @throws StatisticException
     */
    private function countingResurrections(ActionInterface $action): void
    {
        $unit = $this->getUnitStatistics($action->getActionUnit()->getId());
        $unit->addResurrection();
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
