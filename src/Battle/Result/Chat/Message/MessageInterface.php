<?php

declare(strict_types=1);

namespace Battle\Result\Chat\Message;

use Battle\Action\DamageAction;
use Battle\Action\HealAction;
use Battle\Action\SummonAction;
use Battle\Action\WaitAction;

interface MessageInterface
{
    /**
     * Формирует и возвращает сообщение для чата о нанесении урона
     *
     * @param DamageAction $action
     * @return string
     */
    public function damage(DamageAction $action): string;

    /**
     * Формирует и возвращает сообщение для чата о лечении
     *
     * @param HealAction $action
     * @return string
     */
    public function heal(HealAction $action): string;

    /**
     * Формирует и возвращает сообщение для чата о призыве нового существа
     *
     * @param SummonAction $action
     * @return string
     */
    public function summon(SummonAction $action): string;

    /**
     * Формирует и возвращает сообщение для чата о пропуске хода
     *
     * @param WaitAction $action
     * @return string
     */
    public function wait(WaitAction $action): string;
}
