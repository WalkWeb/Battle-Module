<?php

declare(strict_types=1);

namespace Battle\Unit\Race\DataProvider;

use Battle\Container\ContainerInterface;
use Battle\Unit\Ability\DataProvider\AbilityDataProviderInterface;
use Battle\Unit\Race\RaceException;

/**
 * Пример простого поставщика данных по расе юнита, когда данные хранятся в самом классе. Сделан для примера.
 *
 * @package Battle\Unit\Classes\DataProvider
 */
class ExampleRaceDataProvider implements RaceDataProviderInterface
{
    /**
     * @var AbilityDataProviderInterface
     */
    private $abilityDataProvider;

    private static $data = [
        1 => [
            'id'          => 1,
            'name'        => 'People',
            'single_name' => 'Human',
            'color'       => '#1e72e3',
            'icon'        => '',
            'abilities'   => [
                [
                    'name'  => 'Will to live',
                    'level' => 1,
                ],
            ],
        ],
        2 => [
            'id'          => 2,
            'name'        => 'Elves',
            'single_name' => 'Elf',
            'color'       => '#2f8528',
            'icon'        => '',
            'abilities'   => [],
        ],
        3 => [
            'id'          => 3,
            'name'        => 'Orcs',
            'single_name' => 'Orc',
            'color'       => '#ae882d',
            'icon'        => '',
            'abilities'   => [
                [
                    'name'  => 'Rage',
                    'level' => 1,
                ],
            ],
        ],
        4 => [
            'id'          => 4,
            'name'        => 'Gnomes',
            'single_name' => 'Gnome',
            'color'       => '#408cb3',
            'icon'        => '',
            'abilities'   => [],
        ],
        5 => [
            'id'          => 5,
            'name'        => 'Angels',
            'single_name' => 'Angel',
            'color'       => '#2f9b5a',
            'icon'        => '',
            'abilities'   => [],
        ],
        6 => [
            'id'          => 6,
            'name'        => 'Demons',
            'single_name' => 'Demon',
            'color'       => '#ba4829',
            'icon'        => '',
            'abilities'   => [],
        ],
        7 => [
            'id'          => 7,
            'name'        => 'Animals',
            'single_name' => 'Animal',
            'color'       => '#000000',
            'icon'        => '/images/icons/small/base-animal.png',
            'abilities'   => [],
        ],
        8 => [
            'id'          => 8,
            'name'        => 'Undead',
            'single_name' => 'Undead',
            'color'       => '#953b39',
            'icon'        => '/images/icons/small/base-undead.png',
            'abilities'   => [],
        ],
        9 => [
            'id'          => 9,
            'name'        => 'Inferno',
            'single_name' => 'Inferno',
            'color'       => '#ba4829',
            'icon'        => '/images/icons/small/base-inferno.png',
            'abilities'   => [],
        ],
        10 => [
            'id'          => 10,
            'name'        => 'Golem',
            'single_name' => 'Golems',
            'color'       => '#7585a6',
            'icon'        => '/images/icons/small/base-golem.png',
            'abilities'   => [],
        ],
    ];

    public function __construct(ContainerInterface $container)
    {
        $this->abilityDataProvider = $container->getAbilityDataProvider();
    }

    /**
     * @param int $id
     * @return array
     * @throws RaceException
     */
    public function get(int $id): array
    {
        if (!array_key_exists($id, self::$data)) {
            throw new RaceException(RaceException::UNDEFINED_RACE_ID . ': ' . $id);
        }

        $data = self::$data[$id];

        foreach ($data['abilities'] as $i => $ability) {
            $data['abilities'][$i] = $this->abilityDataProvider->get($ability['name'], $ability['level']);
        }

        return $data;
    }
}
