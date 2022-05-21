<?php

use Battle\Command\CommandInterface;
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
            <td><p><?= $this->getTranslation()->trans('Name') ?></p></td>
            <td><p><?= $this->getTranslation()->trans('Race') ?></p></td>
            <td><p><?= $this->getTranslation()->trans('Life') ?></p></td>
            <td><p><?= $this->getTranslation()->trans('Damage') ?></p></td>
            <td><p><?= $this->getTranslation()->trans('Attack Speed') ?></p></td>
            <td><p><?= $this->getTranslation()->trans('Block') ?></p></td>
            <td><p><?= $this->getTranslation()->trans('Concentration') ?></p></td>
            <td><p><?= $this->getTranslation()->trans('Rage') ?></p></td>
            <td><p><?= $this->getTranslation()->trans('Melee') ?>?</p></td>
            <td><p><?= $this->getTranslation()->trans('Action') ?>?</p></td>
            <td><p><?= $this->getTranslation()->trans('Alive') ?>?</p></td>
        </tr>
        <?php foreach ($leftCommand->getUnits() as $unit): ?>
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
                <td><p><?= $unit->getOffense()->getDamage() ?></p></td>
                <td><p><?= $unit->getOffense()->getAttackSpeed() ?></p></td>
                <td><p><?= $unit->getDefense()->getBlock() ?></p></td>
                <td><p><?= $unit->getConcentration() ?>/<?= $unit::MAX_CONCENTRATION ?></p></td>
                <td><p><?= $unit->getRage() ?>/<?= $unit::MAX_RAGE ?></p></td>
                <td><p><?= ($unit->isMelee() ? $this->getTranslation()->trans('Yes') : $this->getTranslation()->trans('No')) ?></p></td>
                <td><p><?= ($unit->isAction() ? $this->getTranslation()->trans('Yes') : $this->getTranslation()->trans('No')) ?></p></td>
                <td><p><?= ($unit->isAlive() ? $this->getTranslation()->trans('Yes') : $this->getTranslation()->trans('No')) ?></p></td>
            </tr>
        <?php endforeach; ?>
        <?php foreach ($rightCommand->getUnits() as $unit): ?>
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
                <td><p><?= $unit->getOffense()->getDamage() ?></p></td>
                <td><p><?= $unit->getOffense()->getAttackSpeed() ?></p></td>
                <td><p><?= $unit->getDefense()->getBlock() ?></p></td>
                <td><p><?= $unit->getConcentration() ?>/<?= $unit::MAX_CONCENTRATION ?></p></td>
                <td><p><?= $unit->getRage() ?>/<?= $unit::MAX_RAGE ?></p></td>
                <td><p><?= ($unit->isMelee() ? $this->getTranslation()->trans('Yes') : $this->getTranslation()->trans('No')) ?></p></td>
                <td><p><?= ($unit->isAction() ? $this->getTranslation()->trans('Yes') : $this->getTranslation()->trans('No')) ?></p></td>
                <td><p><?= ($unit->isAlive() ? $this->getTranslation()->trans('Yes') : $this->getTranslation()->trans('No')) ?></p></td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>