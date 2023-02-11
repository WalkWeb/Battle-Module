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
    /**
     * Возвращает множитель физического урона
     *
     * @return float
     */
    public function getPhysicalDamageMultiplier(): float;

    /**
     * Возвращает множитель урона огнем
     *
     * @return float
     */
    public function getFireDamageMultiplier(): float;

    /**
     * Возвращает множитель урона водой
     *
     * @return float
     */
    public function getWaterDamageMultiplier(): float;

    /**
     * Возвращает множитель урона воздухом
     *
     * @return float
     */
    public function getAirDamageMultiplier(): float;

    /**
     * Возвращает множитель урона землей
     *
     * @return float
     */
    public function getEarthDamageMultiplier(): float;

    /**
     * Возвращает множитель урона магией жизни
     *
     * @return float
     */
    public function getLifeDamageMultiplier(): float;

    /**
     * Возвращает множитель урона магией смерти
     *
     * @return float
     */
    public function getDeathDamageMultiplier(): float;

    /**
     * Возвращает множитель скорости атаки
     *
     * @return float
     */
    public function getAttackSpeedMultiplier(): float;

    /**
     * Возвращает множитель скорости создания заклинаний
     *
     * @return float
     */
    public function getCastSpeedMultiplier(): float;

    /**
     * Возвращает множитель меткости
     *
     * @return float
     */
    public function getAccuracyMultiplier(): float;

    /**
     * Возвращает множитель магической меткости
     *
     * @return float
     */
    public function getMagicAccuracyMultiplier(): float;

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
}
