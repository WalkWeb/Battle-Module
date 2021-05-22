<?php

use Battle\Result\ResultInterface;
use Battle\View\ViewException;

if (!isset($result) || !($result instanceof ResultInterface)) {
    throw new ViewException(ViewException::MISSING_RESULT);
}

foreach ($result->getFullLog()->getLog() as $view) {
    echo $view;
}

?>

<h1><?= $result->getWinnerText() ?></h1>

<div class="statistics_box">
    <table class="statistics">
        <tr class="header">
            <td colspan="5"><p><?= $result->getTranslation()->trans('Statistics') ?></p></td>
        </tr>
        <tr class="header">
            <td><p><span class="stat_unit"><?= $result->getTranslation()->trans('Unit') ?></span></p></td>
            <td><p><span class="stat_damage"><?= $result->getTranslation()->trans('Caused Damage') ?></span></p></td>
            <td><p><span class="stat_taken"><?= $result->getTranslation()->trans('Taken Damage') ?></span></p></td>
            <td><p><span class="stat_heal"><?= $result->getTranslation()->trans('Heal') ?></span></p></td>
            <td><p><span class="stat_kill"><?= $result->getTranslation()->trans('Killing') ?></span></p></td>
        </tr>
        <?php foreach ($result->getStatistic()->getUnitsStatistics() as $unit): ?>
        <tr>
            <td>
                <p>
                    <img src="<?= $unit->getUnit()->getAvatar() ?>" class="stat_ava" alt="" />
                    <span class="stat_unit">
                        <?= $unit->getUnit()->getName() ?>
                    </span>
                    <img src="/images/icons/damage.png" width="21" height="23" class="stat_damage" alt="" />
                    <?= $unit->getUnit()->getDamage() ?>
                    <img src="/images/icons/life.png" width="21" height="19" class="stat_damage" alt="" />
                    <?= $unit->getUnit()->getTotalLife() ?>
                </p>
            </td>
            <td><p><span class="stat_damage"><?= $unit->getCausedDamage() ?></span></p></td>
            <td><p><span class="stat_taken"><?= $unit->getTakenDamage() ?></span></p></td>
            <td><p><span class="stat_heal"><?= $unit->getHeal() ?></span></p></td>
            <td><p><span class="stat_kill"><?= $unit->getKilling() ?></span></p></td>
        </tr>
        <?php endforeach; ?>
        <tr>
            <td><p class="right"><?= $result->getTranslation()->trans('Total rounds') ?>:</p></td>
            <td colspan="4"><p class="left"><?= $result->getStatistic()->getRoundNumber() ?></p></td>
        </tr>
        <tr>
            <td><p class="right"><?= $result->getTranslation()->trans('Total stroke') ?>:</p></td>
            <td colspan="4"><p class="left"><?= $result->getStatistic()->getStrokeNumber() ?></p></td>
        </tr>
        <tr>
            <td><p class="right"><?= $result->getTranslation()->trans('Runtime') ?>:</p></td>
            <td colspan="4"><p class="left"><?= $result->getStatistic()->getRuntime() ?> ms</p></td>
        </tr>
        <tr>
            <td><p class="right"><?= $result->getTranslation()->trans('Memory cost') ?>:</p></td>
            <td colspan="4"><p class="left"><?= $result->getStatistic()->getMemoryCostClipped() ?></p></td>
        </tr>
    </table>
</div>
