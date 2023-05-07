<?php

declare(strict_types=1);

namespace Battle\Unit\Offense\MultipleOffense;

/**
 * Для реализации разнообразных и интересных способностей на урон, нужны механики как фиксированного урона - Offense с
 * фиксированными показателями урона, меткости и прочего, так и механики, которые увеличивают (или уменьшают) атакующие
 * параметры юнит. Например, способность наносит 150% от урона юнита. Также нужны механики, которые позволяют урон из
 * одного типа стихии конвертировать в другой.
 *
 * Для реализации этих механик создан отдельный объект - MultipleOffense. И если он указан в способности, то это
 * указывает на то, что конкретные параметры Offense должны быть рассчитаны отдельно, на основании Offense юнита и
 * MultipleOffense
 *
 * TODO В будущем можно пойти еще дальше, сделав такие механики, когда одна характеристика может указываться
 * TODO фиксированно, другая браться в % на основе характеристик юнита, а к третьей добавляться фиксированное значение.
 * TODO Например способность, которая имеет фиксированный 25% шанс критического урона, урон в размере 200% от урона,
 * TODO юнита, и шанс пробить блок +10
 *
 * @package Battle\Unit\Offense\MultipleOffense
 */
interface MultipleOffenseInterface
{
    // TODO Добавить параметр изменяющий тип урона, в атаку или заклинание

    // TODO Изменение параметра игнорирования блока цели

    public const MIN_MULTIPLIER = 0;
    public const MAX_MULTIPLIER = 10;

    public const CONVERT_NONE     = '';
    public const CONVERT_PHYSICAL = 'convert_physical';
    public const CONVERT_FIRE     = 'convert_fire';
    public const CONVERT_WATER    = 'convert_water';
    public const CONVERT_AIR      = 'convert_air';
    public const CONVERT_EARTH    = 'convert_earth';
    public const CONVERT_LIFE     = 'convert_life';
    public const CONVERT_DEATH    = 'convert_death';

    /**
     * Возвращает множитель урона
     *
     * @return float
     */
    public function getDamageMultiplier(): float;

    /**
     * Возвращает множитель скорости атаки
     *
     * @return float
     */
    public function getSpeedMultiplier(): float;

    /**
     * Возвращает множитель меткости
     *
     * @return float
     */
    public function getAccuracyMultiplier(): float;

    /**
     * Возвращает множитель шанса критического удара
     *
     * @return float
     */
    public function getCriticalChanceMultiplier(): float;

    /**
     * Возвращает множитель урона силы критического удара
     *
     * @return float
     */
    public function getCriticalMultiplierMultiplier(): float;

    /**
     * Если указано значение больше 0 - то будет использоваться параметр вампиризма указанный в способности, а не
     * базовый вампиризм юнита
     *
     * @return int
     */
    public function getVampirism(): int;

    /**
     * @return string
     */
    public function getDamageConvert(): string;
}
