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
    public const MIN_RESISTANCE     = -1000;
    public const MAX_RESISTANCE     = 100;

    public const MIN_DEFENSE        = 1;
    public const MAX_DEFENSE        = 1000000;

    public const MIN_MAGIC_DEFENSE  = 1;
    public const MAX_MAGIC_DEFENSE  = 1000000;

    // Может быть отрицательным до -100% (штраф)
    public const MIN_BLOCK          = -100;
    public const MAX_BLOCK          = 100;

    // Аналогично
    public const MIN_MAGIC_BLOCK    = -100;
    public const MAX_MAGIC_BLOCK    = 100;

    // Аналогично
    public const MIN_MENTAL_BARRIER = -100;
    public const MAX_MENTAL_BARRIER = 100;

    /**
     * Возвращает сопротивление физическому урону
     *
     * @return int
     */
    public function getPhysicalResist(): int;

    /**
     * Устанавливает новое значение сопротивления физическому урону. Применяется в эффектах, изменяющих этот параметр
     *
     * @param int $physicalResist
     * @throws DefenseException
     */
    public function setPhysicalResist(int $physicalResist): void;

    /**
     * Возвращает сопротивление урону огнем
     *
     * @return int
     */
    public function getFireResist(): int;

    /**
     * Устанавливает новое значение сопротивления урону огнем. Применяется в эффектах, изменяющих этот параметр
     *
     * @param int $physicalResist
     * @throws DefenseException
     */
    public function setFireResist(int $physicalResist): void;

    /**
     * Возвращает сопротивление урону водой
     *
     * @return int
     */
    public function getWaterResist(): int;

    /**
     * Устанавливает новое значение сопротивления урону водой. Применяется в эффектах, изменяющих этот параметр
     *
     * @param int $physicalResist
     * @throws DefenseException
     */
    public function setWaterResist(int $physicalResist): void;

    /**
     * Возвращает сопротивление урону воздухом
     *
     * @return int
     */
    public function getAirResist(): int;

    /**
     * Устанавливает новое значение сопротивления урону воздухом. Применяется в эффектах, изменяющих этот параметр
     *
     * @param int $physicalResist
     * @throws DefenseException
     */
    public function setAirResist(int $physicalResist): void;

    /**
     * Возвращает сопротивление урону землей
     *
     * @return int
     */
    public function getEarthResist(): int;

    /**
     * Устанавливает новое значение сопротивления урону землей. Применяется в эффектах, изменяющих этот параметр
     *
     * @param int $physicalResist
     * @throws DefenseException
     */
    public function setEarthResist(int $physicalResist): void;

    /**
     * Возвращает сопротивление урону магией жизни
     *
     * @return int
     */
    public function getLifeResist(): int;

    /**
     * Устанавливает новое значение сопротивления урону магией жизни. Применяется в эффектах, изменяющих этот параметр
     *
     * @param int $physicalResist
     * @throws DefenseException
     */
    public function setLifeResist(int $physicalResist): void;

    /**
     * Возвращает сопротивление урону магией смерти
     *
     * @return int
     */
    public function getDeathResist(): int;

    /**
     * Устанавливает новое значение сопротивления урону магией смерти. Применяется в эффектах, изменяющих этот параметр
     *
     * @param int $physicalResist
     * @throws DefenseException
     */
    public function setDeathResist(int $physicalResist): void;

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
     * @throws DefenseException
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
     * @throws DefenseException
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
     * @throws DefenseException
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
     * @throws DefenseException
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
     * @throws DefenseException
     */
    public function setMentalBarrier(int $mentalBarrier): void;

    /**
     * Возвращает максимальное сопротивление физическому урону
     *
     * @return int
     */
    public function getPhysicalMaxResist(): int;

    /**
     * Устанавливает новое максимальное значение сопротивления физическому урону. Используется в эффектах
     *
     * @param int $physicalMaxResist
     * @throws DefenseException
     */
    public function setPhysicalMaxResist(int $physicalMaxResist): void;

    /**
     * Возвращает максимальное сопротивление урону огнем
     *
     * @return int
     */
    public function getFireMaxResist(): int;

    /**
     * Устанавливает новое максимальное значение сопротивления урону огнем. Используется в эффектах
     *
     * @param int $fireMaxResist
     * @throws DefenseException
     */
    public function setFireMaxResist(int $fireMaxResist): void;

    /**
     * Возвращает максимальное сопротивление урону водой
     *
     * @return int
     */
    public function getWaterMaxResist(): int;

    /**
     * Устанавливает новое максимальное значение сопротивления урону водой. Используется в эффектах
     *
     * @param int $waterMaxResist
     * @throws DefenseException
     */
    public function setWaterMaxResist(int $waterMaxResist): void;

    /**
     * Возвращает максимальное сопротивление урону воздухом
     *
     * @return int
     */
    public function getAirMaxResist(): int;

    /**
     * Устанавливает новое максимальное значение сопротивления урону воздухом. Используется в эффектах
     *
     * @param int $airMaxResist
     * @throws DefenseException
     */
    public function setAirMaxResist(int $airMaxResist): void;

    /**
     * Возвращает максимальное сопротивление урону землей
     *
     * @return int
     */
    public function getEarthMaxResist(): int;

    /**
     * Устанавливает новое максимальное значение сопротивления урону землей. Используется в эффектах
     *
     * @param int $earthMaxResist
     * @throws DefenseException
     */
    public function setEarthMaxResist(int $earthMaxResist): void;

    /**
     * Возвращает максимальное сопротивление урону магией жизни
     *
     * @return int
     */
    public function getLifeMaxResist(): int;

    /**
     * Устанавливает новое максимальное значение сопротивления урону магией жизни. Используется в эффектах
     *
     * @param int $lifeMaxResist
     * @throws DefenseException
     */
    public function setLifeMaxResist(int $lifeMaxResist): void;

    /**
     * Возвращает максимальное сопротивление урону магией смерти
     *
     * @return int
     */
    public function getDeathMaxResist(): int;

    /**
     * Устанавливает новое максимальное значение сопротивления урону магией смерти. Используется в эффектах
     *
     * @param int $deathMaxResist
     * @throws DefenseException
     */
    public function setDeathMaxResist(int $deathMaxResist): void;

    /**
     * Возвращает значение общего множителя получаемого урона (в %, т.е. 30 = -30% получаемого урона, -20 - +20%)
     *
     * @return int
     */
    public function getGlobalResist(): int;

    /**
     * Устанавливает новое значение общего множителя получаемого урона. Используется в эффектах
     *
     * @param int $globalDamageResist
     * @throws DefenseException
     */
    public function setGlobalResist(int $globalDamageResist): void;
}
