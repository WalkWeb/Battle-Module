<?php

declare(strict_types=1);

namespace Battle\Unit;

use Battle\Action\ActionCollection;
use Battle\Action\ActionInterface;
use Battle\Unit\Classes\UnitClassInterface;
use Battle\Command\CommandInterface;
use Battle\Container\ContainerInterface;
use Battle\Unit\Ability\AbilityCollection;
use Battle\Unit\Defense\DefenseInterface;
use Battle\Unit\Effect\EffectCollection;
use Battle\Unit\Offense\OffenseInterface;
use Battle\Unit\Race\RaceInterface;
use Exception;

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
    public const MAX_CONCENTRATION       = 1000;
    public const MAX_RAGE                = 1000;

    // Максимально/минимальные множители получаемой концентрации/хитрости/ярости
    public const MAX_RESOURCE_MULTIPLIER = 1000;
    public const MIN_RESOURCE_MULTIPLIER = -100;

    public const MIN_LIFE         = 0;
    public const MAX_LIFE         = 100000;

    public const MIN_TOTAL_LIFE   = 1;
    public const MAX_TOTAL_LIFE   = 100000;

    public const MIN_MANA         = 0;
    public const MAX_MANA         = 100000;

    public const MIN_TOTAL_MANA   = 0;
    public const MAX_TOTAL_MANA   = 100000;

    public const MIN_LEVEL        = 1;
    public const MAX_LEVEL        = 1000;

    // Помимо ограничения на символы, слишком длинное имя обрезается в css, на случай WWWWW-имен
    public const MIN_NAME_LENGTH  = 1;
    public const MAX_NAME_LENGTH  = 20;

    public const MIN_ID_LENGTH    = 1;
    public const MAX_ID_LENGTH    = 36;

    public const MIN_HIT_CHANCE   = 5;
    public const MAX_HIT_CHANCE   = 95;

    public const BASE_CUNNING     = 15;

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
    public function getActions(CommandInterface $enemyCommand, CommandInterface $alliesCommand): ActionCollection;

    /**
     * Возвращает коллекцию событий, которые необходимо выполнить перед тем, как юнит совершит свои действия, т.е.
     * перед запросом getActions()
     *
     * Это может быть, например, урон или лечение от эффекта
     *
     * @return ActionCollection
     */
    public function getBeforeActions(): ActionCollection;

    /**
     * Возвращает коллекцию событий, которые необходимо выполнить после того, как юнит совершил свои действия, т.е.
     * после запроса getActions()
     *
     * Это могут быть, к примеру, события по завершению эффектов
     *
     * Они (события окончания эффектов) должны выполняться после хода юнита, а не при завершении раунда (как может
     * показаться логичным), для корректного подсчета длительности эффектов
     *
     * Например, юнит получает оглушение на 1 ход после того, как походил в этом раунде. Если эффекты заканчиваются при
     * окончании раунда, то эффект закончится, и в следующем ходу юнит будет ходить как ни в чем не бывало - т.е.
     * эффекта оглушения как будто и не было.
     *
     * А если длительность эффекта привязана к ходу юнита - то оглушение не закончится, пока юнит не сделает попытку
     * совершить свой ход
     *
     * @return ActionCollection
     */
    public function getAfterActions(): ActionCollection;

    /**
     * Универсальный метод, через который мы применяем действие к юниту. Какое бы это действие не было - удар, лечение,
     * применение эффекта или что-то другое
     *
     * Возвращает коллекцию событий, которые стали следствием воздействия на юнита: это может быть особый эффект от
     * оружия, рефлект урона или что-то другое. Подразумевается, что полученная коллекция событий будет сразу же
     * выполнена
     *
     * @param ActionInterface $action
     * @return ActionCollection
     */
    public function applyAction(ActionInterface $action): ActionCollection;

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
     *
     * @throws Exception
     */
    public function newRound(): void;

    /**
     * Является ли юнит бойцом ближнего боя
     *
     * @return bool
     */
    public function isMelee(): bool;

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
     * Возвращает текущую ману юнита
     *
     * @return int
     */
    public function getMana(): int;

    /**
     * Возвращает максимальную ману юнита
     *
     * @return int
     */
    public function getTotalMana(): int;

    /**
     * Возвращает количество концентрации юнита
     *
     * @return int
     */
    public function getConcentration(): int;

    /**
     * Возвращает хитрость юнита
     *
     * @return int
     */
    public function getCunning(): int;

    /**
     * Возвращает количество ярости юнита
     *
     * @return int
     */
    public function getRage(): int;

    /**
     * Возвращает множитель получаемой концентрации. Указывается в процентах (20 => +20%, -30 => -30%)
     *
     * @return int
     */
    public function getAddConcentrationMultiplier(): int;

    /**
     * Устанавливает новое значение множителя получаемой концентрации
     *
     * @param int $addConcentrationMultiplier
     */
    public function setAddConcentrationMultiplier(int $addConcentrationMultiplier): void;

    /**
     * Возвращает множитель хитрости. Указывается в процентах (20 => +20%, -30 => -30%)
     *
     * @return int
     */
    public function getCunningMultiplier(): int;

    /**
     * Возвращает множитель получаемой ярости. Указывается в процентах (20 => +20%, -30 => -30%)
     *
     * @return int
     */
    public function getAddRageMultiplier(): int;

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

    /**
     * Возвращает атакующие характеристики
     *
     * @return OffenseInterface
     */
    public function getOffense(): OffenseInterface;

    /**
     * Возвращает защитные характеристики
     *
     * @return DefenseInterface
     */
    public function getDefense(): DefenseInterface;

    /**
     * Возвращает особые способности юнита, которые срабатывают только при его смерти, если они есть и готовы к
     * использованию
     *
     * TODO Когда будет реализована более сложная механика ответных событий при получении события юнитом - этот метод удалится
     *
     * @return AbilityCollection
     */
    public function getDeadAbilities(): AbilityCollection;

    /**
     * Обездвижен (например, находится под эффектом паралича или оглушения) ли юнит в текущий момент
     *
     * @return bool
     */
    public function isParalysis(): bool;

    /**
     * Возвращает коллекцию последних целей (в этом раунде) юнита. На данный момент относится только к
     * атакованным/вылеченным целям
     *
     * Используется в механике, когда, например, на атакованную или вылеченную цель, следующем событием нужно наложить
     * какой-то эффект
     *
     * @return UnitCollection
     */
    public function getLastTargets(): UnitCollection;

    /**
     * Добавляет новую последнюю цель текущего юнита
     *
     * @param UnitInterface $target
     */
    public function addLastTarget(UnitInterface $target): void;

    /**
     * Очищает коллекцию последних целей. Вызывается при каждом новом раунде
     */
    public function clearLastTarget(): void;
}
