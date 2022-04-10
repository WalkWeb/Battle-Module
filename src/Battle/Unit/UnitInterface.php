<?php

declare(strict_types=1);

namespace Battle\Unit;

use Battle\Action\ActionCollection;
use Battle\Action\ActionInterface;
use Battle\Unit\Classes\UnitClassInterface;
use Battle\Command\CommandInterface;
use Battle\Container\ContainerInterface;
use Battle\Unit\Ability\AbilityCollection;
use Battle\Unit\Effect\EffectCollection;
use Battle\Unit\Race\RaceInterface;

/**
 * Одна боевая единица.
 *
 * Это может быть персонаж, монстр, босс или npc - не важно
 *
 * @package Battle\Unit
 */
interface UnitInterface
{
    // TODO Количество параметров юнита увеличивается, и необходимо вынести в отдельные объекты следующие параметры:
    // TODO Offense: damage, attackSpeed, accuracy, blockIgnore
    // TODO Defense: block, defense

    // TODO Вынести константы на урон в Offense

    // TODO Добавить ограничение на максимальную меткость

    public const ADD_CON_NEW_ROUND       = 200;
    public const ADD_CON_ACTION_UNIT     = 180;
    public const ADD_CON_RECEIVING_UNIT  = 100;
    public const ADD_RAGE_NEW_ROUND      = 50;
    public const ADD_RAGE_ACTION_UNIT    = 90;
    public const ADD_RAGE_RECEIVING_UNIT = 70;
    public const MAX_CONS                = 1000;
    public const MAX_RAGE                = 1000;

    public const MIN_DAMAGE       = 0;
    public const MAX_DAMAGE       = 100000;

    public const MIN_ATTACK_SPEED = 0.0;
    public const MAX_ATTACK_SPEED = 10;

    public const MIN_ACCURACY     = 1;
    public const MIN_DEFENSE      = 1;

    public const MIN_BLOCK        = 0;
    public const MAX_BLOCK        = 100;

    public const MIN_BLOCK_IGNORE = 0;
    public const MAX_BLOCK_IGNORE = 100;

    public const MIN_LIFE         = 0;
    public const MAX_LIFE         = 100000;

    public const MIN_TOTAL_LIFE   = 1;
    public const MAX_TOTAL_LIFE   = 100000;

    public const MIN_LEVEL        = 1;
    public const MAX_LEVEL        = 1000;

    // Помимо ограничения на символы, слишком длинное имя обрезается в css, на случай WWWWW-имен
    public const MIN_NAME_LENGTH  = 1;
    public const MAX_NAME_LENGTH  = 20;

    public const MIN_ID_LENGTH    = 1;
    public const MAX_ID_LENGTH    = 36;

    public const MIN_HIT_CHANCE   = 5;
    public const MAX_HIT_CHANCE   = 95;

    /**
     * UUID or random string
     *
     * @return string
     */
    public function getId(): string;

    /**
     * Имя юнита
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Уровень юнита
     *
     * @return int
     */
    public function getLevel(): int;

    /**
     * URL к картинке-аватару юнита
     *
     * @return string
     */
    public function getAvatar(): string;

    /**
     * Универсальный метод, через который мы запрашиваем "юнит, дай свое действие в текущем состоянии"
     *
     * Это может быть обычный удар, или использование способности, или ничего (если он, например, оглушен)
     *
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $alliesCommand
     * @return ActionCollection
     */
    public function getAction(CommandInterface $enemyCommand, CommandInterface $alliesCommand): ActionCollection;

    /**
     * Универсальный метод, через который мы применяем действие к юниту. Какое бы это действие не было - удар, лечение,
     * применение эффекта или что-то другое
     *
     * @param ActionInterface $action
     */
    public function applyAction(ActionInterface $action): void;

