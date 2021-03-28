<?php

namespace Battle\Statistic\UnitStatistic;

interface UnitStatisticInterface
{
    /**
     * Добавляет нанесенный юнитом урон
     *
     * @param int $damage
     */
    public function addCausedDamage(int $damage): void;

    /**
     * Добавляет полученный юнитом урон
     *
     * @param int $damage
     */
    public function addTakenDamage(int $damage): void;

    /**
     * Увеличивает количество убитых юнитом противников на 1
     */
    public function addKillingUnit(): void;

    /**
     * Возвращает имя юнита
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Возвращает суммарный нанесенный урон юнита
     *
     * @return int
     */
    public function getCausedDamage(): int;

    /**
     * Возвращает суммарный полученный урон юнитом
     *
     * @return int
     */
    public function getTakenDamage(): int;

    /**
     * Возвращает количество убитых противников юнитом
     *
     * @return int
     */
    public function getKilling(): int;
}
