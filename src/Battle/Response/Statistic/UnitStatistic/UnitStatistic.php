<?php

declare(strict_types=1);

namespace Battle\Response\Statistic\UnitStatistic;

use Battle\Unit\UnitInterface;

class UnitStatistic implements UnitStatisticInterface
{
    /**
     * @var UnitInterface
     */
    private UnitInterface $unit;

    /**
     * @var int - Нанесенный юнитом урон
     */
    private int $causedDamage = 0;

    /**
     * @var int - Количество ударов юнита
     */
    private int $hits = 0;

    /**
     * @var int - Количество критических ударов юнита
     */
    private $criticalHits = 0;

    /**
     * @var int - Полученный юнитом урон
     */
    private int $takenDamage = 0;

    /**
     * @var int - Заблокировано получаемых ударов
     */
    private int $blockedHits = 0;

    /**
     * @var int - Уклонился от получаемых ударов
     */
    private int $dodgedHits = 0;

    /**
     * @var int - Суммарное вылеченное здоровье юнитом
     */
    private int $heal = 0;

    /**
     * @var int - Убил юнитов
     */
    private int $killing = 0;

    /**
     * @var int - Призвал существ
     */
    private int $summons = 0;

    /**
     * @var int - Воскресил союзников
     */
    private int $resurrection = 0;

    /**
     * @param UnitInterface $unit
     */
    public function __construct(UnitInterface $unit)
    {
        $this->unit = $unit;
    }

    /**
     * @return UnitInterface
     */
    public function getUnit(): UnitInterface
    {
        return $this->unit;
    }

    /**
     * @param int $damage
     */
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

    /**
     * @param int $damage
     */
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

    /**
     * @param int $heal
     */
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

    /**
     * @return int
     */
    public function getCausedDamage(): int
    {
        return $this->causedDamage;
    }

    /**
     * @return int
     */
    public function getHits(): int
    {
        return $this->hits;
    }

    /**
     * @return int
     */
    public function getCriticalHits(): int
    {
        return $this->criticalHits;
    }

    /**
     * @return int
     */
    public function getTakenDamage(): int
    {
        return $this->takenDamage;
    }

    /**
     * @return int
     */
    public function getBlockedHits(): int
    {
        return $this->blockedHits;
    }

    /**
     * @return int
     */
    public function getDodgedHits(): int
    {
        return $this->dodgedHits;
    }

    /**
     * @return int
     */
    public function getHeal(): int
    {
        return $this->heal;
    }

    /**
     * @return int
     */
    public function getKilling(): int
    {
        return $this->killing;
    }

    /**
     * @return int
     */
    public function getSummons(): int
    {
        return $this->summons;
    }

    /**
     * @return int
     */
    public function getResurrections(): int
    {
        return $this->resurrection;
    }
}
