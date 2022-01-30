<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Battle\BattleFactory;
use Battle\View\ViewFactory;

$data = [
    [
        'id'           => '60f3c032-46a6-454d-ae3a-d066f150f6ef',
        'name'         => 'Titan',
        'level'        => 3,
        'avatar'       => '/images/avas/orcs/orc001.jpg',
        'damage'       => 35,
        'attack_speed' => 1.2,
        'life'         => 185,
        'total_life'   => 185,
        'melee'        => true,
        'command'      => 1,
        'class'        => 5,
        'race'         => 3,
    ],
    [
        'id'           => 'f7e84eab-e4f6-469f-b0e3-f5f965f9fbce',
        'name'         => 'Warrior',
        'level'        => 2,
        'avatar'       => '/images/avas/humans/human001.jpg',
        'damage'       => 28,
        'attack_speed' => 1.1,
        'life'         => 210,
        'total_life'   => 210,
        'melee'        => true,
        'command'      => 1,
        'class'        => 1,
        'race'         => 1,
    ],
    [
        'id'           => '2c58854d-e0ad-4d29-86e4-62bbb4b8d3b7',
        'name'         => 'Alchemist',
        'level'        => 3,
        'avatar'       => '/images/avas/dwarfs/dwarf004.jpg',
        'damage'       => 13,
        'attack_speed' => 1.1,
        'life'         => 75,
        'total_life'   => 75,
        'melee'        => false,
        'command'      => 1,
        'class'        => 6,
        'race'         => 4,
    ],
    [
        'id'           => 'e53b7edd-a4b5-49c4-81a2-050a1b7ecbea',
        'name'         => 'Warden',
        'level'        => 5,
        'avatar'       => '/images/avas/bosses/011.png',
        'damage'       => 40,
        'attack_speed' => 1,
        'life'         => 480,
        'total_life'   => 480,
        'melee'        => true,
        'command'      => 2,
        'class'        => 50,
        'race'         => 9,
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
