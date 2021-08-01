<?php

declare(strict_types=1);

namespace Battle\Round;

use Battle\Container\ContainerInterface;
use Battle\Command\CommandInterface;

class RoundFactory
{
    /**
     * Создает раунд на основе переданных данных
     *
     * Хоть текущая фабрика и проста, она позволяет абстрагироваться в самом Battle от конкретной реализации
     *
     * @param CommandInterface $leftCommand
     * @param CommandInterface $rightCommand
     * @param int $actionCommand
     * @param ContainerInterface $container
     * @return RoundInterface
     * @throws RoundException
     */
    public function create(
        CommandInterface $leftCommand,
        CommandInterface $rightCommand,
        int $actionCommand,
        ContainerInterface $container
    ): RoundInterface
    {
        return new Round(
            $leftCommand,
            $rightCommand,
            $actionCommand,
            $container
        );
    }
}
