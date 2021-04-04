<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Battle\BattleFactory;
use Battle\View\ViewFactory;

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
        'attack_speed' => 1,
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

    $view = (new ViewFactory())->create();
    $battle = BattleFactory::create($data);
    $result = $battle->handle();
    echo $view->renderResult($result);

} catch (Exception $e) {
    die($e->getMessage());
}
