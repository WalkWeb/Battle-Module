<?php

declare(strict_types=1);

namespace Battle\Unit;

use Battle\Action\ActionCollection;
use Battle\Action\ActionInterface;
use Battle\Classes\UnitClassInterface;
use Battle\Command\CommandInterface;
use Battle\Container\ContainerInterface;
use Battle\Unit\Ability\AbilityCollection;
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
    public const ADD_CON_NEW_ROUND       = 200;
    public const ADD_CON_ACTION_UNIT     = 180;
    public const ADD_CON_RECEIVING_UNIT  = 100;
    public const ADD_RAGE_NEW_ROUND      = 50;
    public const ADD_RAGE_ACTION_UNIT    = 90;
    public const ADD_RAGE_RECEIVING_UNIT = 70;
    public const MAX_CONS                = 1000;
    public const MAX_RAGE                = 1000;

    public const MIN_DAMAGE         = 0;
    public const MAX_DAMAGE         = 100000;

    public const MIN_ATTACK_SPEED   = 0.0;
    public const MAX_ATTACK_SPEED   = 10;

    public const MIN_LIFE           = 0;
    public const MAX_LIFE           = 100000;

    public const MIN_TOTAL_LIFE     = 1;
    public const MAX_TOTAL_LIFE     = 100000;

    public const MIN_LEVEL         = 1;
    public const MAX_LEVEL         = 1000;

    // Помимо ограничения на символы, слишком длинное имя обрезается в css
    public const MIN_NAME_LENGTH    = 1;
    public const MAX_NAME_LENGTH    = 20;

    public const MIN_ID_LENGTH    = 1;
    public const MAX_ID_LENGTH    = 36;

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
     * Урон юнита
     *
     * @return int
     */
    public function getDamage(): int;

    // TODO 4 метода ниже перестанут нужны, если добавить проверку на возможность использования способности перед
    // TODO её использованием

    /**
     * Если способность не была применена - нужно сообщить юзеру, чтобы он вновь получил максимальную концентрацию и
     * попробовал использовать способность в следующем ходу
     */
    public function upMaxConcentration(): void;

    /**
     * Аналогично
     */
    public function upMaxRage(): void;

    /**
     * Сообщает юниту, что была использована его способность завязанная на концентрацию
     *
     * Юнит, в свою очередь обнуляет свою концентрацию
     */
    public function useConcentrationAbility(): void;

    /**
     * Аналогично
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
     * @return AbilityCollection
     */
    public function getAbilities(): AbilityCollection;
}
