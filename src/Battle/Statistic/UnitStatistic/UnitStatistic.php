<?php

declare(strict_types=1);

namespace Battle\Statistic\UnitStatistic;

class UnitStatistic implements UnitStatisticInterface
{
    /**
     * @var string - ID юнита
     */
    private $id;

    /**
     * @var string - Имя юнита
     */
    private $name;

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
     * @param string $id
     * @param string $name
     */
    public function __construct(string $id, string $name)
    {
        $this->id = $id;
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
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
