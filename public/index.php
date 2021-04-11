<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Battle\BattleFactory;
use Battle\View\ViewFactory;

$data = [
    [
        'id'           => '81941b8a-f7ca-447e-8951-36777ae6e79e',
        'name'         => 'Warrior',
        'avatar'       => '/images/avas/humans/human001.jpg',
        'damage'       => 25,
        'attack_speed' => 1.0,
        'life'         => 110,
        'melee'        => true,
        'class'        => 1,
        'command'      => 'left',
    ],
    [
        'id'           => '9ec07b22-7176-434c-a5d8-05f6adee4486',
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
        'id'           => 'bf75c4a3-b866-4787-88c7-8db57daf3d64',
        'name'         => 'Skeleton',
        'avatar'       => '/images/avas/monsters/005.png',
        'damage'       => 25,
        'attack_speed' => 1,
        'life'         => 65,
        'melee'        => true,
        'class'        => 1,
        'command'      => 'right',
    ],
    [
        'id'           => '54656319-f841-4ba5-8c71-e67c5afdd3e9',
        'name'         => 'Necro',
        'avatar'       => '/images/avas/monsters/006.png',
        'damage'       => 10,
        'attack_speed' => 1.0,
        'life'         => 62,
        'melee'        => false,
        'class'        => 2,
        'command'      => 'right',
    ],
];

try {

    $view = (new ViewFactory())->create();
    echo $view->renderHead();

    $battle = BattleFactory::create($data);
    $result = $battle->handle();
    echo $view->renderResult($result);

} catch (Exception $e) {
    die($e->getMessage());
}
