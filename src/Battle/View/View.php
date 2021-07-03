<?php

declare(strict_types=1);

namespace Battle\View;

use Battle\Command\CommandInterface;
use Battle\Result\ResultInterface;
use Battle\Translation\Translation;
use Battle\Unit\UnitException;
use Battle\Unit\UnitInterface;

// TODO Проверка на наличие файлов шаблонов

/**
 * @package Battle\View
 */
class View implements ViewInterface
{
    /**
     * @var string
     */
    private $templateDir;

    /**
     * @var Translation
     */
    private $translation;

    /**
     * @var string
     */
    private $headTemplate;

    /**
     * @var string
     */
    private $resultTemplate;

    /**
     * @var string
     */
    private $rowTemplate;

    /**
     * @var string
     */
    private $unitTemplate;

    /**
     * @var string
     */
    private $unitFullLogTemplate;

    /**
     * @var string
     */
    private $unitsStatsTemplate;

    /**
     * @param Translation $translation
     * @param string $templateDir
     * @param string $headTemplate
     * @param string $resultTemplate
     * @param string $rowTemplate
     * @param string $unitTemplate
     * @param string $unitFullLogTemplate
     * @param string $unitsStatsTemplate
     */
    public function __construct(
        Translation $translation,
        string $templateDir,
        string $headTemplate,
        string $resultTemplate,
        string $rowTemplate,
        string $unitTemplate,
        string $unitFullLogTemplate,
        string $unitsStatsTemplate
    )
    {
        $this->translation = $translation;
        $this->templateDir = $templateDir;
        $this->headTemplate = $headTemplate;
        $this->resultTemplate = $resultTemplate;
        $this->rowTemplate = $rowTemplate;
        $this->unitFullLogTemplate = $unitFullLogTemplate;
        $this->unitTemplate = $unitTemplate;

        $this->unitsStatsTemplate = $unitsStatsTemplate;
    }

    /**
     * Генерирует html-код для отображения <head> страницы. Этот код нужен для демонстрации, но для генерации кода
     * внутри существующего проекта он будет лишний
     *
     * @return string
     */
    public function renderHead(): string
    {
        ob_start();

        require $this->templateDir . $this->headTemplate;

        return ob_get_clean();
    }

    /**
     * Генерирует html-код для отображения результата боя
     *
     * @param ResultInterface $result
     * @return string
     */
    public function renderResult(ResultInterface $result): string
    {
        ob_start();

        require $this->templateDir . $this->resultTemplate;

        return ob_get_clean();
    }

    /**
     * Формирует html-код для отображения текущего состояния сражающихся команд
     *
     * @param CommandInterface $leftCommand
     * @param CommandInterface $rightCommand
     * @param bool $fullLog
     * @return string
     * @throws UnitException
     */
    public function renderCommandView(CommandInterface $leftCommand, CommandInterface $rightCommand, bool $fullLog = false): string
    {
        $leftMeleeUnits = '';
        $leftRangeUnits = '';
        $rightMeleeUnits = '';
        $rightRangeUnits = '';

        foreach ($leftCommand->getMeleeUnits() as $unit) {
            $leftMeleeUnits .= $fullLog ? $this->getStatUnitView($unit) : $this->getUnitView($unit);
        }

        foreach ($leftCommand->getRangeUnits() as $unit) {
            $leftRangeUnits .= $fullLog ? $this->getStatUnitView($unit) : $this->getUnitView($unit);
        }

        foreach ($rightCommand->getMeleeUnits() as $unit) {
            $rightMeleeUnits .= $fullLog ? $this->getStatUnitView($unit) : $this->getUnitView($unit);
        }

        foreach ($rightCommand->getRangeUnits() as $unit) {
            $rightRangeUnits .= $fullLog ? $this->getStatUnitView($unit) : $this->getUnitView($unit);
        }

        ob_start();

        require $this->templateDir . $this->rowTemplate;

        return ob_get_clean();
    }

    /**
     * Генерирует html-код для отображения юнита
     *
     * @uses getWidth, getBgClass
     * @param UnitInterface $unit
     * @return string
     */
    public function getUnitView(UnitInterface $unit): string
    {
        ob_start();

        require $this->templateDir . $this->unitTemplate;

        return ob_get_clean();
    }

    /**
     * @uses getWidth, getBgClass
     * @param UnitInterface $unit
     * @return string
     */
    public function getStatUnitView(UnitInterface $unit): string
    {
        ob_start();

        require $this->templateDir . $this->unitFullLogTemplate;

        return ob_get_clean();
    }

    /**
     * Генерирует html-код для отображения таблицы характеристик юнитов
     *
     * @param CommandInterface $leftCommand
     * @param CommandInterface $rightCommand
     * @return string
     */
    public function getUnitsStats(CommandInterface $leftCommand, CommandInterface $rightCommand): string
    {
        ob_start();

        require $this->templateDir . $this->unitsStatsTemplate;

        return ob_get_clean();
    }

    /**
     * @return Translation
     */
    public function getTranslation(): Translation
    {
        return $this->translation;
    }

    /**
     * @param int $value
     * @param int $max
     * @return int
     */
    private function getWidth(int $value, int $max): int
    {
        return (int)($value / $max * 100);
    }
}
