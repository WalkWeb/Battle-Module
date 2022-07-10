<?php

declare(strict_types=1);

namespace Battle\Unit\Classes;

use Battle\Container\ContainerInterface;
use Battle\Traits\ValidationTrait;
use Exception;

class UnitClassFactory
{
    use ValidationTrait;

    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Создает класс юнита на основе массива параметров. Массив параметров получается через ClassDataProvider
     *
     * От класса зависят способности, которые юнит будет применять в бою
     *
     * @param array $data
     * @return UnitClassInterface
     * @throws Exception
     */
    public function create(array $data): UnitClassInterface
    {
        self::int($data, 'id', UnitClassException::INVALID_ID_DATA);
        self::string($data, 'name', UnitClassException::INVALID_NAME_DATA);
        self::string($data, 'small_icon', UnitClassException::INVALID_SMALL_ICON_DATA);
        self::array($data, 'abilities', UnitClassException::INVALID_ABILITIES_DATA);

        return new UnitClass(
            $data['id'],
            $data['name'],
            $data['small_icon'],
            $data['abilities'],
            $this->container
        );
    }
}
