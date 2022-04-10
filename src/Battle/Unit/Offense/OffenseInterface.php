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
    public const MIN_DAMAGE       = 0;
    public const MAX_DAMAGE       = 100000;

    public const MIN_ATTACK_SPEED = 0.0;
    public const MAX_ATTACK_SPEED = 10;

    public const MIN_ACCURACY     = 1;
    // TODO Добавить MAX_ACCURACY

    public const MIN_BLOCK_IGNORE = 0;
    public const MAX_BLOCK_IGNORE = 100;

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
