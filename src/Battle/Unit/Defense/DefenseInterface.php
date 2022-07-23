<?php

declare(strict_types=1);

namespace Battle\Unit\Defense;

/**
 * Defense – это объект-хранилище защитных характеристик. По умолчанию подразумеваются защитные характеристики юнита.
 *
 * @package Battle\Unit\Defense
 */
interface DefenseInterface
{
    public const MIN_DEFENSE = 1;
    public const MAX_DEFENSE = 1000000;

    public const MIN_BLOCK   = 0;
    public const MAX_BLOCK   = 100;

    public const MIN_MENTAL_BARRIER = 0;
    public const MAX_MENTAL_BARRIER = 100;

    /**
     * Возвращает защиту юнита. Влияет на шанс уклониться от вражеской атаки (с оружия)
     *
     * @return int
     */
    public function getDefense(): int;

    /**
     * Устанавливает новое значение защиты. Применяется в эффектах, изменяющих защиту юнита
     *
     * @param int $defense
     */
    public function setDefense(int $defense): void;

    /**
     * Возвращает шанс блока вражеских атак юнита
     *
     * @return int
     */
    public function getBlock(): int;

    /**
     * Устанавливает новое значение блока. Применяется в эффектах, изменяющих блок юнита
     *
     * @param int $block
     */
    public function setBlock(int $block): void;

    /**
     * Возвращает значение ментального барьера (часть урона, которая вначале будет идти по мане, и только потом по
     * здоровью). В диапазоне от 0 до 100%
     *
     * @return int
     */
    public function getMentalBarrier(): int;

    /**
     * Устанавливает новое значение ментального барьера. Применяется в эффектах, изменяющих его
     *
     * @param int $mentalBarrier
     */
    public function setMentalBarrier(int $mentalBarrier): void;
}
