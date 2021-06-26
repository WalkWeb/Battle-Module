<?php

use Battle\Command\CommandInterface;
use Battle\View\ViewException;

if (empty($leftCommand) || !($leftCommand instanceof CommandInterface)) {
    throw new ViewException(ViewException::MISSING_COMMAND);
}

if (empty($rightCommand) || !($rightCommand instanceof CommandInterface)) {
    throw new ViewException(ViewException::MISSING_COMMAND);
}

?>
<div class="units_stats_box">
    <table class="units_stats">
        <tr class="header">
            <td><p>Command</p></td>
            <td><p>Name</p></td>
            <td><p>Race</p></td>
            <td><p>Life</p></td>
            <td><p>Damage</p></td>
            <td><p>Concentration</p></td>
            <td><p>Rage</p></td>
            <td><p>Melee?</p></td>
            <td><p>Action?</p></td>
            <td><p>Alive?</p></td>
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
                <td><p><?= $unit->getRace()->getName() ?></p></td>
                <td><p><?= $unit->getLife() ?>/<?= $unit->getTotalLife() ?></p></td>
                <td><p><?= $unit->getDamage() ?></p></td>
                <td><p><?= $unit->getConcentration() ?>/<?= $unit::MAX_CONS ?></p></td>
                <td><p><?= $unit->getRage() ?>/<?= $unit::MAX_RAGE ?></p></td>
                <td><p><?= ($unit->isMelee() ? 'Yes' : 'No') ?></p></td>
                <td><p><?= ($unit->isAction() ? 'Yes' : 'No') ?></p></td>
                <td><p><?= ($unit->isAlive() ? 'Yes' : 'No') ?></p></td>
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
                <td><p><?= $unit->getRace()->getName() ?></p></td>
                <td><p><?= $unit->getLife() ?>/<?= $unit->getTotalLife() ?></p></td>
                <td><p><?= $unit->getDamage() ?></p></td>
                <td><p><?= $unit->getConcentration() ?>/<?= $unit::MAX_CONS ?></p></td>
                <td><p><?= $unit->getRage() ?>/<?= $unit::MAX_RAGE ?></p></td>
                <td><p><?= ($unit->isMelee() ? 'Yes' : 'No') ?></p></td>
                <td><p><?= ($unit->isAction() ? 'Yes' : 'No') ?></p></td>
                <td><p><?= ($unit->isAlive() ? 'Yes' : 'No') ?></p></td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>