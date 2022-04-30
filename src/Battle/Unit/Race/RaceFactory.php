<?php

declare(strict_types=1);

namespace Battle\Unit\Race;

use Battle\BattleException;
use Battle\Traits\ValidationTrait;
use Battle\Unit\Ability\Effect\RageAbility;
use Battle\Unit\Ability\Resurrection\WillToLiveAbility;

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
            'abilities'   => [
                WillToLiveAbility::class,
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
                RageAbility::class,
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
        // other races
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

    /**
     * Создает расу на основании id
     *
     * @param int $id
     * @return RaceInterface
     * @throws BattleException
     * @throws RaceException
     */
    public static function createById(int $id): RaceInterface
    {
        if (!array_key_exists($id, self::$data)) {
            throw new RaceException(RaceException::UNDEFINED_RACE_ID);
        }

        return self::createByArray( self::$data[$id]);
    }

    /**
     * Создает расу на основании массива параметров
     *
     * @param array $data
     * @return RaceInterface
     * @throws BattleException
     * @throws RaceException
     */
    public static function createByArray(array $data): RaceInterface
    {
        self::existAndInt($data, 'id', RaceException::INCORRECT_ID);
        self::existAndString($data, 'name', RaceException::INCORRECT_NAME);
        self::existAndString($data, 'single_name', RaceException::INCORRECT_SINGLE_NAME);
        self::existAndString($data, 'color', RaceException::INCORRECT_COLOR);
        self::existAndString($data, 'icon', RaceException::INCORRECT_ICON);
        self::existAndArray($data, 'abilities', RaceException::INCORRECT_ABILITIES);

        foreach ($data['abilities'] as $abilityClass) {
            if (!is_string($abilityClass)) {
                throw new RaceException(RaceException::INCORRECT_ABILITY);
            }
        }

        return new Race(
            $data['id'],
            $data['name'],
            $data['single_name'],
            $data['color'],
            $data['icon'],
            $data['abilities']
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
