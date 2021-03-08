<?php

declare(strict_types=1);

namespace Battle\Statistic;

class UnitStatistic
{
    /** @var string - Имя юнита */
    private $name;

    /** @var int - Нанесенный юнитом урон */
    private $causedDamage = 0;

    /** @var int - Полученный юнитом урон */
    private $takenDamage = 0;

    /** @var int - Убил юнитов */
    private $killing = 0;

    /**
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function addCausedDamage(int $damage): void
    {
        $this->causedDamage += $damage;
    }

    public function addTakenDamage(int $damage): void
    {
        $this->takenDamage += $damage;
    }

    public function addKillingUnit(): void
    {
        $this->killing++;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
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
    public function getKilling(): int
    {
        return $this->killing;
    }
}
