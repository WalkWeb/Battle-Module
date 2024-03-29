<?php

declare(strict_types=1);

namespace Tests\Battle\View;

use Battle\Command\CommandFactory;
use Battle\Container\ContainerException;
use Battle\Response\Response;
use Battle\Translation\Translation;
use Battle\Unit\Ability\AbilityFactory;
use Battle\View\View;
use Battle\View\ViewException;
use Battle\View\ViewFactory;
use Battle\View\ViewInterface;
use Exception;
use Tests\AbstractUnitTest;
use Tests\Factory\CommandFactory as TestCommandFactory;
use Tests\Factory\UnitFactory;

class ViewTest extends AbstractUnitTest
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

        $expectHtml = <<<EOT
<div class="row">
    <table>
        <tr>
            <td class="w25" id="left_command_range"></td>
            <td class="w25" id="left_command_melee"><div align="center">
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
                    <div class="unit_ava" style="background-image: url(/images/avas/humans/human001.jpg);">
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
                <div class="unit_effect_container">
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
</div>
</td>
            <td class="w25" id="right_command_melee"><div align="center">
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
                                    <span class="hp">250</span> / <span class="thp">250</span>
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
                    <div class="unit_ava" style="background-image: url(/images/avas/humans/human002.jpg);">
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
                <div class="unit_effect_container">
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
</div>
</td>
            <td class="w25" id="right_command_range"></td>
        </tr>
    </table>
</div>
EOT;

        self::assertEquals($expectHtml, $this->getView()->renderCommandView($leftCommand, $rightCommand));
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

        $expectHtml = <<<EOT
<div class="row">
    <table>
        <tr>
            <td class="w25" id="left_command_range"><div align="center">
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
                    <div class="unit_ava" style="background-image: url(/images/avas/monsters/003.png);">
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
                <div class="unit_effect_container">
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
</div>
</td>
            <td class="w25" id="left_command_melee"></td>
            <td class="w25" id="right_command_melee"></td>
            <td class="w25" id="right_command_range"><div align="center">
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
                    <div class="unit_ava" style="background-image: url(/images/avas/monsters/003.png);">
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
                <div class="unit_effect_container">
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
</div>
</td>
        </tr>
    </table>
