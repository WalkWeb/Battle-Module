<?php

declare(strict_types=1);

namespace Battle\View;

use Battle\Command\CommandInterface;

interface ViewInterface
{
    /**
     * Генерирует html-код для отображения текущего состояния сражающихся команд
     *
     * @param CommandInterface $leftCommand
     * @param CommandInterface $rightCommand
     * @return string
     */
    public function render(CommandInterface $leftCommand, CommandInterface $rightCommand): string;
}
