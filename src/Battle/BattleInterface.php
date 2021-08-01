<?php

namespace Battle;

use Battle\Container\ContainerInterface;
use Battle\Result\ResultInterface;

interface BattleInterface
{
    public const COMMAND_PARAMETER = 'command';
    public const LEFT_COMMAND      = 1;
    public const RIGHT_COMMAND     = 2;

    /**
     * Обрабатывает бой, возвращая результат выполнения
     *
     * @return ResultInterface
     */
    public function handle(): ResultInterface;

    /**
     * Возвращает контейнер
     *
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface;
}
