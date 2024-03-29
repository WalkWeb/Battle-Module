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
     * @var ContainerInterface
     */
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Создает расу юнита на основе массива параметров
     *
     * @param array $data
     * @return RaceInterface
     * @throws BattleException
     */
    public function create(array $data): RaceInterface
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
            $this->container
        );
    }
}
