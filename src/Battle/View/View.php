<?php

declare(strict_types=1);

namespace Battle\View;

use Battle\Command\CommandInterface;
use Battle\Unit\UnitInterface;

/**
 * TODO Подумать, как можно вынести конкретный html код в отдельные шаблоны
 *
 * @package Battle\View
 */
class View implements ViewInterface
{
    /**
     * Генерирует html-код для отображения текущего состояния сражающихся команд
     *
     * @param CommandInterface $leftCommand
     * @param CommandInterface $rightCommand
     * @return string
     */
    public function render(CommandInterface $leftCommand, CommandInterface $rightCommand): string
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

        return '<div class="row">
                    <table>
                        <tr>
                            <td class="w25">' . $leftRangeUnits . '</td>
                            <td class="w25">' . $leftMeleeUnits . '</td>
                            <td class="w25">' . $rightMeleeUnits . '</td>
                            <td class="w25">' . $rightRangeUnits . '</td>
                        </tr>
                    </table>
                </div>';
    }

    /**
     * @param UnitInterface $unit
     * @return string
     */
    private function getUnitView(UnitInterface $unit): string
    {
        $action = $unit->isAction() ? 'yes' : 'no';
        $alive = $unit->isAlive() ? 'yes' : 'no';

        return '
        <div class="unit">
            <table>
                <tr>
                    <td rowspan="2" class="avatar_box" style="background-image: url(' . $unit->getAvatar() . ')"></td>
                    <td>Name</td>
                    <td>Damage</td>
                    <td>Life</td>
                    <td>Cons</td>
                    <td>Action?</td>
                    <td>Alive?</td>
                </tr>
                <tr>
                    <td>' . $unit->getName() . '</td>
                    <td>' . $unit->getDamage() . '</td>
                    <td class="life_bar">
                        <div class="life_bar">
                            <div class="life" style="width: ' . $this->getWidth($unit->getLife(), $unit->getTotalLife()) . '%;"></div>
                        </div>
                        <div class="life_text">
                            ' . $unit->getLife() . '/' . $unit->getTotalLife() . '
                        </div>
                    </td>
                    <td>' . $unit->getConcentration() . '/' . UnitInterface::MAX_CONS . '</td>
                    <td class="' . $this->getBgClass($unit->isAction()) . '">' . $action . '</td>
                    <td>' . $alive . '</td>
                </tr>
            </table>
        </div>';
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
