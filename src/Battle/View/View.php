<?php

declare(strict_types=1);

namespace Battle\View;

use Battle\Command\CommandInterface;
use Battle\Result\ResultInterface;
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
     * @param string $templateDir
     * @param string $headTemplate
     * @param string $resultTemplate
     * @param string $rowTemplate
     * @param string $unitTemplate
     */
    public function __construct(
        string $templateDir,
        string $headTemplate,
        string $resultTemplate,
        string $rowTemplate,
        string $unitTemplate)
    {
        $this->templateDir = $templateDir;
        $this->headTemplate = $headTemplate;
        $this->resultTemplate = $resultTemplate;
        $this->rowTemplate = $rowTemplate;
        $this->unitTemplate = $unitTemplate;
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
     * @return string
     */
    public function renderCommandView(CommandInterface $leftCommand, CommandInterface $rightCommand): string
    {
        $leftMeleeUnits = '';
        $leftRangeUnits = '';
        $rightMeleeUnits = '';
        $rightRangeUnits = '';

        foreach ($leftCommand->getMeleeUnits() as $unit) {
            $leftMeleeUnits .= $this->getUnitView($unit);
        }

        foreach ($leftCommand->getRangeUnits() as $unit) {
            $leftRangeUnits .= $this->getUnitView($unit);
        }

        foreach ($rightCommand->getMeleeUnits() as $unit) {
            $rightMeleeUnits .= $this->getUnitView($unit);
        }

        foreach ($rightCommand->getRangeUnits() as $unit) {
            $rightRangeUnits .= $this->getUnitView($unit);
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
     * @param int $value
     * @param int $max
     * @return int
     */
    private function getWidth(int $value, int $max): int
    {
        return (int)($value / $max * 100);
    }

    /**
     * @param bool $value
     * @return string
     */
    private function getBgClass(bool $value): string
    {
        return $value ? 'c6' : 'c4';
    }
}
