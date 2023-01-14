<?php

declare(strict_types=1);

namespace Battle\Unit\Offense;

use Battle\Unit\Defense\DefenseInterface;
use Battle\Weapon\Type\WeaponTypeInterface;

/**
 * Offense – это объект-хранилище атакующих характеристик. По умолчанию подразумеваются атакующие характеристики юнита.
 *
 * @package Battle\Unit\Offense
 */
interface OffenseInterface
{
    public const TYPE_ATTACK             = 1;
    public const TYPE_SPELL              = 2;

    public const MIN_DAMAGE              = 0;
    public const MAX_DAMAGE              = 100000;

    public const MIN_ATTACK_SPEED        = 0.0;
    public const MAX_ATTACK_SPEED        = 10;

    public const MIN_CAST_SPEED          = 0.0;
    public const MAX_CAST_SPEED          = 10;

    public const MIN_ACCURACY            = 1;
    public const MAX_ACCURACY            = 1000000;

    public const MIN_MAGIC_ACCURACY      = 1;
    public const MAX_MAGIC_ACCURACY      = 1000000;

    public const MIN_BLOCK_IGNORING      = 0;
    public const MAX_BLOCK_IGNORING      = 100;

    public const MIN_CRITICAL_CHANCE     = 0;
    public const MAX_CRITICAL_CHANCE     = 100;

    public const MIN_CRITICAL_MULTIPLIER = 0;
    public const MAX_CRITICAL_MULTIPLIER = 10000;

    public const MIN_DAMAGE_MULTIPLIER   = 0;
    public const MAX_DAMAGE_MULTIPLIER   = 10000;

    public const MIN_VAMPIRE             = 0;
    public const MAX_VAMPIRE             = 100;

    /**
     * Возвращает тип урона: атака или заклинание
     *
     * @return int
     */
    public function getDamageType(): int;

    /**
     * Возвращает тип оружия
     *
     * @return WeaponTypeInterface
     */
    public function getWeaponType(): WeaponTypeInterface;

    /**
     * Возвращает общий урон (по всем стихиям), с учетом сопротивлений цели
     *
     * @param DefenseInterface $defense
     * @return int
     */
    public function getDamage(DefenseInterface $defense): int;

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
     * @throws OffenseException
     */
    public function setPhysicalDamage(int $physicalDamage): void;

    /**
     * Возвращает урон огнем
     *
     * @return int
     */
    public function getFireDamage(): int;

    /**
     * Устанавливает новое значение урона огнем. Применяется в эффектах, изменяющих урон огнем юнита
     *
     * @param int $fireDamage
     * @throws OffenseException
     */
    public function setFireDamage(int $fireDamage): void;

    /**
     * Возвращает урон водой
     *
     * @return int
     */
    public function getWaterDamage(): int;

    /**
     * Устанавливает новое значение урона водой. Применяется в эффектах, изменяющих урон водой юнита
     *
     * @param int $waterDamage
     * @throws OffenseException
     */
    public function setWaterDamage(int $waterDamage): void;

    /**
     * Возвращает урон воздухом
     *
     * @return int
     */
    public function getAirDamage(): int;

    /**
     * Устанавливает новое значение урона воздухом. Применяется в эффектах, изменяющих урон воздухом юнита
     *
     * @param int $airDamage
     * @throws OffenseException
     */
    public function setAirDamage(int $airDamage): void;

    /**
     * Возвращает урон землей
     *
     * @return int
     */
    public function getEarthDamage(): int;

    /**
     * Устанавливает новое значение урона землей. Применяется в эффектах, изменяющих урон землей юнита
     *
     * @param int $earthDamage
     * @throws OffenseException
     */
    public function setEarthDamage(int $earthDamage): void;

    /**
     * Возвращает урон магией жизни
     *
     * @return int
     */
    public function getLifeDamage(): int;

    /**
     * Устанавливает новое значение урона магией жизни. Применяется в эффектах, изменяющих урон магией жизни юнита
     *
     * @param int $lifeDamage
     * @throws OffenseException
     */
    public function setLifeDamage(int $lifeDamage): void;

