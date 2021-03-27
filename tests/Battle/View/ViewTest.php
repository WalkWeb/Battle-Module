<?php

declare(strict_types=1);

namespace Tests\Battle\View;

use Battle\Classes\ClassFactoryException;
use Battle\Command\CommandException;
use Battle\View\View;
use PHPUnit\Framework\TestCase;
use Tests\Battle\Factory\CommandFactory;
use Tests\Battle\Factory\UnitFactoryException;

class ViewTest extends TestCase
{
    /**
     * @throws ClassFactoryException
     * @throws CommandException
     * @throws UnitFactoryException
     */
    public function testViewRender(): void
    {
        $leftCommand = CommandFactory::createLeftCommand();
        $rightCommand = CommandFactory::createRightCommand();
        $view = new View();

        $html = $view->render($leftCommand, $rightCommand);

        $expectHtml = <<<EOT
<div class="row">
                    <table>
                        <tr>
                            <td class="w25"></td>
                            <td class="w25">
        <div class="unit">
            <table>
                <tr>
                    <td rowspan="2" class="avatar_box" style="background-image: url(/images/avas/humans/human001.jpg)"></td>
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
                            100/100
                        </div>
                    </td>
                    <td>0/1000</td>
                    <td class="c4">no</td>
                    <td>yes</td>
                </tr>
            </table>
        </div></td>
                            <td class="w25">
        <div class="unit">
            <table>
                <tr>
                    <td rowspan="2" class="avatar_box" style="background-image: url(/images/avas/humans/human002.jpg)"></td>
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
                            150/150
                        </div>
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
}
