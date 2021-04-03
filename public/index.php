<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../public/html.html';

use Battle\BattleFactory;

$data = [
    [
        'name'         => 'Warrior',
        'avatar'       => '/images/avas/humans/human001.jpg',
        'damage'       => 15,
        'attack_speed' => 1.0,
        'life'         => 110,
        'melee'        => true,
        'class'        => 1,
        'command'      => 'left',
    ],
    [
        'name'         => 'Priest',
        'avatar'       => '/images/avas/humans/human004.jpg',
        'damage'       => 12,
        'attack_speed' => 1.0,
        'life'         => 95,
        'melee'        => false,
        'class'        => 2,
        'command'      => 'left',
    ],
    [
        'name'         => 'Skeleton',
        'avatar'       => '/images/avas/monsters/005.png',
        'damage'       => 25,
        'attack_speed' => 1.5,
        'life'         => 165,
        'melee'        => true,
        'class'        => 1,
        'command'      => 'right',
    ],
    [
        'name'         => 'Necro',
        'avatar'       => '/images/avas/monsters/006.png',
        'damage'       => 10,
        'attack_speed' => 1.0,
        'life'         => 80,
        'melee'        => false,
        'class'        => 2,
        'command'      => 'right',
    ],
];

try {

    $battle = BattleFactory::create($data);
    $result = $battle->handle();
    $views = $result->getChat()->getMessages();

    foreach ($views as $view) {
        echo $view;
    }

    echo '<h1>' . $result->getWinnerText() . '</h1>';

    echo '<p>Количество раундов: ' . $result->getStatistic()->getRoundNumber() . '</p>';
    echo '<p>Количество ходов: ' . $result->getStatistic()->getStrokeNumber() . '</p>';

    foreach ($result->getStatistic()->getUnitsStatistics() as $unit) {
        echo
            '<p><b>' . $unit->getName() . '</b>' .
            '<br />Caused Damage: ' . $unit->getCausedDamage() .
            '<br />Taken Damage: ' . $unit->getTakenDamage() .
            '<br />Heal: ' . $unit->getHeal() .
            '<br />Killing: ' . $unit->getKilling() . '</p>';
    }

    echo '<p>На обработку боя ушло: ' . $result->getStatistic()->getRuntime() . ' ms</p>';
    echo '<p>Расход памяти: ' . $result->getStatistic()->getMemoryCostClipped() . '</p>';

} catch (Exception $e) {
    die($e->getMessage());
}
