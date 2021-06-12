<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Battle\BattleFactory;
use Battle\View\ViewFactory;

$data = [
    [
        'id'           => '81941b8a-f7ca-447e-8951-36777ae6e79e',
        'name'         => 'Warrior',
        'level'        => 3,
        'avatar'       => '/images/avas/humans/human001.jpg',
        'damage'       => 25,
        'attack_speed' => 0.8,
        'life'         => 110,
        'total_life'   => 110,
        'melee'        => true,
        'class'        => 1,
        'race'         => 1,
        'command'      => 1,
    ],
    [
        'id'           => '9ec07b22-7176-434c-a5d8-05f6adee4486',
        'name'         => 'Priest',
        'level'        => 2,
        'avatar'       => '/images/avas/humans/human004.jpg',
        'damage'       => 12,
        'attack_speed' => 1.0,
        'life'         => 95,
        'total_life'   => 95,
        'melee'        => false,
        'class'        => 2,
        'race'         => 1,
        'command'      => 1,
    ],
    [
        'id'           => 'bf75c4a3-b866-4787-88c7-8db57daf3d64',
        'name'         => 'Skeleton',
        'level'        => 2,
        'avatar'       => '/images/avas/monsters/005.png',
        'damage'       => 20,
        'attack_speed' => 1.2,
        'life'         => 65,
        'total_life'   => 65,
        'melee'        => true,
        'class'        => null,
        'race'         => 8,
        'command'      => 2,
    ],
    [
        'id'           => '54656319-f841-4ba5-8c71-e67c5afdd3e9',
        'name'         => 'Necromancer',
        'level'        => 1,
        'avatar'       => '/images/avas/monsters/001.png',
        'damage'       => 10,
        'attack_speed' => 1.0,
        'life'         => 62,
        'total_life'   => 62,
        'melee'        => false,
        'class'        => 4,
        'race'         => 8,
        'command'      => 2,
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
