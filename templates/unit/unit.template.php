<?php

use Battle\Unit\UnitInterface;
use Battle\View\ViewException;

if (empty($unit) || !($unit instanceof UnitInterface)) {
    throw new ViewException(ViewException::MISSING_UNIT);
}

?>
<div class="unit">
    <table>
        <tr>
            <td rowspan="2" class="avatar_box" style="background-image: url(<?= $unit->getAvatar() ?>"></td>
            <td>Name</td>
            <td>Damage</td>
            <td>Life</td>
            <td>Cons</td>
            <td>Action?</td>
            <td>Alive?</td>
        </tr>
        <tr>
            <td><?= $unit->getName() ?></td>
            <td><?= $unit->getDamage() ?></td>
            <td class="life_bar">
                <div class="life_bar">
                    <div class="life" style="width: <?= $this->getWidth($unit->getLife(), $unit->getTotalLife()) ?>%;"></div>
                </div>
                <div class="life_text">
                    <?= $unit->getLife() . '/' . $unit->getTotalLife() ?>
                </div>
            </td>
            <td><?= $unit->getConcentration() . '/' . UnitInterface::MAX_CONS ?></td>
            <td class="<?= $this->getBgClass($unit->isAction()) ?>"><?= ($unit->isAction() ? 'yes' : 'no') ?></td>
            <td><?= ($unit->isAlive() ? 'yes' : 'no') ?></td>
        </tr>
    </table>
</div>