<?php

declare(strict_types=1);

namespace Battle\Response\Statistic\UnitStatistic;

use Battle\Unit\UnitInterface;

class UnitStatistic implements UnitStatisticInterface
{
    private UnitInterface $unit;

    /**
     * Нанесенный юнитом урон
     */
    private int $causedDamage = 0;

    /**
     * Количество ударов юнита
     */
    private int $hits = 0;

    /**
     * Количество критических ударов юнита
     */
    private int $criticalHits = 0;

    /**
     * Полученный юнитом урон
     */
    private int $takenDamage = 0;

    /**
     * Заблокировано получаемых ударов
     */
    private int $blockedHits = 0;

    /**
     * Уклонился от получаемых ударов
     */
    private int $dodgedHits = 0;

    /**
     * Суммарное вылеченное здоровье юнитом
     */
    private int $heal = 0;

    /**
     * Убил юнитов
     */
    private int $killing = 0;

    /**
     * Призвал существ
     */
    private int $summons = 0;

    /**
     * Воскресил союзников
     */
    private int $resurrection = 0;

    public function __construct(UnitInterface $unit)
    {
        $this->unit = $unit;
    }

    public function getUnit(): UnitInterface
    {
        return $this->unit;
    }

    public function addCausedDamage(int $damage): void
    {
        $this->causedDamage += $damage;
    }

    public function addHit(): void
    {
        $this->hits++;
    }

    public function addCriticalHit(): void
    {
        $this->criticalHits++;
    }

    public function addTakenDamage(int $damage): void
    {
        $this->takenDamage += $damage;
    }

    public function addBlockedHit(): void
    {
        $this->blockedHits++;
    }

    public function addDodgedHit(): void
    {
        $this->dodgedHits++;
    }

    public function addHeal(int $heal): void
    {
        $this->heal += $heal;
    }

    public function addKillingUnit(): void
    {
        $this->killing++;
    }

    public function addSummon(): void
    {
        $this->summons++;
    }

    public function addResurrection(): void
    {
        $this->resurrection++;
    }

    public function getCausedDamage(): int
    {
        return $this->causedDamage;
    }

    public function getHits(): int
    {
        return $this->hits;
    }

    public function getCriticalHits(): int
    {
        return $this->criticalHits;
    }

    public function getTakenDamage(): int
    {
        return $this->takenDamage;
    }

    public function getBlockedHits(): int
    {
        return $this->blockedHits;
    }

    public function getDodgedHits(): int
    {
        return $this->dodgedHits;
    }

    public function getHeal(): int
    {
        return $this->heal;
    }

    public function getKilling(): int
    {
        return $this->killing;
    }

    public function getSummons(): int
    {
        return $this->summons;
    }

    public function getResurrections(): int
    {
        return $this->resurrection;
    }
}
