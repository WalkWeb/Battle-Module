<?php

declare(strict_types=1);

namespace Battle\Command;

use Battle\Unit\Effect\EffectInterface;
use Battle\Unit\UnitCollection;
use Battle\Unit\UnitException;
use Battle\Unit\UnitInterface;

interface CommandInterface
{
    /**
     * Имеются ли в команде живые юниты
     *
     * @return bool
     */
    public function isAlive(): bool;

    /**
     * Готова ли команда ходить
     *
     * Если в команде есть юниты, которые живые и еще не ходили в этом раунде - вернет true
     * Если все живые походили - вернет false
     *
     * @return bool
     */
    public function isAction(): bool;

    /**
     * Возвращает случайного живого юнита для атаки, если есть
     *
     * @return UnitInterface|null
     */
    public function getUnitForAttacks(): ?UnitInterface;

    /**
     * Возвращает случайного живого юнита ближнего боя для атаки, есть есть
     *
     * @return UnitInterface|null
     */
    public function getMeleeUnitForAttacks(): ?UnitInterface;

    /**
     * Возвращает самого раненого живого юнита в команде, если все живы или мертвы - возвращает null
     *
     * @return UnitInterface|null
     */
    public function getUnitForHeal(): ?UnitInterface;

    /**
     * Возвращает самого раненого живого юнита в команде не имеющего указанного эффекта
     *
     * Если все живы или мертвы - возвращает null
     *
     * @param EffectInterface $effect
     * @return UnitInterface|null
     */
    public function getUnitForEffectHeal(EffectInterface $effect): ?UnitInterface;

    /**
     * Возвращает юнита готового совершать действие в этом раунде
     *
     * @return UnitInterface|null
     */
    public function getUnitForAction(): ?UnitInterface;

    /**
     * Возвращает случайного юнита не имеющего указанного эффекта
     *
     * Если живых юнитов нет, или все, что есть, имеют указанный эффект - возвращает null
     *
     * @param EffectInterface $effect
     * @return UnitInterface|null
     */
    public function getUnitForEffect(EffectInterface $effect): ?UnitInterface;

    /**
     * Возвращает случайного мертвого юнита в команде
     *
     * Если все живы - возвращает null
     *
     * @return UnitInterface|null
     */
    public function getUnitForResurrection():? UnitInterface;

    /**
     * Возвращает всех живых юнитов в команде
     *
     * @return UnitCollection
     */
    public function getAllAliveUnits(): UnitCollection;

    /**
     * Возвращает всех раненых (но живых) юнитов в команде
     *
     * @return UnitCollection
     */
    public function getAllWoundedUnits(): UnitCollection;

    /**
     * Возвращает коллекцию юнитов данной команды
     *
     * @return UnitCollection
     */
    public function getUnits(): UnitCollection;

    /**
     * Возвращает коллекцию юнитов ближнего боя в команде
     *
     * @return UnitCollection
     * @throws UnitException
     */
    public function getMeleeUnits(): UnitCollection;

    /**
     * Возвращает коллекцию юнитов дальнего боя в команде
     *
     * @return UnitCollection
     * @throws UnitException
     */
    public function getRangeUnits(): UnitCollection;

    /**
     * Есть ли живые юниты ближнего боя в команде
     *
     * @return bool
     */
    public function existMeleeUnits(): bool;

    /**
     * Сообщает команде о начале нового раунда
     *
     * У всех юнитов action будет переведен в false
     * А также будут сделаны другие действия, соответствующие началу нового раунда
     */
    public function newRound(): void;

    /**
     * Возвращает суммарное значение текущего здоровья у юнитов в команде
     *
     * Используется для расчета победителя, когда лимит по раундам превышен, но обе команды живы - тогда победитель
     * определяется по наибольшему оставшемся здоровью
     *
     * @return int
     */
    public function getTotalLife(): int;

    /**
     * Клонирование команды должно также клонировать всех юнитов в команде
     *
     * @throws UnitException
     * @return mixed
     */
    public function __clone();
}