</div>
EOT;

        self::assertEquals($expectHtml, $this->getView()->renderCommandView($leftCommand, $rightCommand));
    }

    /**
     * Тест шаблона unit_full_log.template.php
     *
     * Чтобы View покрылся тестом полноценно - один из юнитов обязательно должен иметь эффект
     *
     * @throws Exception
     */
    public function testViewRenderFullLogUnit(): void
    {
        $abilityFactory = new AbilityFactory();
        $unit = UnitFactory::createByTemplate(21);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = $abilityFactory->create($unit, $this->container->getAbilityDataProvider()->get('Reserve Forces', 1));

        foreach ($ability->getActions($enemyCommand, $command) as $action) {
            self::assertTrue($action->canByUsed());
            $action->handle();
        }

        $expectHtml = <<<EOT
<div class="row">
    <table>
        <tr>
            <td class="w25" id="left_command_range"></td>
            <td class="w25" id="left_command_melee"><div align="center">
    <div class="unit_main_box">
        <div class="unit_box1">
            <div class="unit_box1_right">
                <div class="unit_box1_right2">
                    <div class="unit_box1_right3">
                        <div class="unit_box1_right4">
                            <div class="unit_hp">
                                <div class="unit_hp_bar">
                                    <div class="unit_hp_bar2" style="width: 100%;"></div>
                                </div>
                                <div class="unit_hp_text">
                                    <span class="hp">130</span> / <span class="thp">130</span>
                                </div>
                                <div class="unit_hp_text_add">
                                    <span class="recdam"></span>
                                </div>
                            </div>
                                                            <div class="unit_cons">
                                    <div class="unit_cons_bar2" style="width: 20%;"></div>
                                </div>
                                <div class="unit_rage">
                                    <div class="unit_rage_bar2" style="width: 14%;"></div>
                                </div>
                                                    </div>
                    </div>
                </div>
            </div>
            <div class="unit_box1_left">
                <div class="unit_box1_left2">
                    <div class="unit_ava" style="background-image: url(/images/avas/orcs/orc001.jpg);">
                        <div class="unit_ava_blank"></div>
                        <div class="unit_ava_blank"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="unit_box2">
            <div class="unit_box2_right">
                <div class="unit_box2_right2">
                    <div class="unit_box2_right3">
                        <p><span style="color: #ae882d">Titan</span></p>
                    </div>
                </div>
                <div class="unit_effect_container">
                    <div class="unit_effect_icon" style="background-image: url(/images/icons/ability/156.png)"><div>6</div></div>                </div>
            </div>
            <div class="unit_box2_left">
                <div class="unit_icon">
                    <div class="unit_icon_left">3</div>
                    <div class="unit_icon_right">
                        <img src="/images/icons/small/titan.png" alt="">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div></td>
            <td class="w25" id="right_command_melee"><div align="center">
    <div class="unit_main_box">
        <div class="unit_box1">
            <div class="unit_box1_right">
                <div class="unit_box1_right2">
                    <div class="unit_box1_right3">
                        <div class="unit_box1_right4">
                            <div class="unit_hp">
                                <div class="unit_hp_bar">
                                    <div class="unit_hp_bar2" style="width: 100%;"></div>
                                </div>
                                <div class="unit_hp_text">
                                    <span class="hp">250</span> / <span class="thp">250</span>
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
                    <div class="unit_ava" style="background-image: url(/images/avas/humans/human002.jpg);">
                        <div class="unit_ava_blank"></div>
                        <div class="unit_ava_blank"></div>
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
                <div class="unit_effect_container">
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
            <td class="w25" id="right_command_range"></td>
        </tr>
    </table>
</div>
EOT;

        self::assertEquals($expectHtml, $this->getView()->renderCommandView($command, $enemyCommand, true));
    }

    /**
     * @throws ContainerException
     */
    public function testViewRenderHead(): void
    {
        $expectHtml = <<<EOT
<html lang="ru">
<head>
    <title>Battle Module</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <link rel="stylesheet" type="text/css" href="/styles/battle.css">
    <script src="/js/battle.js"></script>
</head>
<body>
EOT;

        self::assertEquals($expectHtml, $this->getView()->renderHead());
    }

    /**
     * Тест на генерацию html-кода для отображения таблицы характеристик юнитов
     *
     * @throws Exception
     */
    public function testViewGetUnitsStats(): void
    {
        $leftCommand = TestCommandFactory::createLeftCommand();
        $rightCommand = TestCommandFactory::createRightCommand();

        $expectHtml = <<<EOT
<div class="units_stats_box">
    <table class="units_stats">
                    <tr class="header">
                <td colspan="1" rowspan="4"><p><img src="/images/avas/humans/human001.jpg" width="90" alt="" /><br />unit_1</p></td>
                <td><p>Melee?</p></td>
                <td><p>Race</p></td>
                <td><p><abbr title="Life"><img src="/images/battle/stats_icon/life.png" alt=""></abbr></p></td>
                <td><p><abbr title="Mana"><img src="/images/battle/stats_icon/mana.png" alt=""></abbr></p></td>
                <td><p>Alive?</p></td>
                <td><p>Action?</p></td>
                <td><p><abbr title="Physical Damage"><img src="/images/battle/stats_icon/physical_damage.png" alt=""></abbr></p></td>
                <td><p><abbr title="Fire Damage"><img src="/images/battle/stats_icon/fire_damage.png" alt=""></abbr></p></td>
                <td><p><abbr title="Water Damage"><img src="/images/battle/stats_icon/water_damage.png" alt=""></abbr></p></td>
                <td><p><abbr title="Air Damage"><img src="/images/battle/stats_icon/air_damage.png" alt=""></abbr></p></td>
                <td><p><abbr title="Earth Damage"><img src="/images/battle/stats_icon/earth_damage.png" alt=""></abbr></p></td>
                <td><p><abbr title="Life Magic Damage"><img src="/images/battle/stats_icon/life_damage.png" alt=""></abbr></p></td>
                <td><p><abbr title="Death Magic Damage"><img src="/images/battle/stats_icon/death_damage.png" alt=""></abbr></p></td>
                <td><p><abbr title="Accuracy"><img src="/images/battle/stats_icon/accuracy.png" alt=""></abbr></p></td>
                <td><p><abbr title="Magic Accuracy"><img src="/images/battle/stats_icon/magic_accuracy.png" alt=""></abbr></p></td>
                <td><p><abbr title="Chance Critical Damage"><img src="/images/battle/stats_icon/critical_chance.png" alt=""></abbr></p></td>
                <td><p><abbr title="Critical Damage Multiplier"><img src="/images/battle/stats_icon/critical_multiplication.png" alt=""></abbr></p></td>
                <td><p><abbr title="Attack Speed"><img src="/images/battle/stats_icon/attack_speed.png" alt=""></abbr></p></td>
                <td><p><abbr title="Attack Speed"><img src="/images/battle/stats_icon/cast_speed.png" alt=""></abbr></p></td>
            </tr>
            <tr>
                <td><p>Yes</p></td>
                <td><p>Human</p></td>
                <td><p>100/100</p></td>
                <td><p>50/50</p></td>
                <td><p>Yes</p></td>
                <td><p>No</p></td>
                <td><p>20</p></td>
                <td><p>0</p></td>
                <td><p>0</p></td>
                <td><p>0</p></td>
                <td><p>0</p></td>
                <td><p>0</p></td>
                <td><p>0</p></td>
                <td><p>200</p></td>
                <td><p>100</p></td>
                <td><p>5%</p></td>
                <td><p>200%</p></td>
                <td><p>1</p></td>
                <td><p>1.2</p></td>
            </tr>
            <tr class="header">
                <td><p>Concentration</p></td>
                <td><p>Rage</p></td>
                <td><p>Level</p></td>
                <td><p><abbr title="Mental Barrier"><img src="/images/battle/stats_icon/mental_barrier.png" alt=""></abbr></p></td>
                <td><p>Damage Type</p></td>
                <td><p>Weapon Type</p></td>
                <td><p><abbr title="Physical Damage Resistance"><img src="/images/battle/stats_icon/physical_resistance.png" alt=""></abbr></p></td>
                <td><p><abbr title="Fire Damage Resistance"><img src="/images/battle/stats_icon/fire_resistance.png" alt=""></abbr></p></td>
                <td><p><abbr title="Water Damage Resistance"><img src="/images/battle/stats_icon/water_resistance.png" alt=""></abbr></p></td>
                <td><p><abbr title="Air Damage Resistance"><img src="/images/battle/stats_icon/air_resistance.png" alt=""></abbr></p></td>
                <td><p><abbr title="Earth Damage Resistance"><img src="/images/battle/stats_icon/earth_resistance.png" alt=""></abbr></p></td>
                <td><p><abbr title="Life Magic Damage Resistance"><img src="/images/battle/stats_icon/life_resistance.png" alt=""></abbr></p></td>
                <td><p><abbr title="Death Magic Damage Resistance"><img src="/images/battle/stats_icon/death_resistance.png" alt=""></abbr></p></td>
                <td><p><abbr title="Defense"><img src="/images/battle/stats_icon/defence.png" alt=""></abbr></p></td>
                <td><p><abbr title="Magic Defense"><img src="/images/battle/stats_icon/magic_defence.png" alt=""></abbr></p></td>
                <td><p><abbr title="Block"><img src="/images/battle/stats_icon/block.png" alt=""></abbr></p></td>
                <td><p><abbr title="Magic Block"><img src="/images/battle/stats_icon/magic_block.png" alt=""></abbr></p></td>
                <td><p><abbr title="Block Ignoring"><img src="/images/battle/stats_icon/block_ignore.png" alt=""></abbr></p></td>
                <td><p><abbr title="Vampirism"><img src="/images/battle/stats_icon/vampirism.png" alt=""></abbr></p></td>
            </tr>
            <tr>
                <td><p>0/1000</p></td>
                <td><p>0/1000</p></td>
                <td><p>1</p></td>
                <td><p>0%</p></td>
                <td><p>Attack</p></td>
                <td><p>Sword</p></td>
                <td><p>0%</p></td>
                <td><p>0%</p></td>
                <td><p>0%</p></td>
                <td><p>0%</p></td>
                <td><p>0%</p></td>
                <td><p>0%</p></td>
                <td><p>0%</p></td>
                <td><p>100</p></td>
                <td><p>50</p></td>
                <td><p>0%</p></td>
                <td><p>0%</p></td>
                <td><p>0</p></td>
                <td><p>0%</p></td>
            </tr>
                    <tr class="header">
                <td colspan="1" rowspan="4"><p><img src="/images/avas/humans/human002.jpg" width="90" alt="" /><br />unit_2</p></td>
                <td><p>Melee?</p></td>
                <td><p>Race</p></td>
                <td><p><abbr title="Life"><img src="/images/battle/stats_icon/life.png" alt=""></abbr></p></td>
                <td><p><abbr title="Mana"><img src="/images/battle/stats_icon/mana.png" alt=""></abbr></p></td>
                <td><p>Alive?</p></td>
                <td><p>Action?</p></td>
                <td><p><abbr title="Physical Damage"><img src="/images/battle/stats_icon/physical_damage.png" alt=""></abbr></p></td>
                <td><p><abbr title="Fire Damage"><img src="/images/battle/stats_icon/fire_damage.png" alt=""></abbr></p></td>
                <td><p><abbr title="Water Damage"><img src="/images/battle/stats_icon/water_damage.png" alt=""></abbr></p></td>
                <td><p><abbr title="Air Damage"><img src="/images/battle/stats_icon/air_damage.png" alt=""></abbr></p></td>
                <td><p><abbr title="Earth Damage"><img src="/images/battle/stats_icon/earth_damage.png" alt=""></abbr></p></td>
                <td><p><abbr title="Life Magic Damage"><img src="/images/battle/stats_icon/life_damage.png" alt=""></abbr></p></td>
                <td><p><abbr title="Death Magic Damage"><img src="/images/battle/stats_icon/death_damage.png" alt=""></abbr></p></td>
                <td><p><abbr title="Accuracy"><img src="/images/battle/stats_icon/accuracy.png" alt=""></abbr></p></td>
                <td><p><abbr title="Magic Accuracy"><img src="/images/battle/stats_icon/magic_accuracy.png" alt=""></abbr></p></td>
                <td><p><abbr title="Chance Critical Damage"><img src="/images/battle/stats_icon/critical_chance.png" alt=""></abbr></p></td>
                <td><p><abbr title="Critical Damage Multiplier"><img src="/images/battle/stats_icon/critical_multiplication.png" alt=""></abbr></p></td>
                <td><p><abbr title="Attack Speed"><img src="/images/battle/stats_icon/attack_speed.png" alt=""></abbr></p></td>
                <td><p><abbr title="Attack Speed"><img src="/images/battle/stats_icon/cast_speed.png" alt=""></abbr></p></td>
            </tr>
            <tr>
                <td><p>Yes</p></td>
                <td><p>Human</p></td>
                <td><p>250/250</p></td>
                <td><p>20/50</p></td>
                <td><p>Yes</p></td>
                <td><p>No</p></td>
                <td><p>30</p></td>
                <td><p>0</p></td>
                <td><p>0</p></td>
                <td><p>0</p></td>
                <td><p>0</p></td>
                <td><p>0</p></td>
                <td><p>0</p></td>
                <td><p>200</p></td>
                <td><p>100</p></td>
                <td><p>5%</p></td>
                <td><p>200%</p></td>
                <td><p>1</p></td>
                <td><p>0</p></td>
            </tr>
            <tr class="header">
                <td><p>Concentration</p></td>
                <td><p>Rage</p></td>
                <td><p>Level</p></td>
                <td><p><abbr title="Mental Barrier"><img src="/images/battle/stats_icon/mental_barrier.png" alt=""></abbr></p></td>
                <td><p>Damage Type</p></td>
                <td><p>Weapon Type</p></td>
                <td><p><abbr title="Physical Damage Resistance"><img src="/images/battle/stats_icon/physical_resistance.png" alt=""></abbr></p></td>
                <td><p><abbr title="Fire Damage Resistance"><img src="/images/battle/stats_icon/fire_resistance.png" alt=""></abbr></p></td>
                <td><p><abbr title="Water Damage Resistance"><img src="/images/battle/stats_icon/water_resistance.png" alt=""></abbr></p></td>
                <td><p><abbr title="Air Damage Resistance"><img src="/images/battle/stats_icon/air_resistance.png" alt=""></abbr></p></td>
                <td><p><abbr title="Earth Damage Resistance"><img src="/images/battle/stats_icon/earth_resistance.png" alt=""></abbr></p></td>
                <td><p><abbr title="Life Magic Damage Resistance"><img src="/images/battle/stats_icon/life_resistance.png" alt=""></abbr></p></td>
                <td><p><abbr title="Death Magic Damage Resistance"><img src="/images/battle/stats_icon/death_resistance.png" alt=""></abbr></p></td>
                <td><p><abbr title="Defense"><img src="/images/battle/stats_icon/defence.png" alt=""></abbr></p></td>
                <td><p><abbr title="Magic Defense"><img src="/images/battle/stats_icon/magic_defence.png" alt=""></abbr></p></td>
                <td><p><abbr title="Block"><img src="/images/battle/stats_icon/block.png" alt=""></abbr></p></td>
                <td><p><abbr title="Magic Block"><img src="/images/battle/stats_icon/magic_block.png" alt=""></abbr></p></td>
                <td><p><abbr title="Block Ignoring"><img src="/images/battle/stats_icon/block_ignore.png" alt=""></abbr></p></td>
                <td><p><abbr title="Vampirism"><img src="/images/battle/stats_icon/vampirism.png" alt=""></abbr></p></td>
            </tr>
            <tr>
                <td><p>0/1000</p></td>
                <td><p>0/1000</p></td>
                <td><p>1</p></td>
                <td><p>0%</p></td>
                <td><p>Attack</p></td>
                <td><p>Sword</p></td>
                <td><p>0%</p></td>
                <td><p>0%</p></td>
                <td><p>0%</p></td>
                <td><p>0%</p></td>
                <td><p>0%</p></td>
                <td><p>0%</p></td>
                <td><p>0%</p></td>
                <td><p>100</p></td>
                <td><p>50</p></td>
                <td><p>0%</p></td>
                <td><p>0%</p></td>
                <td><p>0</p></td>
                <td><p>0%</p></td>
            </tr>
            </table>
</div>
EOT;

        self::assertEquals($expectHtml, $this->getView()->getUnitsStats($leftCommand, $rightCommand));
    }

    /**
     * @throws Exception
     */
    public function testViewRenderResult(): void
    {
        $leftCommand = TestCommandFactory::createLeftCommand();
        $rightCommand = TestCommandFactory::createRightCommand();

        $response = new Response($leftCommand, $rightCommand, $leftCommand, $rightCommand, 1, $this->container);

        // Из-за вывода статистики, и подсчета времени выполнения в статистике, мы никогда не сможем точно узнать
        // какой код будет выведен. По этому просто проверяем, что рендер прошел без ошибок, и получили строку
        self::assertIsString($this->getView()->renderBattle($response));
    }

    /**
     * Тест на проверку ситуации, когда указанного файла шаблона не существует
     *
     * @throws Exception
     */
    public function testViewMissingTemplate(): void
    {
        $view = new View(
            new Translation(),
            __DIR__ . '/../../../templates/',
            'battle/missed_file.php',
            'battle/battle.template.php',
            'battle/row.template.php',
            'battle/unit/unit.template.php',
            'battle/unit/unit_full_log.template.php',
            'battle/unit/units_stats.template.php'
        );

        $this->expectException(ViewException::class);
        $this->expectExceptionMessage(ViewException::MISSING_TEMPLATE . ': Head Template');
        $view->renderHead();
    }

    /**
     * Тест на корректность отображения полоски маны, когда юнит имеет ментальный барьер и неполное здоровье
     *
     * (тест на ошибку, когда длина полоски считалась из размера здоровья, а не маны)
     *
     * @throws Exception
     */
    public function testViewMentalBarrierNoFullLife(): void
    {
        // 50% здоровья, 100% маны - полоска должна быть на 100%
        $unit = UnitFactory::createByTemplate(32);

        $expectHtml = <<<EOT
<div align="center">
    <div class="unit_main_box" id="usr_5ab57a34-232f-4d94-a2c9-bb19f41b4e25">
        <div class="unit_box1">
            <div class="unit_box1_right">
                <div class="unit_box1_right2">
                    <div class="unit_box1_right3">
                        <div class="unit_box1_right4">
                            <div class="unit_hp">
                                <div id="hp_bar_bg_5ab57a34-232f-4d94-a2c9-bb19f41b4e25" class="unit_hp_bar_mana">
                                    <div id="hp_bar_5ab57a34-232f-4d94-a2c9-bb19f41b4e25" class="unit_hp_bar2_mana" style="width: 100%;"></div>
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
                    <div class="unit_ava" style="background-image: url(/images/avas/monsters/003.png);">
                        <div id="ava_5ab57a34-232f-4d94-a2c9-bb19f41b4e25" class="unit_ava_blank"></div>
                        <div id="avas_5ab57a34-232f-4d94-a2c9-bb19f41b4e25" class="unit_ava_blank"></div>
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
                <div class="unit_effect_container">
                    <p id="unit_effects_5ab57a34-232f-4d94-a2c9-bb19f41b4e25"></p>
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
</div>

EOT;

        self::assertEquals($expectHtml, $this->getView()->getUnitView($unit));
    }

    /**
     * @return ViewInterface
     * @throws ContainerException
     */
    private function getView(): ViewInterface
    {
        return (new ViewFactory($this->getContainer()))->create();
    }
}
