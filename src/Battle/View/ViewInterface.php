<?php

declare(strict_types=1);

namespace Battle\View;

use Battle\Command\CommandInterface;
use Battle\Response\ResponseInterface;
use Battle\Translation\Translation;
use Battle\Unit\UnitInterface;

interface ViewInterface
{
    /**
     * Генерирует html-код для отображения <head> страницы. Этот код нужен только для демонстрации боя в рамках данного
     * репозитория. В реальном проекте использоваться не должен.
     *
     * @return string
     */
    public function renderHead(): string;

    /**
     * Генерирует html-код для отображения результата боя
     *
     * @param ResponseInterface $response
     * @return string
     */
    public function renderBattle(ResponseInterface $response): string;

    /**
     * Генерирует html-код для отображения текущего состояния сражающихся команд
     *
     * @param CommandInterface $leftCommand
     * @param CommandInterface $rightCommand
     * @param bool $fullLog
     * @return string
     */
    public function renderCommandView(CommandInterface $leftCommand, CommandInterface $rightCommand, bool $fullLog = false): string;

    /**
     * Генерирует html-код для отображения юнита
     *
     * @param UnitInterface $unit
     * @return string
     */
    public function getUnitView(UnitInterface $unit): string;

    /**
     * Генерирует html-код для отображения таблицы характеристик юнитов
     *
     * @param CommandInterface $leftCommand
     * @param CommandInterface $rightCommand
     * @return string
     */
    public function getUnitsStats(CommandInterface $leftCommand, CommandInterface $rightCommand): string;

    /**
     * Возвращает объект отвечающий за мультиязычность
     *
     * @return Translation
     */
    public function getTranslation(): Translation;
}
