<?php

declare(strict_types=1);

namespace Tests\Battle\View;

use Battle\Command\CommandFactory;
use Battle\Result\Chat\Chat;
use Battle\Result\FullLog\FullLog;
use Battle\Result\Result;
use Battle\Result\Scenario\Scenario;
use Battle\Statistic\Statistic;
use Battle\Translation\Translation;
use Battle\View\ViewFactory;
use Exception;
use PHPUnit\Framework\TestCase;
use Tests\Battle\Factory\CommandFactory as TestCommandFactory;
use Tests\Battle\Factory\UnitFactory;

class ViewTest extends TestCase
{
    /**
     * Тест генерации вида юнитов ближнего боя
     *
     * @throws Exception
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
            <td class="w25"><div align="center">
    <div class="unit_main_box" id="usr_f7e84eab-e4f6-469f-b0e3-f5f965f9fbce">
        <div class="unit_box1">
            <div class="unit_box1_right">
                <div class="unit_box1_right2">
                    <div class="unit_box1_right3">
                        <div class="unit_box1_right4">
                            <div class="unit_hp">
                                <div id="hp_bar_bg_f7e84eab-e4f6-469f-b0e3-f5f965f9fbce" class="unit_hp_bar">
                                    <div id="hp_bar_f7e84eab-e4f6-469f-b0e3-f5f965f9fbce" class="unit_hp_bar2" style="width: 100%;"></div>
                                </div>
                                <div class="unit_hp_text">
                                    <span class="hp">100</span> / <span class="thp">100</span>
                                </div>
                                <div class="unit_hp_text_add">
                                    <span class="recdam"></span>
                                </div>
                            </div>
                                                        <div class="unit_cons">
                                <div class="unit_cons_bar2" style="width: 0%;"></div>
                            </div>
                            <div class="unit_rage">
                                <div class="unit_rage_bar2" style="width: 0%;"></div>
                            </div>
                                                    </div>
                    </div>
                </div>
            </div>
            <div class="unit_box1_left">
                <div class="unit_box1_left2">
                    <div class="unit_ava" style="background: url(/images/avas/humans/human001.jpg) no-repeat center; background-size: cover;">
                        <div id="ava_f7e84eab-e4f6-469f-b0e3-f5f965f9fbce" class="unit_ava_blank"></div>
                        <div id="avas_f7e84eab-e4f6-469f-b0e3-f5f965f9fbce" class="unit_ava_blank"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="unit_box2">
            <div class="unit_box2_right">
                <div class="unit_box2_right2">
                    <div class="unit_box2_right3">
                        <p><span style="color: #1e72e3">unit_1</span></p>
                    </div>
                </div>
                <div class="unit_effect_contant">
                    <p id="unit_effects_f7e84eab-e4f6-469f-b0e3-f5f965f9fbce"></p>
                </div>
            </div>
            <div class="unit_box2_left">
                <div class="unit_icon">
                    <div class="unit_icon_left">1</div>
                    <div class="unit_icon_right">
                        <img src="/images/icons/small/warrior.png" alt="">
                    </div>
                </div>

            </div>
        </div>
    </div>
</div></td>
            <td class="w25"><div align="center">
    <div class="unit_main_box" id="usr_1aab367d-37e8-4544-9915-cb3d7779308b">
        <div class="unit_box1">
            <div class="unit_box1_right">
                <div class="unit_box1_right2">
                    <div class="unit_box1_right3">
                        <div class="unit_box1_right4">
                            <div class="unit_hp">
                                <div id="hp_bar_bg_1aab367d-37e8-4544-9915-cb3d7779308b" class="unit_hp_bar">
                                    <div id="hp_bar_1aab367d-37e8-4544-9915-cb3d7779308b" class="unit_hp_bar2" style="width: 100%;"></div>
                                </div>
                                <div class="unit_hp_text">
                                    <span class="hp">150</span> / <span class="thp">150</span>
                                </div>
                                <div class="unit_hp_text_add">
                                    <span class="recdam"></span>
                                </div>
                            </div>
                                                        <div class="unit_cons">
                                <div class="unit_cons_bar2" style="width: 0%;"></div>
                            </div>
                            <div class="unit_rage">
                                <div class="unit_rage_bar2" style="width: 0%;"></div>
                            </div>
                                                    </div>
                    </div>
                </div>
            </div>
            <div class="unit_box1_left">
                <div class="unit_box1_left2">
                    <div class="unit_ava" style="background: url(/images/avas/humans/human002.jpg) no-repeat center; background-size: cover;">
                        <div id="ava_1aab367d-37e8-4544-9915-cb3d7779308b" class="unit_ava_blank"></div>
                        <div id="avas_1aab367d-37e8-4544-9915-cb3d7779308b" class="unit_ava_blank"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="unit_box2">
            <div class="unit_box2_right">
                <div class="unit_box2_right2">
                    <div class="unit_box2_right3">
                        <p><span style="color: #1e72e3">unit_2</span></p>
                    </div>
                </div>
                <div class="unit_effect_contant">
                    <p id="unit_effects_1aab367d-37e8-4544-9915-cb3d7779308b"></p>
                </div>
            </div>
            <div class="unit_box2_left">
                <div class="unit_icon">
                    <div class="unit_icon_left">1</div>
                    <div class="unit_icon_right">
                        <img src="/images/icons/small/warrior.png" alt="">
                    </div>
                </div>

            </div>
        </div>
    </div>
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
     * @throws Exception
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
            <td class="w25"><div align="center">
    <div class="unit_main_box" id="usr_46d969c1-463b-42b1-a2e0-2c64a8c34ae1">
        <div class="unit_box1">
            <div class="unit_box1_right">
                <div class="unit_box1_right2">
                    <div class="unit_box1_right3">
                        <div class="unit_box1_right4">
                            <div class="unit_hp">
                                <div id="hp_bar_bg_46d969c1-463b-42b1-a2e0-2c64a8c34ae1" class="unit_hp_bar">
                                    <div id="hp_bar_46d969c1-463b-42b1-a2e0-2c64a8c34ae1" class="unit_hp_bar2" style="width: 100%;"></div>
                                </div>
                                <div class="unit_hp_text">
                                    <span class="hp">80</span> / <span class="thp">80</span>
                                </div>
                                <div class="unit_hp_text_add">
                                    <span class="recdam"></span>
                                </div>
                            </div>
                                                        <div class="unit_cons">
                                <div class="unit_cons_bar2" style="width: 0%;"></div>
                            </div>
                            <div class="unit_rage">
                                <div class="unit_rage_bar2" style="width: 0%;"></div>
                            </div>
                                                    </div>
                    </div>
                </div>
            </div>
            <div class="unit_box1_left">
                <div class="unit_box1_left2">
                    <div class="unit_ava" style="background: url(/images/avas/monsters/003.png) no-repeat center; background-size: cover;">
                        <div id="ava_46d969c1-463b-42b1-a2e0-2c64a8c34ae1" class="unit_ava_blank"></div>
                        <div id="avas_46d969c1-463b-42b1-a2e0-2c64a8c34ae1" class="unit_ava_blank"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="unit_box2">
            <div class="unit_box2_right">
                <div class="unit_box2_right2">
                    <div class="unit_box2_right3">
                        <p><span style="color: #1e72e3">unit_5</span></p>
                    </div>
                </div>
                <div class="unit_effect_contant">
                    <p id="unit_effects_46d969c1-463b-42b1-a2e0-2c64a8c34ae1"></p>
                </div>
            </div>
            <div class="unit_box2_left">
                <div class="unit_icon">
                    <div class="unit_icon_left">1</div>
                    <div class="unit_icon_right">
                        <img src="/images/icons/small/priest.png" alt="">
                    </div>
                </div>

            </div>
        </div>
    </div>
</div></td>
            <td class="w25"></td>
            <td class="w25"></td>
            <td class="w25"><div align="center">
    <div class="unit_main_box" id="usr_1e813812-9a21-4e18-b494-8d552bac0cf4">
        <div class="unit_box1">
            <div class="unit_box1_right">
                <div class="unit_box1_right2">
                    <div class="unit_box1_right3">
                        <div class="unit_box1_right4">
                            <div class="unit_hp">
                                <div id="hp_bar_bg_1e813812-9a21-4e18-b494-8d552bac0cf4" class="unit_hp_bar">
                                    <div id="hp_bar_1e813812-9a21-4e18-b494-8d552bac0cf4" class="unit_hp_bar2" style="width: 100%;"></div>
                                </div>
                                <div class="unit_hp_text">
                                    <span class="hp">50</span> / <span class="thp">50</span>
                                </div>
                                <div class="unit_hp_text_add">
                                    <span class="recdam"></span>
                                </div>
                            </div>
                                                        <div class="unit_cons">
                                <div class="unit_cons_bar2" style="width: 0%;"></div>
                            </div>
                            <div class="unit_rage">
                                <div class="unit_rage_bar2" style="width: 0%;"></div>
                            </div>
                                                    </div>
                    </div>
                </div>
            </div>
            <div class="unit_box1_left">
                <div class="unit_box1_left2">
                    <div class="unit_ava" style="background: url(/images/avas/monsters/003.png) no-repeat center; background-size: cover;">
                        <div id="ava_1e813812-9a21-4e18-b494-8d552bac0cf4" class="unit_ava_blank"></div>
                        <div id="avas_1e813812-9a21-4e18-b494-8d552bac0cf4" class="unit_ava_blank"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="unit_box2">
            <div class="unit_box2_right">
                <div class="unit_box2_right2">
                    <div class="unit_box2_right3">
                        <p><span style="color: #1e72e3">unit_6</span></p>
                    </div>
                </div>
                <div class="unit_effect_contant">
                    <p id="unit_effects_1e813812-9a21-4e18-b494-8d552bac0cf4"></p>
                </div>
            </div>
            <div class="unit_box2_left">
                <div class="unit_icon">
                    <div class="unit_icon_left">1</div>
                    <div class="unit_icon_right">
                        <img src="/images/icons/small/priest.png" alt="">
                    </div>
                </div>

            </div>
        </div>
    </div>
</div></td>
        </tr>
    </table>
</div>
EOT;

        self::assertEquals($expectHtml, $html);
    }

    public function testViewRenderHead(): void
    {
        $factory = new ViewFactory();
        $view = $factory->create();

        $expectHtml = <<<EOT
<html lang="ru">
<head>
    <title>Battle Module</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <link rel="stylesheet" type="text/css" href="/styles/main.css">
    <script src="/js/battle.js"></script>
</head>
<body>
EOT;

        self::assertEquals($expectHtml, $view->renderHead());
    }

    /**
     * @throws Exception
     */
    public function testViewRenderResult(): void
    {
        $leftCommand = TestCommandFactory::createLeftCommand();
        $rightCommand = TestCommandFactory::createRightCommand();

        $result = new Result($leftCommand, $rightCommand, $leftCommand, $rightCommand, 1, new FullLog(), new Chat(), new Scenario(), new Statistic(), new Translation());
        $view = (new ViewFactory)->create();

        // Из-за вывода статистики, и подсчета времени выполнения в статистике, мы никогда не сможем точно узнать
        // какой код будет выведен. По этому просто проверяем, что рендер прошел без ошибок, и получили строку
        self::assertIsString($view->renderResult($result));
    }
}
