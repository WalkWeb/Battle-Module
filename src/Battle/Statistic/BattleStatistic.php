<?php

declare(strict_types=1);

namespace Battle\Statistic;

use Battle\Action\ActionInterface;
use Battle\Action\DamageAction;

class BattleStatistic
{
    /**
     * Дублируется со счетчиков в Battle по той причине, что статистики в бою может и не быть, но подсчет раундов в
     * любом случае нужен
     *
     * @var int - Количество раундов
     */
    private $roundNumber = 1;

    /***
     * Дублируется со счетчиков в Battle по той причине, что статистики в бою может и не быть, но подсчет ходов в любом
     * случае нужен
     *
     * @var int - Количество ходов
     */
    private $strokeNumber = 1;

    /**
     * @var array - Статистика по юнитам
     */
    private $unitsStatistics = [];

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

    public function addUnitAction(ActionInterface $action): void
    {
        if ($action instanceof DamageAction) {
            $this->countingCausedDamage($action);
            $this->countingTakenDamage($action);
        }
    }

    /**
     * @return UnitStatistic[]
     */
    public function getUnitsStatistics(): array
    {
        return $this->unitsStatistics;
    }

    private function getUnitStatistics(string $name): UnitStatistic
    {
        return $this->unitsStatistics[$name];
    }

    private function countingCausedDamage(ActionInterface $action): void
    {
        if (!array_key_exists($action->getActionUnit()->getName(), $this->unitsStatistics)) {
            $unit = new UnitStatistic($action->getActionUnit()->getName());
            $unit->addCausedDamage($action->getFactualPower());

            $defendUnit = $action->getTargetUnit();
            if (!$defendUnit->isAlive()) {
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
}
