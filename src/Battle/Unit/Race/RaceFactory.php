<?php

declare(strict_types=1);

namespace Battle\Unit\Race;

use Battle\BattleException;
use Battle\Container\ContainerInterface;
use Battle\Traits\ValidationTrait;

class RaceFactory
{
    use ValidationTrait;

    /**
     * Создает расу юнита на основе массива параметров
     *
     * @param array $data
     * @param ContainerInterface $container
     * @return RaceInterface
     * @throws BattleException
     */
    public static function create(array $data, ContainerInterface $container): RaceInterface
    {
        self::int($data, 'id', RaceException::INCORRECT_ID);
        self::string($data, 'name', RaceException::INCORRECT_NAME);
        self::string($data, 'single_name', RaceException::INCORRECT_SINGLE_NAME);
        self::string($data, 'color', RaceException::INCORRECT_COLOR);
        self::string($data, 'icon', RaceException::INCORRECT_ICON);
        self::array($data, 'abilities', RaceException::INCORRECT_ABILITIES);

        return new Race(
            $data['id'],
            $data['name'],
            $data['single_name'],
            $data['color'],
            $data['icon'],
            $data['abilities'],
            $container
        );
    }
}
