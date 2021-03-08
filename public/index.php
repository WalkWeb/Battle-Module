<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../public/style.html';

use Battle\Battle;
use Battle\Chat\Chat;
use Battle\Classes\ClassFactory;
use Battle\Command;
use Battle\Statistic\BattleStatistic;
use Battle\Unit\Unit;

try {
    $warrior = ClassFactory::create(1);
    $priest = ClassFactory::create(2);

    $unit1 = new Unit('User 1', 15, 1, 110, true, $warrior);
    $unit2 = new Unit('User 2', 12, 1, 95, false, $priest);
    $unit3 = new Unit('User 3', 25, 1.5, 165, true, $warrior);
    $unit4 = new Unit('User 4', 10, 1, 75, false, $priest);

    $leftCommand = new Command([$unit1, $unit2]);
    $rightCommand = new Command([$unit3, $unit4]);
    $chat = new Chat();

    $battle = new Battle($leftCommand, $rightCommand, new BattleStatistic(), $chat);

    $result = $battle->handle();

    $messages = $chat->getAll();

    foreach ($messages as $message) {
        echo '<p>' . $message . '</p>';
    }

    echo '<h1>' . $result->getWinnerTest() . '</h1>';

    echo '<p>Количество раундов: ' . $battle->getStatistics()->getRoundNumber() . '</p>';
    echo '<p>Количество ходов: ' . $battle->getStatistics()->getStrokeNumber() . '</p>';

    foreach ($battle->getStatistics()->getUnitsStatistics() as $unit) {
        echo
            '<p><b>' . $unit->getName() . '</b>' .
            '<br />Caused Damage: ' . $unit->getCausedDamage() .
            '<br />Taken Damage: ' . $unit->getTakenDamage() .
            '<br />Killing: ' . $unit->getKilling() . '</p>';
    }

} catch (Exception $e) {
    die($e->getMessage());
}
