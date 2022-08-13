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
use Battle\Unit\UnitInterface;

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
    private int $roundNumber = 1;

    /***
     * Дублируется со счетчиком в Battle по той причине, что статистики в бою может и не быть, но подсчет ходов в любом
     * случае нужен
     *
     * @var int - Количество ходов
     */
    private int $strokeNumber = 1;

    /**
     * @var UnitStatisticCollection - Статистика по юнитам
     */
    private UnitStatisticCollection $unitsStatistics;

    /**
     * Время начала боя
     *
     * @var float
     */
    private $startTime;

    /**
     * Затраченное время на выполнения боя
     *
     * @var float|null
     */
    private ?float $runtime = null;

    /**
     * Затраченная память на начало боя
     *
     * @var int
     */
    private int $startMemory;

    /**
     * Количество байт памяти затраченных на выполнение боя
     *
     * @var int|null
     */
    private ?int $memoryCost = null;

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
                $this->countingBlockedHits($action);
                $this->countingDodgedHits($action);
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
        $unit = $this->getOrCreateUnitStatistic($action->getCreatorUnit());
        $unit->addCausedDamage($action->getFactualPower());
        $unit->addHit();

        if ($action->isCriticalDamage()) {
            $unit->addCriticalHit();
        }

        foreach ($action->getTargetUnits() as $targetUnit) {
            if (!$targetUnit->isAlive()) {
                $unit->addKillingUnit();
            }
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
        foreach ($action->getTargetUnits() as $targetUnit) {
            if (!$action->isBlocked($targetUnit) && !$action->isDodged($targetUnit)) {
                $unit = $this->getOrCreateUnitStatistic($targetUnit);
                $unit->addTakenDamage($action->getFactualPowerByUnit($targetUnit));
            }
        }
    }

    /**
     * Подсчитывает заблокированные удары
     *
     * @param ActionInterface $action
     * @throws ActionException
     * @throws StatisticException
     */
    private function countingBlockedHits(ActionInterface $action): void
    {
        foreach ($action->getTargetUnits() as $targetUnit) {
            if ($action->isBlocked($targetUnit)) {
                $unit = $this->getOrCreateUnitStatistic($targetUnit);
                $unit->addBlockedHit();
            }
        }
    }

    /**
     * Подсчитывает удары от которых юнит уклонился
     *
     * @param ActionInterface $action
     * @throws ActionException
     * @throws StatisticException
     */
    private function countingDodgedHits(ActionInterface $action): void
    {
        foreach ($action->getTargetUnits() as $targetUnit) {
            if ($action->isDodged($targetUnit)) {
                $unit = $this->getOrCreateUnitStatistic($targetUnit);
                $unit->addDodgedHit();
            }
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
        $unit = $this->getOrCreateUnitStatistic($action->getCreatorUnit());
        $unit->addHeal($action->getFactualPower());

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
        if (!$this->unitsStatistics->exist($action->getCreatorUnit()->getId())) {
            $unit = new UnitStatistic($action->getCreatorUnit());
            $unit->addSummon();
            $this->unitsStatistics->add($unit);
        } else {
            $unit = $this->getUnitStatistics($action->getCreatorUnit()->getId());
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
        $unit = $this->getUnitStatistics($action->getCreatorUnit()->getId());
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

    /**
     * @param UnitInterface $unit
     * @return UnitStatisticInterface
     * @throws StatisticException
     */
    private function getOrCreateUnitStatistic(UnitInterface $unit): UnitStatisticInterface
    {
        if ($this->unitsStatistics->exist($unit->getId())) {
            return $this->getUnitStatistics($unit->getId());
        }

        $unitStatistic = new UnitStatistic($unit);
        $this->unitsStatistics->add($unitStatistic);
        return $unitStatistic;
    }
}
