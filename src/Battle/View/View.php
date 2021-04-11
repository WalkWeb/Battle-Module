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
     * @param string $resultTemplate
     * @param string $rowTemplate
     * @param string $unitTemplate
     */
    public function __construct(string $templateDir, string $resultTemplate, string $rowTemplate, string $unitTemplate)
    {
        $this->templateDir = $templateDir;
        $this->resultTemplate = $resultTemplate;
        $this->rowTemplate = $rowTemplate;
        $this->unitTemplate = $unitTemplate;
    }

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
