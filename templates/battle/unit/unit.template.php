<?php

use Battle\Unit\UnitInterface;
use Battle\View\ViewException;

if (empty($unit) || !($unit instanceof UnitInterface)) {
    throw new ViewException(ViewException::MISSING_UNIT);
}

// todo добавить отображение уровня
// todo добавить отображение длины полоски ярости

?>
<div align="center">
    <div class="unit_main_box" id="usr_<?= $unit->getId() ?>">
        <div class="unit_box1">
            <div class="unit_box1_right">
                <div class="unit_box1_right2">
                    <div class="unit_box1_right3">
                        <div class="unit_box1_right4">
                            <div class="unit_hp">
                                <div id="hp_bar_bg_40" class="unit_hp_bar">
                                    <div id="hp_bar_40" class="unit_hp_bar2" style="width: <?= $this->getWidth($unit->getLife(), $unit->getTotalLife()) ?>%;"></div>
                                </div>
                                <div class="unit_hp_text">
                                    <span class="hp"><?= $unit->getLife() ?></span> / <span class="thp"><?= $unit->getTotalLife() ?></span>
                                </div>
                                <div class="unit_hp_text_add">
                                    <span class="recdam"></span>
                                </div>
                            </div>
                            <div class="unit_cons">
                                <div class="unit_cons_bar2" style="width: <?= $this->getWidth($unit->getConcentration(), UnitInterface::MAX_CONS) ?>%;"></div>
                            </div>
                            <div class="unit_rage">
                                <div class="unit_rage_bar2" style="width: 100%;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="unit_box1_left">
                <div class="unit_box1_left2">
                    <div class="unit_ava" style="background: url(<?= $unit->getAvatar() ?>) no-repeat center; background-size: cover;">
                        <div id="ava40" class="unit_ava_blank"></div>
                        <div id="avas40" class="unit_ava_blank"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="unit_box2">
            <div class="unit_box2_right">
                <div class="unit_box2_right2">
                    <div class="unit_box2_right3">
                        <p><span class="unitcolor5"><?= $unit->getName() ?></span></p>
                    </div>
                </div>
                <div class="unit_effect_contant">
                    <p id="unit_effects_40"></p>
                </div>
            </div>
            <div class="unit_box2_left">
                <div class="unit_icon">
                    <div class="unit_icon_left">
                        1
                    </div>
                    <div class="unit_icon_right">
                        <img src="<?= $unit->getClass()->getSmallIcon() ?>" alt="">
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>