<?php

declare(strict_types=1);

namespace Battle\Unit;

use Battle\Action\ActionCollection;
use Battle\Action\ActionInterface;
use Battle\Classes\UnitClassInterface;
use Battle\Command\CommandInterface;
use Battle\Effect\Effect;

/**
 * Одна боевая единица.
 *
 * Это может быть персонаж, монстр, босс или npc - не важно
 *
 * @package Battle\Unit
 */
interface UnitInterface
{
    public const NEW_ROUND_ADD_CONS = 500;
    public const MAX_CONS = 1000;

    /**
     * Имя юнита
     *
     * @return string
     */
    public function getName(): string;

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
     * Возвращает урон юнита
     *
     * TODO Переделать на DamageInterface - который в свою очередь будет содержать все параметры удара
     *
     * @return int
     */
    public function getDamage(): int;

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
     * TODO На удаление, действие у юнита получается только через getAction()
     *
     * @param CommandInterface $defendingCommand
     * @return ActionCollection
     */
    public function getDamageAction(CommandInterface $defendingCommand): ActionCollection;

    /**
     * TODO На удаление, действие у юнита получается только через getAction()
     *
     * @param CommandInterface $alliesCommand
     * @return ActionCollection
     */
    public function getHealAction(CommandInterface $alliesCommand): ActionCollection;

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
     * Добавляет эффект юниту
     *
     * TODO Метод должен быть приватным, добавление эффекта напрямую запрещено, только через applyAction()
     *
     * @param Effect $effect
     */
    public function addEffect(Effect $effect): void;

    /**
     * Возвращает эффекты на юните
     *
     * TODO Должен возвращать не массив а EffectCollection
     *
     * @return array
     */
    public function getEffects(): array;
}