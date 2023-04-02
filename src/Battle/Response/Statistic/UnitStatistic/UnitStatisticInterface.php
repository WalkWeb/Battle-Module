<?php

namespace Battle\Response\Statistic\UnitStatistic;

use Battle\Unit\UnitInterface;

interface UnitStatisticInterface
{
    /**
     * Возвращает юнита, по которому ведется статистика
     *
     * @return UnitInterface
     */
    public function getUnit(): UnitInterface;

    /**
     * Добавляет нанесенный юнитом урон
     *
     * @param int $damage
     */
    public function addCausedDamage(int $damage): void;

    /**
     * Добавляет один нанесенный удар
     */
    public function addHit(): void;

    /**
     * Добавляет один критический нанесенный удар
     */
    public function addCriticalHit(): void;

    /**
     * Добавляет полученный юнитом урон
     *
     * @param int $damage
     */
    public function addTakenDamage(int $damage): void;

    /**
     * Указывает, что юнит заблокировал входящий удар
     */
    public function addBlockedHit(): void;

    /**
     * Указывает, что юнит уклонился от входящего удара
     */
    public function addDodgedHit(): void;

    /**
     * Добавляет вылеченное здоровье юнитом
     *
     * @param int $heal
     */
    public function addHeal(int $heal): void;

    /**
     * Увеличивает количество убитых юнитом противников на 1
     */
    public function addKillingUnit(): void;

    /**
     * Увеличивает количество призванных существ юнитом на 1
     */
    public function addSummon(): void;

    /**
     * Увеличивает количество воскрешенных союзников юнитом на 1
     */
    public function addResurrection(): void;

    /**
     * Возвращает суммарный нанесенный урон юнита
     *
     * @return int
     */
    public function getCausedDamage(): int;

    /**
     * Возвращает суммарное количество ударов юнита
     *
     * @return int
     */
    public function getHits(): int;

    /**
     * Возвращает суммарное количество критических ударов юнита
     *
     * @return int
     */
    public function getCriticalHits(): int;

    /**
     * Возвращает суммарный полученный урон юнитом
     *
     * @return int
     */
    public function getTakenDamage(): int;

    /**
     * Возвращает суммарное количество заблокированных входящих ударов по юниту
     *
     * @return int
     */
    public function getBlockedHits(): int;

    /**
     * Возвращает суммарное количество уклонений от входящих ударов по юниту
     *
     * @return int
     */
    public function getDodgedHits(): int;

    /**
     * Возвращает суммарное вылеченное здоровье юнитом
     *
     * @return int
     */
    public function getHeal(): int;

    /**
     * Возвращает количество убитых противников юнитом
     *
     * @return int
     */
    public function getKilling(): int;

    /**
     * Возвращает количество призванных существ юнитом
     *
     * @return int
     */
    public function getSummons(): int;

    /**
     * @return int
     */
    public function getResurrections(): int;
}
