<?php

declare(strict_types=1);

namespace Battle\View;

use Battle\Command\CommandInterface;
use Battle\Result\ResultInterface;
use Battle\Unit\UnitInterface;

interface ViewInterface
{
    /**
     * Генерирует html-код для отображения результата боя
     *
     * @param ResultInterface $result
     * @return string
     */
    public function renderResult(ResultInterface $result): string;

    /**
     * Генерирует html-код для отображения текущего состояния сражающихся команд
     *
     * @param CommandInterface $leftCommand
     * @param CommandInterface $rightCommand
     * @return string
     */
    public function renderCommandView(CommandInterface $leftCommand, CommandInterface $rightCommand): string;

    /**
     * Генерирует html-код для отображения юнита
     *
     * @param UnitInterface $unit
     * @return string
     */
    public function getUnitView(UnitInterface $unit): string;
}
