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
        'damage'       => 35,
        'attack_speed' => 0.8,
        'life'         => 250,
        'total_life'   => 250,
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
        'damage'       => 18,
        'attack_speed' => 1.0,
        'life'         => 95,
        'total_life'   => 95,
        'melee'        => false,
        'class'        => 2,
        'race'         => 1,
        'command'      => 1,
    ],
    [
        'id'           => '821a913e-f6a8-4dbf-bb8b-213ebc25c3a0',
        'name'         => 'Skeleton Warrior',
        'level'        => 3,
        'avatar'       => '/images/avas/monsters/005.png',
        'damage'       => 25,
        'attack_speed' => 1.2,
        'life'         => 75,
        'total_life'   => 75,
        'melee'        => true,
        'class'        => null,
        'race'         => 8,
        'command'      => 2,
    ],
    [
        'id'           => '57b11b16-c1fc-463a-a2a0-9dff8a7e2d5c',
        'name'         => 'Zombie',
        'level'        => 2,
        'avatar'       => '/images/avas/monsters/006.png',
        'damage'       => 23,
        'attack_speed' => 0.7,
        'life'         => 62,
        'total_life'   => 62,
        'melee'        => true,
        'class'        => null,
        'race'         => 8,
        'command'      => 2,
    ],
    [
        'id'           => '94a42e87-94d2-4a75-9760-ab2e22ee3bc6',
        'name'         => 'Dark Mage',
        'level'        => 2,
        'avatar'       => '/images/avas/monsters/001.png',
        'damage'       => 18,
        'attack_speed' => 1,
        'life'         => 57,
        'total_life'   => 57,
        'melee'        => false,
        'class'        => 4,
        'race'         => 8,
        'command'      => 2,
    ],
];

try {
    $battle = BattleFactory::create($data);
    $result = $battle->handle();

    $view = (new ViewFactory())->create($battle->getContainer()->getTranslation());
    echo $view->renderHead(); // example layout styles
    echo $view->renderResult($result);

} catch (Exception $e) {
    die($e->getMessage());
}
