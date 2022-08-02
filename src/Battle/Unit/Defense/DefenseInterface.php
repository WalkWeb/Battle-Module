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
    public const MIN_DEFENSE        = 1;
    public const MAX_DEFENSE        = 1000000;

    public const MIN_MAGIC_DEFENSE  = 1;
    public const MAX_MAGIC_DEFENSE  = 1000000;

    // TODO Может быть отрицательным до -100 (этакий штраф), при подсчете если минусовой, приравнивается 0
    public const MIN_BLOCK          = 0;
    public const MAX_BLOCK          = 100;

    // TODO Аналогично
    public const MIN_MAGIC_BLOCK    = 0;
    public const MAX_MAGIC_BLOCK    = 100;

    // TODO Аналогично
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
     * Возвращает магическую защиту юнита. Влияет на шанс уклониться от вражеского заклинания
     *
     * @return int
     */
    public function getMagicDefense(): int;

    /**
     * Устанавливает новое значение магической защиты. Применяется в эффектах, изменяющих магическую защиту юнита
     *
     * @param int $magicDefense
     */
    public function setMagicDefense(int $magicDefense): void;

    /**
     * Возвращает шанс блока вражеских атак юнита (0-100%)
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
     * Возвращает шанс блока вражеских заклинаний юнита (0-100%)
     *
     * @return int
     */
    public function getMagicBlock(): int;

    /**
     * Устанавливает новое значение магического блока. Применяется в эффектах, изменяющих магический блок юнита
     *
     * @param int $magicBlock
     */
    public function setMagicBlock(int $magicBlock): void;

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
