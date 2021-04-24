<?php

use Battle\Result\ResultInterface;
use Battle\View\ViewException;

if (!isset($result) || !($result instanceof ResultInterface)) {
    throw new ViewException(ViewException::MISSING_RESULT);
}

foreach ($result->getChat()->getMessages() as $view) {
    echo $view;
}

echo '<h1>' . $result->getWinnerText() . '</h1>';

?>

<br />
<hr>

<h2>Statistics:</h2>

<div class="statistics_box">
    <table class="statistics">
        <tr class="header">
            <td><p><span class="stat_unit">Unit</span></p></td>
            <td><p><span class="stat_damage">Caused Damage</span></p></td>
            <td><p><span class="stat_taken">Taken Damage</span></p></td>
            <td><p><span class="stat_heal">Heal</span></p></td>
            <td><p><span class="stat_kill">Killing</span></p></td>
        </tr>
        <?php foreach ($result->getStatistic()->getUnitsStatistics() as $unit): ?>
        <tr>
            <td><p><span class="stat_unit"><?= $unit->getName() ?></span></p></td>
            <td><p><span class="stat_damage"><?= $unit->getCausedDamage() ?></span></p></td>
            <td><p><span class="stat_taken"><?= $unit->getTakenDamage() ?></span></p></td>
            <td><p><span class="stat_heal"><?= $unit->getHeal() ?></span></p></td>
            <td><p><span class="stat_kill"><?= $unit->getKilling() ?></span></p></td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<p>Total rounds: <?= $result->getStatistic()->getRoundNumber() ?></p>
<p>Total stroke: <?= $result->getStatistic()->getStrokeNumber() ?></p>
<p>Runtime: <?= $result->getStatistic()->getRuntime() ?> ms</p>
<p>Memory cost: <?= $result->getStatistic()->getMemoryCostClipped() ?></p>
