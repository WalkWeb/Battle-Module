<?php

declare(strict_types=1);

namespace Battle\Statistic\UnitStatistic;

use Battle\Unit\UnitInterface;

class UnitStatistic implements UnitStatisticInterface
{
    /**
     * @var UnitInterface
     */
    private $unit;

    /**
     * @var int - Нанесенный юнитом урон
     */
    private $causedDamage = 0;

    /**
     * @var int - Полученный юнитом урон
     */
    private $takenDamage = 0;

    /**
     * @var int - Суммарное вылеченное здоровье юнитом
     */
    private $heal = 0;

    /**
     * @var int - Убил юнитов
     */
    private $killing = 0;

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

    /**
     * @param int $damage
     */
    public function addTakenDamage(int $damage): void
    {
        $this->takenDamage += $damage;
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
    public function getTakenDamage(): int
    {
        return $this->takenDamage;
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
}
