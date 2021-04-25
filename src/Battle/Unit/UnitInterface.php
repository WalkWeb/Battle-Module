<?php

declare(strict_types=1);

namespace Battle\Unit;

use Battle\Action\ActionCollection;
use Battle\Action\ActionInterface;
use Battle\Classes\UnitClassInterface;
use Battle\Command\CommandInterface;
use Battle\Effect\EffectCollection;

/**
 * Одна боевая единица.
 *
 * Это может быть персонаж, монстр, босс или npc - не важно
 *
 * @package Battle\Unit
 */
interface UnitInterface
{
    public const ADD_CON_NEW_ROUND      = 200;
    public const ADD_CON_ACTION_UNIT    = 180;
    public const ADD_CON_RECEIVING_UNIT = 100;
    public const MAX_CONS               = 1000;

    public const MIN_DAMAGE         = 0;
    public const MAX_DAMAGE         = 100000;

    public const MIN_ATTACK_SPEED   = 0.1;
    public const MAX_ATTACK_SPEED   = 10;

    public const MIN_LIFE           = 0;
    public const MAX_LIFE           = 100000;

    public const MIN_TOTAL_LIFE     = 1;
    public const MAX_TOTAL_LIFE     = 100000;

    // Помимо ограничения на символы, слишком длинное имя обрезается в css
    public const MIN_NAME_LENGTH    = 1;
    public const MAX_NAME_LENGTH    = 20;

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
     * @return string
     */
    public function applyAction(ActionInterface $action): string;

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
     * Возвращает класс юнита
     *
     * @return UnitClassInterface
     */
    public function getClass(): UnitClassInterface;

    /**
     * Возвращает эффекты на юните
     *
     * @return EffectCollection
     */
    public function getEffects(): EffectCollection;

    /**
     * Урон юнита
     *
     * @return int
     */
    public function getDamage(): int;
}
