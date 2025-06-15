<?php

namespace Battle;

use Battle\Container\ContainerInterface;
use Battle\Response\ResponseInterface;

interface BattleInterface
{
    public const string COMMAND_PARAMETER = 'command';
    public const int LEFT_COMMAND         = 1;
    public const int RIGHT_COMMAND        = 2;

    /**
     * Обрабатывает бой, возвращая результат выполнения
     *
     * @return ResponseInterface
     */
    public function handle(): ResponseInterface;

    /**
     * Возвращает контейнер
     *
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface;
}
