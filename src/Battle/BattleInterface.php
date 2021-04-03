<?php

namespace Battle;

use Battle\Result\ResultInterface;

interface BattleInterface
{
    public const COMMAND_PARAMETER = 'command';
    public const LEFT_COMMAND      = 'left';
    public const RIGHT_COMMAND     = 'right';

    /**
     * Обрабатывает бой, возвращая результат выполнения
     *
     * @return ResultInterface
     */
    public function handle(): ResultInterface;
}
