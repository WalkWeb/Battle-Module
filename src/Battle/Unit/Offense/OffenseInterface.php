<?php

declare(strict_types=1);

namespace Battle\Unit\Offense;

/**
 * Offense – это объект-хранилище атакующих характеристик. По умолчанию подразумеваются атакующие характеристики юнита.
 *
 * @package Battle\Unit\Offense
 */
interface OffenseInterface
{
    /**
     * Возвращает урон
     *
     * @return int
     */
    public function getDamage(): int;

    /**
     * Возвращает скорость атаки
     *
     * @return float
     */
    public function getAttackSpeed(): float;

    /**
     * Возвращает меткость
     *
     * @return int
     */
    public function getAccuracy(): int;

    /**
     * Возвращает показатель игнорирования блока цели
     *
     * @return int
     */
    public function getBlockIgnore(): int;
}
