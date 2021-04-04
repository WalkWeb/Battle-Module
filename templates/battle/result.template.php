<?php

use Battle\Result\ResultInterface;
use Battle\View\ViewException;

if (!isset($result) || !($result instanceof ResultInterface)) {
    throw new ViewException(ViewException::MISSING_RESULT);
}
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

<?= '<p>Количество раундов: ' . $result->getStatistic()->getRoundNumber() . '</p>' ?>
<?= '<p>Количество ходов: ' . $result->getStatistic()->getStrokeNumber() . '</p>' ?>



<?php foreach ($result->getStatistic()->getUnitsStatistics() as $unit): ?>

    <?= '<p><b>' . $unit->getName() . '</b>' .
        '<br />Caused Damage: ' . $unit->getCausedDamage() .
        '<br />Taken Damage: ' . $unit->getTakenDamage() .
        '<br />Heal: ' . $unit->getHeal() .
        '<br />Killing: ' . $unit->getKilling() . '</p>'
    ?>

<?php endforeach; ?>

<p>На обработку боя ушло: <?= $result->getStatistic()->getRuntime() ?> ms</p>
<p>Расход памяти: <?= $result->getStatistic()->getMemoryCostClipped() ?></p>
