<?php

declare(strict_types=1);

namespace Battle\Command;

use Battle\Unit\UnitCollection;
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
     * Возвращает юнита готового совершать действие в этом раунде
     *
     * @return UnitInterface|null
     */
    public function getUnitForAction(): ?UnitInterface;

    /**
     * Возвращает коллекцию юнитов данной команды
     *
     * @return UnitCollection
     */
    public function getUnits(): UnitCollection;

    /**
     * Возвращает коллекцию юнитов ближнего боя в команде
     *
     * todo array => UnitCollection
     *
     * @return array
     */
    public function getMeleeUnits(): array;

    /**
     * Возвращает коллекцию юнитов дальнего боя в команде
     *
     *  todo array => UnitCollection
     *
     * @return array
     */
    public function getRangeUnits(): array;

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
}
