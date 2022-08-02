<?php

use Battle\Command\CommandInterface;
use Battle\Unit\Offense\OffenseInterface;
use Battle\Unit\UnitInterface;
use Battle\View\ViewException;
use Battle\View\ViewInterface;

if (empty($leftCommand) || !($leftCommand instanceof CommandInterface)) {
    throw new ViewException(ViewException::MISSING_COMMAND);
}

if (empty($rightCommand) || !($rightCommand instanceof CommandInterface)) {
    throw new ViewException(ViewException::MISSING_COMMAND);
}

if (empty($this) || !($this instanceof ViewInterface)) {
    throw new ViewException(ViewException::MISSING_VIEW);
}

/** @var UnitInterface $unit */

?>
<div class="units_stats_box">
    <table class="units_stats">
        <tr class="header">
            <td><p><?= $this->getTranslation()->trans('Command') ?></p></td>
            <td class="name"><p><?= $this->getTranslation()->trans('Name') ?></p></td>
            <td><p><?= $this->getTranslation()->trans('Race') ?></p></td>
            <td><p><abbr title="<?= $this->getTranslation()->trans('Life') ?>"><img src="/images/battle/stats_icon/life.png" alt=""></abbr></p></td>
            <td><p><abbr title="<?= $this->getTranslation()->trans('Mana') ?>"><img src="/images/battle/stats_icon/mana.png" alt=""></abbr></p></td>
            <td><p><abbr title="<?= $this->getTranslation()->trans('Mental Barrier') ?>"><img src="/images/battle/stats_icon/mental_barrier.png" alt=""></abbr></p></td>
            <td><p><?= $this->getTranslation()->trans('Type Damage') ?></p></td>
            <td><p><?= $this->getTranslation()->trans('Damage') ?></p></td>
            <td><p><abbr title="<?= $this->getTranslation()->trans('Attack Speed') ?>"><img src="/images/battle/stats_icon/attack_speed.png" alt=""></abbr></p></td>
            <td><p><abbr title="<?= $this->getTranslation()->trans('Accuracy') ?>"><img src="/images/battle/stats_icon/accuracy.png" alt=""></abbr></p></td>
            <td><p><abbr title="<?= $this->getTranslation()->trans('Magic Accuracy') ?>"><img src="/images/battle/stats_icon/magic_accuracy.png" alt=""></abbr></p></td>
            <td><p><abbr title="<?= $this->getTranslation()->trans('Defense') ?>"><img src="/images/battle/stats_icon/defence.png" alt=""></abbr></p></td>
            <td><p><abbr title="<?= $this->getTranslation()->trans('Magic Defense') ?>"><img src="/images/battle/stats_icon/magic_defence.png" alt=""></abbr></p></td>
            <td><p><abbr title="<?= $this->getTranslation()->trans('Block') ?>"><img src="/images/battle/stats_icon/block.png" alt=""></abbr></p></td>
            <td><p><abbr title="<?= $this->getTranslation()->trans('Magic Block') ?>"><img src="/images/battle/stats_icon/magic_block.png" alt=""></abbr></p></td>
            <td><p><?= $this->getTranslation()->trans('Concentration') ?></p></td>
            <td><p><?= $this->getTranslation()->trans('Rage') ?></p></td>
            <td><p><?= $this->getTranslation()->trans('Melee') ?>?</p></td>
            <td><p><?= $this->getTranslation()->trans('Action') ?>?</p></td>
            <td><p><?= $this->getTranslation()->trans('Alive') ?>?</p></td>
        </tr>
        <?php foreach (array_merge(iterator_to_array($leftCommand->getUnits()), iterator_to_array($rightCommand->getUnits())) as $unit): ?>
            <tr>
                <td><p><?= $unit->getCommand() ?></p></td>
                <td>
                    <p>
                        <img src="<?= $unit->getAvatar() ?>" class="stat_ava" alt="" />
                        <?= $unit->getName() ?>
                    </p>
                </td>
                <td><p><?= $this->getTranslation()->trans($unit->getRace()->getSingleName()) ?></p></td>
                <td><p><?= $unit->getLife() ?>/<?= $unit->getTotalLife() ?></p></td>
                <td><p><?= $unit->getMana() ?>/<?= $unit->getTotalMana() ?></p></td>
                <td><p><?= $unit->getDefense()->getMentalBarrier() ?>%</p></td>
                <td><p><?= ($unit->getOffense()->getTypeDamage() === OffenseInterface::TYPE_ATTACK ? $this->getTranslation()->trans('Attack') : $this->getTranslation()->trans('Spell')) ?></p></td>
                <td><p><?= $unit->getOffense()->getDamage() ?></p></td>
                <td><p><?= $unit->getOffense()->getAttackSpeed() ?></p></td>
                <td><p><?= $unit->getOffense()->getAccuracy() ?></p></td>
                <td><p><?= $unit->getOffense()->getMagicAccuracy() ?></p></td>
                <td><p><?= $unit->getDefense()->getDefense() ?></p></td>
                <td><p><?= $unit->getDefense()->getMagicDefense() ?></p></td>
                <td><p><?= $unit->getDefense()->getBlock() ?></p></td>
                <td><p><?= $unit->getDefense()->getMagicBlock() ?></p></td>
                <td><p><?= $unit->getConcentration() ?>/<?= $unit::MAX_CONCENTRATION ?></p></td>
                <td><p><?= $unit->getRage() ?>/<?= $unit::MAX_RAGE ?></p></td>
                <td><p><?= ($unit->isMelee() ? $this->getTranslation()->trans('Yes') : $this->getTranslation()->trans('No')) ?></p></td>
                <td><p><?= ($unit->isAction() ? $this->getTranslation()->trans('Yes') : $this->getTranslation()->trans('No')) ?></p></td>
                <td><p><?= ($unit->isAlive() ? $this->getTranslation()->trans('Yes') : $this->getTranslation()->trans('No')) ?></p></td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>