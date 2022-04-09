<?php

declare(strict_types=1);

namespace Battle\View;

use Battle\Command\CommandInterface;
use Battle\Result\ResultInterface;
use Battle\Translation\Translation;
use Battle\Unit\UnitException;
use Battle\Unit\UnitInterface;

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
     * @throws ViewException
     */
    public function renderHead(): string
    {
        $filePath = $this->templateDir . $this->headTemplate;

        $this->checkExistTemplate($filePath, 'Head Template');

        ob_start();

        require $filePath;

        return ob_get_clean();
    }

    /**
     * Генерирует html-код для отображения результата боя
     *
     * @param ResultInterface $result
     * @return string
     * @throws ViewException
     */
    public function renderResult(ResultInterface $result): string
    {
        $filePath = $this->templateDir . $this->resultTemplate;

        $this->checkExistTemplate($filePath, 'Result Template');

        ob_start();

        require $filePath;

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
     * @throws ViewException
     */
    public function renderCommandView(CommandInterface $leftCommand, CommandInterface $rightCommand, bool $fullLog = false): string
    {
        $filePath = $this->templateDir . $this->rowTemplate;

        $this->checkExistTemplate($filePath, 'Row Template');

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

        require $filePath;

        return ob_get_clean();
    }

    /**
     * Генерирует html-код для отображения юнита
     *
     * @param UnitInterface $unit
     * @return string
     * @throws ViewException
     * @uses getWidth, getBgClass
     */
    public function getUnitView(UnitInterface $unit): string
    {
        $filePath = $this->templateDir . $this->unitTemplate;

        $this->checkExistTemplate($filePath, 'Unit Template');

        ob_start();

        require $filePath;

        return ob_get_clean();
    }

    /**
     * @param UnitInterface $unit
     * @return string
     * @throws ViewException
     * @uses getWidth, getBgClass
     */
    public function getStatUnitView(UnitInterface $unit): string
    {
        $filePath = $this->templateDir . $this->unitFullLogTemplate;

        $this->checkExistTemplate($filePath, 'Unit Full Log Template');

        ob_start();

        require $filePath;

        return ob_get_clean();
    }

    /**
     * Генерирует html-код для отображения таблицы характеристик юнитов
     *
     * @param CommandInterface $leftCommand
     * @param CommandInterface $rightCommand
     * @return string
     * @throws ViewException
     */
    public function getUnitsStats(CommandInterface $leftCommand, CommandInterface $rightCommand): string
    {
        $filePath = $this->templateDir . $this->unitsStatsTemplate;

        $this->checkExistTemplate($filePath, 'Units Stats Template');

        ob_start();

        require $filePath;

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

    /**
     * Проверяет существование указанного файла
     *
     * @param string $filePath
     * @param string $nameFile
     * @throws ViewException
     */
    private function checkExistTemplate(string $filePath, string $nameFile): void
    {
        if (!file_exists($filePath)) {
            throw new ViewException(ViewException::MISSING_TEMPLATE . ': ' . $nameFile);
        }
    }

    /**
     * Длительность эффекта отображается только в том случае, если она меньше 10. Это сделано для того, чтобы большая
     * цифра длительности не выходила за рамки иконки эффекта
     *
     * @param UnitInterface $unit
     * @return string
     */
    private function getEffects(UnitInterface $unit): string
    {
        $html = '';

        foreach ($unit->getEffects() as $effect) {
            $duration = $effect->getDuration() < 10 ? (string)$effect->getDuration() : '';
            $html .=
                '<div class="unit_effect_icon" style="background-image: url(' . $effect->getIcon() . ')"><div>' .
                    $duration .
                '</div></div>';
        }

        return $html;
    }
}