    /**
     * Возвращает урон магией смерти
     *
     * @return int
     */
    public function getDeathDamage(): int;

    /**
     * Устанавливает новое значение урона магией смерти. Применяется в эффектах, изменяющих урон магией смерти юнита
     *
     * @param int $deathDamage
     * @throws OffenseException
     */
    public function setDeathDamage(int $deathDamage): void;

    /**
     * Возвращает скорость атаки
     *
     * @return float
     */
    public function getAttackSpeed(): float;

    /**
     * @param float $attackSpeed
     * @throws OffenseException
     */
    public function setAttackSpeed(float $attackSpeed): void;

    /**
     * Возвращает скорость создания заклинаний (параметр аналогичный скорости атаки но для типа урона заклинание
     *
     * @return float
     */
    public function getCastSpeed(): float;

    /**
     * @param float $castSpeed
     * @throws OffenseException
     */
    public function setCastSpeed(float $castSpeed): void;

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
     * @throws OffenseException
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
     * @throws OffenseException
     */
    public function setMagicAccuracy(int $magicAccuracy): void;

    /**
     * Возвращает значение игнорирования блока цели. Необходимо для реализации механик, когда, например, определенное
     * оружие может игнорировать блок цели. Для полного игнорирования блока цели необходимо вернуть 100. Хотя можно
     * и вернуть другое значение, например 10, и тогда шанс блока целью в 25% будет уменьшен до шанса в 15%
     *
     * @return int
     */
    public function getBlockIgnoring(): int;

    /**
     * @param int $blockIgnoring
     * @throws OffenseException
     */
    public function setBlockIgnoring(int $blockIgnoring): void;

    /**
     * Возвращает шанс критического удара в %
     *
     * 0 - значит критических ударов никогда не будет, 100% - каждый удар будет критическим
     *
     * @return int
     */
    public function getCriticalChance(): int;

    /**
     * Устанавливает новое значение шанса критического удара. Используется в эффектах, изменяющих этот параметр
     *
     * @param int $criticalChance
     * @throws OffenseException
     */
    public function setCriticalChance(int $criticalChance): void;

    /**
     * Возвращает множитель критического удара в % (и уже в конкретных расчетах критического урона значение делится на
     * 100)
     *
     * @return int
     */
    public function getCriticalMultiplier(): int;

    /**
     * Устанавливает новое значение силы критического удара. Используется в эффектах, изменяющих этот параметр
     *
     * @param int $criticalMultiplier
     * @throws OffenseException
     */
    public function setCriticalMultiplier(int $criticalMultiplier): void;

    /**
     * Возвращает значение вампиризма (0-100%) - указывает, какое количество от фактически нанесенного урона будет
     * своровано в здоровье
     *
     * @return int
     */
    public function getVampirism(): int;

    /**
     * Устанавливает новое значение вампиризма. Используется в эффектах, изменяющих этот параметр
     *
     * @param int $vampirism
     * @throws OffenseException
     */
    public function setVampirism(int $vampirism): void;

    /**
     * Возвращает значение магического вампиризма (0-100%) - указывает, какое количество от фактически нанесенного урона
     * будет своровано в ману
     *
     * @return int
     */
    public function getMagicVampirism(): int;

    /**
     * Устанавливает новое значение магического вампиризма. Используется в эффектах, изменяющих этот параметр
     *
     * @param int $magicVampirism
     * @throws OffenseException
     */
    public function setMagicVampirism(int $magicVampirism): void;

    /**
     * Возвращает общий множитель наносимого урона. Значение в %, т.е. 120 => +20% наносимого урона, 80 => -20%
     *
     * @return int
     */
    public function getDamageMultiplier(): int;

    /**
     * Устанавливает новое значение общего множителя наносимого урона. Используется в эффектах
     *
     * @param int $damageMultiplier
     * @throws OffenseException
     */
    public function setDamageMultiplier(int $damageMultiplier): void;

    /**
     * Возвращает ДПС (средний урон за ход = урон * скорость атаки * (1 + (шанс крита/100) * (сила крита/100 - 1))
     *
     * @return float
     */
    public function getDPS(): float;
}
