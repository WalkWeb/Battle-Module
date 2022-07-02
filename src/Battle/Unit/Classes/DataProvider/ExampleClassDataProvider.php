<?php

declare(strict_types=1);

namespace Battle\Unit\Classes\DataProvider;

use Battle\Container\ContainerInterface;
use Battle\Unit\Ability\DataProvider\AbilityDataProviderInterface;
use Battle\Unit\Classes\UnitClassException;

/**
 * Пример простого поставщика данных по классу юнита, когда данные хранятся в самом классе. Сделан для примера - при
 * большом количестве классов редактировать его будет неудобно, плюс будет съедать много памяти. Лучше хранить данные в
 * базе, а поставщик в этом случае будет делать простой SELECT в базу и все.
 *
 * При этом можно сделать веб-интерфейс (в админ-панели), через который параметры классов можно будет изменять сразу в
 * браузере.
 *
 * @package Battle\Unit\Classes\DataProvider
 */
class ExampleClassDataProvider implements ClassDataProviderInterface
{
    /**
     * @var AbilityDataProviderInterface
     */
    private $abilityDataProvider;

    private static $data = [
        1 => [
            'id'         => 1,
            'name'       => 'Warrior',
            'small_icon' => '/images/icons/small/warrior.png',
            'abilities'  => [
                [
                    'name'  => 'Heavy Strike',
                    'level' => 1,
                ],
                [
                    'name'  => 'Blessed Shield',
                    'level' => 1,
                ],
            ],
        ],
    ];

    public function __construct(ContainerInterface $container)
    {
        $this->abilityDataProvider = $container->getAbilityDataProvider();
    }

    /**
     * Сам ClassDataProvider не хранит данные по способностям, он содержит только названия и уровни способностей (в
     * будущем уровни способности будут браться из $unit), а уже при формировании массива параметров запрашиваются
     * данные по нужным способностям в AbilityDataProvider
     *
     * @param int $id
     * @return array
     * @throws UnitClassException
     */
    public function get(int $id): array
    {
        if (!array_key_exists($id, self::$data)) {
            throw new UnitClassException(UnitClassException::UNDEFINED_CLASS_ID . ': ' . $id);
        }

        $data = self::$data[$id];

        foreach ($data['abilities'] as $i => $ability) {
            $data['abilities'][$i] = $this->abilityDataProvider->get($ability['name'], $ability['level']);
        }

        return $data;
    }
}
