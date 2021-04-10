<?php

declare(strict_types=1);

namespace Tests\Battle\View;

use Battle\Classes\ClassFactoryException;
use Battle\Command\CommandException;
use Battle\Command\CommandFactory;
use Battle\Unit\UnitException;
use Battle\View\ViewFactory;
use PHPUnit\Framework\TestCase;
use Tests\Battle\Factory\CommandFactory as TestCommandFactory;
use Tests\Battle\Factory\UnitFactory;
use Tests\Battle\Factory\UnitFactoryException;

class ViewTest extends TestCase
{
    /**
     * Тест генерации вида юнитов ближнего боя
     *
     * @throws ClassFactoryException
     * @throws CommandException
     * @throws UnitFactoryException
     * @throws UnitException
     */
    public function testViewRenderMelee(): void
    {
        $leftCommand = TestCommandFactory::createLeftCommand();
        $rightCommand = TestCommandFactory::createRightCommand();
        $view = (new ViewFactory)->create();

        $html = $view->renderCommandView($leftCommand, $rightCommand);

        $expectHtml = <<<EOT
<div class="row">
    <table>
        <tr>
            <td class="w25"></td>
            <td class="w25"><div class="unit">
    <table>
        <tr>
            <td rowspan="2" class="avatar_box" style="background-image: url(/images/avas/humans/human001.jpg"></td>
            <td>Name</td>
            <td>Damage</td>
            <td>Life</td>
            <td>Cons</td>
            <td>Action?</td>
            <td>Alive?</td>
        </tr>
        <tr>
            <td>unit_1</td>
            <td>20</td>
            <td class="life_bar">
                <div class="life_bar">
                    <div class="life" style="width: 100%;"></div>
                </div>
                <div class="life_text">
                    100/100                </div>
            </td>
            <td>0/1000</td>
            <td class="c4">no</td>
            <td>yes</td>
        </tr>
    </table>
</div></td>
            <td class="w25"><div class="unit">
    <table>
        <tr>
            <td rowspan="2" class="avatar_box" style="background-image: url(/images/avas/humans/human002.jpg"></td>
            <td>Name</td>
            <td>Damage</td>
            <td>Life</td>
            <td>Cons</td>
            <td>Action?</td>
            <td>Alive?</td>
        </tr>
        <tr>
            <td>unit_2</td>
            <td>30</td>
            <td class="life_bar">
                <div class="life_bar">
                    <div class="life" style="width: 100%;"></div>
                </div>
                <div class="life_text">
                    150/150                </div>
            </td>
            <td>0/1000</td>
            <td class="c4">no</td>
            <td>yes</td>
        </tr>
    </table>
</div></td>
            <td class="w25"></td>
        </tr>
    </table>
</div>
EOT;

        self::assertEquals($expectHtml, $html);
    }

    /**
     * Тест генерации вида юнитов дальнего боя
     *
     * @throws ClassFactoryException
     * @throws CommandException
     * @throws UnitException
     * @throws UnitFactoryException
     */
    public function testViewRenderRange(): void
    {
        $lifeUnit = UnitFactory::createByTemplate(5);
        $rightUnit = UnitFactory::createByTemplate(6);

        $leftCommand = CommandFactory::create([$lifeUnit]);
        $rightCommand = CommandFactory::create([$rightUnit]);
        $view = (new ViewFactory)->create();

        $html = $view->renderCommandView($leftCommand, $rightCommand);

        $expectHtml = <<<EOT
<div class="row">
    <table>
        <tr>
            <td class="w25"><div class="unit">
    <table>
        <tr>
            <td rowspan="2" class="avatar_box" style="background-image: url(/images/avas/monsters/003.png"></td>
            <td>Name</td>
            <td>Damage</td>
            <td>Life</td>
            <td>Cons</td>
            <td>Action?</td>
            <td>Alive?</td>
        </tr>
        <tr>
            <td>unit_5</td>
            <td>15</td>
            <td class="life_bar">
                <div class="life_bar">
                    <div class="life" style="width: 100%;"></div>
                </div>
                <div class="life_text">
                    80/80                </div>
            </td>
            <td>0/1000</td>
            <td class="c4">no</td>
            <td>yes</td>
        </tr>
    </table>
</div></td>
            <td class="w25"></td>
            <td class="w25"></td>
            <td class="w25"><div class="unit">
    <table>
        <tr>
            <td rowspan="2" class="avatar_box" style="background-image: url(/images/avas/monsters/003.png"></td>
            <td>Name</td>
            <td>Damage</td>
            <td>Life</td>
            <td>Cons</td>
            <td>Action?</td>
            <td>Alive?</td>
        </tr>
        <tr>
            <td>unit_6</td>
            <td>12</td>
            <td class="life_bar">
                <div class="life_bar">
                    <div class="life" style="width: 100%;"></div>
                </div>
                <div class="life_text">
                    50/50                </div>
            </td>
            <td>0/1000</td>
            <td class="c4">no</td>
            <td>yes</td>
        </tr>
    </table>
</div></td>
        </tr>
    </table>
</div>
EOT;

        self::assertEquals($expectHtml, $html);
    }
}
