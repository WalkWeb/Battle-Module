<?php

declare(strict_types=1);

namespace Battle\View;

use Battle\Command\CommandInterface;
use Battle\Unit\UnitInterface;

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

    /**
     * @param UnitInterface $unit
     * @return string
     */
    public function getUnitView(UnitInterface $unit): string;
}
