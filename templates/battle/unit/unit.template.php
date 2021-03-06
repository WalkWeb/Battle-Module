<?php

use Battle\Unit\UnitInterface;
use Battle\View\ViewException;

if (empty($unit) || !($unit instanceof UnitInterface)) {
    throw new ViewException(ViewException::MISSING_UNIT);
}

?>
<div align="center">
    <div class="unit_main_box" id="usr_<?= $unit->getId() ?>">
        <div class="unit_box1">
            <div class="unit_box1_right">
                <div class="unit_box1_right2">
                    <div class="unit_box1_right3">
                        <div class="unit_box1_right4">
                            <div class="unit_hp">
                                <div id="hp_bar_bg_<?= $unit->getId() ?>" class="unit_hp_bar">
                                    <div id="hp_bar_<?= $unit->getId() ?>" class="unit_hp_bar2" style="width: <?= $this->getWidth($unit->getLife(), $unit->getTotalLife()) ?>%;"></div>
                                </div>
                                <div class="unit_hp_text">
                                    <span class="hp"><?= $unit->getLife() ?></span> / <span class="thp"><?= $unit->getTotalLife() ?></span>
                                </div>
                                <div class="unit_hp_text_add">
                                    <span class="recdam"></span>
                                </div>
                            </div>
                            <?php if ($unit->getClass()): ?>
                            <div class="unit_cons">
                                <div class="unit_cons_bar2" style="width: <?= $this->getWidth($unit->getConcentration(), UnitInterface::MAX_CONS) ?>%;"></div>
                            </div>
                            <div class="unit_rage">
                                <div class="unit_rage_bar2" style="width: <?= $this->getWidth($unit->getRage(), UnitInterface::MAX_RAGE) ?>%;"></div>
                            </div>
                            <?php else: ?>
                            <!-- TODO Доработать js-скрипт таким образом, чтобы эти элементы не были обязательными -->
                                <div class="unit_cons" style="display: none;">
                                    <div class="unit_cons_bar2" style="width: 0;"></div>
                                </div>
                                <div class="unit_rage" style="display: none;">
                                    <div class="unit_rage_bar2" style="width: 0;"></div>
                                </div>
                            <?php endif ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="unit_box1_left">
                <div class="unit_box1_left2">
                    <div class="unit_ava" style="background-image: url(<?= $unit->getAvatar() ?>);">
                        <div id="ava_<?= $unit->getId() ?>" class="unit_ava_blank"></div>
                        <div id="avas_<?= $unit->getId() ?>" class="unit_ava_blank"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="<?= $unit->getClass() ? 'unit_box2' : 'unit_box2_na' ?>">
            <div class="unit_box2_right">
                <div class="unit_box2_right2">
                    <div class="unit_box2_right3">
                        <p><span style="color: <?= $unit->getRace()->getColor() ?>"><?= $unit->getName() ?></span></p>
                    </div>
                </div>
                <div class="unit_effect_contant">
                    <p id="unit_effects_<?= $unit->getId() ?>"></p>
                </div>
            </div>
            <div class="unit_box2_left">
                <div class="unit_icon">
                    <div class="unit_icon_left"><?= $unit->getLevel() ?></div>
                    <div class="unit_icon_right">
                        <img src="<?= $unit->getIcon() ?>" alt="">
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>