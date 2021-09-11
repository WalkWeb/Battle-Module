<?php

namespace Battle\Unit\Effect;

use Battle\Action\ActionCollection;
use Battle\Action\ActionException;
use Battle\Unit\UnitInterface;

interface EffectInterface
{
    /**
     * Возвращает имя эффекта
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Возвращает url к иконке эффекта
     *
     * @return string
     */
    public function getIcon(): string;

    /**
     * Возвращает базовую длительность эффекта
     *
     * @return int
     */
    public function getBaseDuration(): int;

    /**
     * Возвращает текущую длительность эффекта
     *
     * @return int
     */
    public function getDuration(): int;

    /**
     * Сообщает эффекту о новом раунде (эффект уменьшает свою длительность на 1)
     */
    public function nextRound(): void;

    /**
     * Сбрасывает длительность эффекта на базовую
     */
    public function resetDuration(): void;

    /**
     * Возвращает коллекцию событий, которые необходимо применить к юниту при применении эффекта
     *
     * @return ActionCollection
     */
    public function getOnApplyActions(): ActionCollection;

    /**
     * Возвращает коллекцию событий, которые необходимо применить к юниту в каждом новом раунде
     *
     * @return ActionCollection
     */
    public function getOnNextRoundActions(): ActionCollection;

    /**
     * Возвращает коллекцию событий, которые необходимо применить к юниту при удалении этого эффекта
     *
     * @return ActionCollection
     * @throws ActionException
     */
    public function getOnDisableActions(): ActionCollection;

    /**
     * При применении эффекта необходимо изменить $actionUnit во всех Action, так как теперь они будут вызываться от
     * владельца эффекта.
     *
     * Сразу устанавливать в $actionUnit того, на кого эффект будет применен мы не можем, потому что в момент создания
     * эффекта это еще неизвестно. Это событие вообще может не наступить, если эффект накладывается в конце действия
     * другого эффекта, а юнит умер до того, как эффект закончился
     *
     * @param UnitInterface $unit
     */
    public function changeActionUnit(UnitInterface $unit): void;
}
