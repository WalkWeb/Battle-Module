<?php

use Battle\Result\ResultInterface;
use Battle\View\ViewException;

if (!isset($result) || !($result instanceof ResultInterface)) {
    throw new ViewException(ViewException::MISSING_RESULT);
}

// TODO Разделить html на шапку и остальное
?>

<html lang="ru">
<head>
    <title>Battle Module</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <link rel="stylesheet" type="text/css" href="/styles/main.css">
</head>
<body>

<?php foreach ($result->getChat()->getMessages() as $view): ?>
    <?= $view ?>
<?php endforeach; ?>

<?= '<h1>' . $result->getWinnerText() . '</h1>' ?>

<br />
<hr>

<h2>Statistics:</h2>

<?= '<p>Total rounds: ' . $result->getStatistic()->getRoundNumber() . '</p>' ?>
<?= '<p>Total stroke: ' . $result->getStatistic()->getStrokeNumber() . '</p>' ?>

<?php foreach ($result->getStatistic()->getUnitsStatistics() as $unit): ?>

    <?= '<p><b>' . $unit->getName() . '</b>' .
        '<br />Caused Damage: ' . $unit->getCausedDamage() .
        '<br />Taken Damage: ' . $unit->getTakenDamage() .
        '<br />Heal: ' . $unit->getHeal() .
        '<br />Killing: ' . $unit->getKilling() . '</p>'
    ?>

<?php endforeach; ?>

<p>Runtime: <?= $result->getStatistic()->getRuntime() ?> ms</p>
<p>Memory cost: <?= $result->getStatistic()->getMemoryCostClipped() ?></p>
