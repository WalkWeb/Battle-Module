<?php

declare(strict_types=1);

namespace Battle\Unit\Race;

use Battle\BattleException;
use Battle\Traits\ValidationTrait;

class RaceFactory
{
    use ValidationTrait;

    private static $data = [
        // played races
        1 => [
            'id'          => 1,
            'name'        => 'People',
            'single_name' => 'Human',
            'color'       => '#1e72e3',
            'icon'        => '',
        ],
        2 => [
            'id'          => 2,
            'name'        => 'Elves',
            'single_name' => 'Elf',
            'color'       => '#2f8528',
            'icon'        => '',
        ],
        3 => [
            'id'          => 3,
            'name'        => 'Orcs',
            'single_name' => 'Orc',
            'color'       => '#ae882d',
            'icon'        => '',
        ],
        4 => [
            'id'          => 4,
            'name'        => 'Gnomes',
            'single_name' => 'Gnome',
            'color'       => '#408cb3',
            'icon'        => '',
        ],
        5 => [
            'id'          => 5,
            'name'        => 'Angels',
            'single_name' => 'Angel',
            'color'       => '#2f9b5a',
            'icon'        => '',
        ],
        6 => [
            'id'          => 6,
            'name'        => 'Demons',
            'single_name' => 'Demon',
            'color'       => '#ba4829',
            'icon'        => '',
        ],
        // enemy races
        7 => [
            'id'          => 7,
            'name'        => 'Animals',
            'single_name' => 'Animal',
            'color'       => '#000000',
            'icon'        => '/images/icons/small/base-animal.png',
        ],
        8 => [
            'id'          => 8,
            'name'        => 'Undead',
            'single_name' => 'Undead',
            'color'       => '#953b39',
            'icon'        => '/images/icons/small/base-undead.png',
        ],
        9 => [
            'id'          => 9,
            'name'        => 'Inferno',
            'single_name' => 'Inferno',
            'color'       => '#ba4829',
            'icon'        => '/images/icons/small/base-inferno.png',
        ],
        10 => [
            'id'          => 10,
            'name'        => 'Golem',
            'single_name' => 'Golems',
            'color'       => '#7585a6',
            'icon'        => '/images/icons/small/base-golem.png',
        ],
    ];

    /**
     * @param int $id
     * @return RaceInterface
     * @throws RaceException
     * @throws BattleException
     */
    public static function create(int $id): RaceInterface
    {
        if (!array_key_exists($id, self::$data)) {
            throw new RaceException(RaceException::UNDEFINED_RACE_ID);
        }

        $data = self::$data[$id];

        self::existAndInt($data, 'id', RaceException::INCORRECT_ID);
        self::existAndString($data, 'name', RaceException::INCORRECT_NAME);
        self::existAndString($data, 'single_name', RaceException::INCORRECT_SINGLE_NAME);
        self::existAndString($data, 'color', RaceException::INCORRECT_COLOR);
        self::existAndString($data, 'icon', RaceException::INCORRECT_ICON);

        return new Race(
            $data['id'],
            $data['name'],
            $data['single_name'],
            $data['color'],
            $data['icon'],
        );
    }

    /**
     * @return array[]
     */
    public static function getData(): array
    {
        return self::$data;
    }
}
