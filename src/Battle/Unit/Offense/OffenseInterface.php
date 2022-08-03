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
    public const TYPE_ATTACK        = 1;
    public const TYPE_SPELL         = 2;

    public const MIN_DAMAGE         = 0;
    public const MAX_DAMAGE         = 100000;

    public const MIN_ATTACK_SPEED   = 0.0;
    public const MAX_ATTACK_SPEED   = 10;

    public const MIN_ACCURACY       = 1;
    public const MAX_ACCURACY       = 1000000;

    public const MIN_MAGIC_ACCURACY = 1;
    public const MAX_MAGIC_ACCURACY = 1000000;

    public const MIN_BLOCK_IGNORE   = 0;
    public const MAX_BLOCK_IGNORE   = 100;

    /**
     * Возвращает тип урона: атака или заклинание
     *
     * @return int
     */
    public function getTypeDamage(): int;

    /**
     * Возвращает урон
     *
     * TODO Сейчас это единственный параметр урона, в будущем он будет возвращать суммарный урон по всем стихиям
     * TODO с учетом сопротивлений цели
     *
     * @return int
     */
    public function getDamage(): int;

    /**
     * Устанавливает новое значение урона. Применяется в эффектах, изменяющих урон юнита
     * 
     * @param int $damage
     */
    public function setDamage(int $damage): void;

    /**
     * Возвращает физический урон
     *
     * @return int
     */
    public function getPhysicalDamage(): int;

    /**
     * Устанавливает новое значение физического урона. Применяется в эффектах, изменяющих физический урон юнита
     *
     * @param int $physicalDamage
     */
    public function setPhysicalDamage(int $physicalDamage): void;

    /**
     * Возвращает скорость атаки
     *
     * @return float
     */
    public function getAttackSpeed(): float;

    /**
     * @param float $attackSpeed
     */
    public function setAttackSpeed(float $attackSpeed): void;
    
    /**
     * Возвращает меткость (влияет на шанс попадания при использовании атак)
     *
     * @return int
     */
    public function getAccuracy(): int;

    /**
     * Устанавливает новое значение меткости. Применяется в эффектах, изменяющих её
     *
     * @param int $accuracy
     */
    public function setAccuracy(int $accuracy): void;

    /**
     * Возвращает магическую меткость (влияет на шанс попадания при использовании заклинаний)
     *
     * @return int
     */
    public function getMagicAccuracy(): int;

    /**
     * Устанавливает новое значение магической меткости. Применяется в эффектах, изменяющих её
     *
     * @param int $magicAccuracy
     */
    public function setMagicAccuracy(int $magicAccuracy): void;

    /**
     * Возвращает значение игнорирования блока цели. Необходимо для реализации механик, когда, например, определенное
     * оружие может игнорировать блок цели. Для полного игнорирования блока цели необходимо вернуть 100. Хотя можно
     * и вернуть другое значение, например 10, и тогда шанс блока целью в 25% будет уменьшен до шанса в 15%
     *
     * @return int
     */
    public function getBlockIgnore(): int;

    /**
     * @param int $blockIgnore
     */
    public function setBlockIgnore(int $blockIgnore): void;

    /**
     * Возвращает ДПС (средний урон за ход = урон * скорость атаки)
     *
     * @return float
     */
    public function getDPS(): float;
}
