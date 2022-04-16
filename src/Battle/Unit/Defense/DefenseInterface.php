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
    // TODO Add MAX_DEFENSE

    public const MIN_BLOCK   = 0;
    public const MAX_BLOCK   = 100;

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
     *  Устанавливает новое значение блока. Применяется в эффектах, изменяющих блок юнита
     *
     * @param int $block
     */
    public function setBlock(int $block): void;
}
