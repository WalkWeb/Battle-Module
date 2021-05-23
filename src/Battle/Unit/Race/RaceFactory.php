<?php

declare(strict_types=1);

namespace Battle\Unit\Race;

use Battle\BattleException;
use Battle\Traits\Validation;

class RaceFactory
{
    use Validation;

    private static $data = [
        // played races
        1 => [
            'id'          => 1,
            'name'        => 'People',
            'single_name' => 'Human',
            'color'       => '#000000',
            'icon'        => '',
        ],
        2 => [
            'id'          => 2,
            'name'        => 'Elves',
            'single_name' => 'Elf',
            'color'       => '#000000',
            'icon'        => '',
        ],
        3 => [
            'id'          => 3,
            'name'        => 'Orcs',
            'single_name' => 'Orc',
            'color'       => '#000000',
            'icon'        => '',
        ],
        4 => [
            'id'          => 4,
            'name'        => 'Gnomes',
            'single_name' => 'Gnome',
            'color'       => '#000000',
            'icon'        => '',
        ],
        5 => [
            'id'          => 5,
            'name'        => 'Angels',
            'single_name' => 'Angel',
            'color'       => '#000000',
            'icon'        => '',
        ],
        6 => [
            'id'          => 6,
            'name'        => 'Demons',
            'single_name' => 'Demon',
            'color'       => '#000000',
            'icon'        => '',
        ],
        // enemy races
        7 => [
            'id'          => 7,
            'name'        => 'Animals',
            'single_name' => 'Animal',
            'color'       => '#000000',
            'icon'        => '',
        ],
        8 => [
            'id'          => 8,
            'name'        => 'Undead',
            'single_name' => 'Undead',
            'color'       => '#000000',
            'icon'        => '',
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
}