    /**
     * Возвращает коллекцию событий, которые необходимо применить перед ходом данного юнита в текущем раунде
     *
     * Например, юнит имеет эффект постепенного лечения (или урона) - значит, перед ходом этого юнита нужно применить к
     * нему эффект лечения или урона.
     *
     * @return ActionCollection
     */
    public function getOnNewRoundActions(): ActionCollection;

    /**
     * Совершал ли юнит действие в этом раунде
     *
     * @return bool
     */
    public function isAction(): bool;

    /**
     * Живой ли юнит
     *
     * @return bool
     */
    public function isAlive(): bool;

    /**
     * Указывает, что юнит сделал свой ход в этом раунде
     */
    public function madeAction(): void;

    /**
     * Сообщает юниту о том, что начался новый раунд (нужно обновить isAction, применить эффекты и сделать прочие
     * действия для нового раунда)
     */
    public function newRound(): void;

    /**
     * Является ли юнит бойцом ближнего боя
     *
     * @return bool
     */
    public function isMelee(): bool;

    /**
     * Возвращает скорость атаки юнита
     *
     * @return float
     */
    public function getAttackSpeed(): float;

    /**
     * Возвращает меткость юнита. Влияет на шанс попадания по цели атаками (с оружия)
     *
     * @return int
     */
    public function getAccuracy(): int;

    /**
     * Возвращает защиту юнита. Влияет на шанс уклониться от вражеской атаки (с оружия)
     *
     * @return int
     */
    public function getDefense(): int;

    /**
     * Возвращает шанс блока вражеских атак юнита
     *
     * @return int
     */
    public function getBlock(): int;

    /**
     * Возвращает значение игнорирования блока цели. Необходимо для реализации механик, когда, например, определенное
     * оружие может игнорировать блок цели. Для полного игнорирования блока цели необходимо вернуть 100. Хотя можно
     * и вернуть другое значение, например 10, и тогда шанс блока целью в 25% будет уменьшен до шанса в 15%
     *
     * @return int
     */
    public function getBlockIgnore(): int;

    /**
     * Возвращает текущее здоровье юнита
     *
     * @return int
     */
    public function getLife(): int;

    /**
     * Возвращает максимальное здоровье юнита
     *
     * @return int
     */
    public function getTotalLife(): int;

    /**
     * Возвращает количество концентрации юнита
     *
     * @return int
     */
    public function getConcentration(): int;

    /**
     * Возвращает количество ярости юнита
     *
     * @return int
     */
    public function getRage(): int;

    /**
     * Возвращает класс юнита, или null, если класса нет
     *
     * @return UnitClassInterface|null
     */
    public function getClass(): ?UnitClassInterface;

    /**
     * Возвращает расу юнита
     *
     * @return RaceInterface
     */
    public function getRace(): RaceInterface;

    /**
     * Возвращает урон юнита
     *
     * @return int
     */
    public function getDamage(): int;

    /**
     * Возвращает ДПС юнита (средний урон за ход = урон * скорость атаки)
     *
     * @return float
     */
    public function getDPS(): float;

    /**
     * Сообщает юниту, что была использована его способность завязанная на концентрацию
     *
     * Юнит, в свою очередь обнуляет свою концентрацию
     */
    public function useConcentrationAbility(): void;

    /**
     * Аналогично, только с яростью
     */
    public function useRageAbility(): void;

    /**
     * Возвращает путь к иконке юнита
     *
     * Если есть класс - берется иконка класса, если её нет - берется иконка расы
     *
     * @return string
     */
    public function getIcon(): string;

    /**
     * Возвращает номер команды: 1 - левая команда, 2 - правая команда
     *
     * Этот параметр необходим, для корректного отображения анимации в бою
     *
     * @return int
     */
    public function getCommand(): int;

    /**
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface;

    /**
     * Возвращает коллекцию способностей юнита
     *
     * @return AbilityCollection
     */
    public function getAbilities(): AbilityCollection;

    /**
     * Возвращает текущую коллекцию эффектов на юните
     *
     * @return EffectCollection
     */
    public function getEffects(): EffectCollection;
}
