<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Race;

use Battle\BattleException;
use Battle\Container\Container;
use Battle\Container\ContainerException;
use Battle\Unit\Ability\AbilityCollection;
use Battle\Unit\Ability\AbilityFactory;
use Battle\Unit\Race\DataProvider\RaceDataProviderInterface;
use Battle\Unit\Race\RaceException;
use Battle\Unit\Race\RaceFactory;
use Battle\Unit\UnitInterface;
use Exception;
use Tests\AbstractUnitTest;
use Tests\Battle\Factory\UnitFactory;

class RaceFactoryTest extends AbstractUnitTest
{
    /**
     * Тест на успешное создание расы
     *
     * @dataProvider successDataProvider
     * @param int $id
     * @throws BattleException
     * @throws Exception
     */
    public function testRaceFactorySuccess(int $id): void
    {
        $container = new Container();
        $data = $this->getDataProvider()->get($id);
        $race = RaceFactory::create($data, $container);

        self::assertEquals($data['id'], $race->getId());
        self::assertEquals($data['name'], $race->getName());
        self::assertEquals($data['single_name'], $race->getSingleName());
        self::assertEquals($data['color'], $race->getColor());
        self::assertEquals($data['icon'], $race->getIcon());

        // У некоторых рас есть расовые навыки. Чтобы сравнить ожидаемые и фактические нужен юнит определенной расы
        // По этому ниже идут такие замороченные проверки. Врожденные навыки есть у расы людей (id: 1) и орков (id: 3)
        if ($id === 1 || $id === 3) {

            $unit = $id === 1 ? UnitFactory::createByTemplate(1) : UnitFactory::createByTemplate(21);

            $expectedAbilities = $this->createAbilityCollection($unit, $data['abilities']);

            self::assertSameSize($expectedAbilities, $race->getAbilities($unit));

            self::assertEquals(
                $expectedAbilities,
                $race->getAbilities($unit)
            );
        }
    }

    /**
     * Тест на различные варианты невалидных данных для создания расы
     *
     * @dataProvider failDataProvider
     * @param array $data
     * @param string $error
     * @throws Exception
     */
    public function testRaceFactoryFail(array $data, string $error): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage($error);
        RaceFactory::create($data, new Container());
    }

    /**
     * @return array
     */
    public function successDataProvider(): array
    {
        return [
            [1],
            [2],
            [3],
            [4],
            [5],
            [6],
            [7],
            [8],
            [9],
            [10],
        ];
    }

    public function failDataProvider(): array
    {
        return [
            [
                // Отсутствует id
                [
                    'name'        => 'People',
                    'single_name' => 'Human',
                    'color'       => '#1e72e3',
                    'icon'        => '',
                    'abilities'   => [],
                ],
                RaceException::INCORRECT_ID,
            ],
            [
                // id некорректного типа
                [
                    'id'          => '1',
                    'name'        => 'People',
                    'single_name' => 'Human',
                    'color'       => '#1e72e3',
                    'icon'        => '',
                    'abilities'   => [],
                ],
                RaceException::INCORRECT_ID,
            ],
            [
                // отсутствует name
                [
                    'id'          => 1,
                    'single_name' => 'Human',
                    'color'       => '#1e72e3',
                    'icon'        => '',
                    'abilities'   => [],
                ],
                RaceException::INCORRECT_NAME,
            ],
            [
                // name некорректного типа
                [
                    'id'          => 1,
                    'name'        => ['People'],
                    'single_name' => 'Human',
                    'color'       => '#1e72e3',
                    'icon'        => '',
                    'abilities'   => [],
                ],
                RaceException::INCORRECT_NAME,
            ],
            [
                // отсутствует single_name
                [
                    'id'          => 1,
                    'name'        => 'People',
                    'color'       => '#1e72e3',
                    'icon'        => '',
                    'abilities'   => [],
                ],
                RaceException::INCORRECT_SINGLE_NAME,
            ],
            [
                // single_name некорректного типа
                [
                    'id'          => 1,
                    'name'        => 'People',
                    'single_name' => true,
                    'color'       => '#1e72e3',
                    'icon'        => '',
                    'abilities'   => [],
                ],
                RaceException::INCORRECT_SINGLE_NAME,
            ],
            [
                // отсутствует color
                [
                    'id'          => 1,
                    'name'        => 'People',
                    'single_name' => 'Human',
                    'icon'        => '',
                    'abilities'   => [],
                ],
                RaceException::INCORRECT_COLOR,
            ],
            [
                // color некорректного типа
                [
                    'id'          => 1,
                    'name'        => 'People',
                    'single_name' => 'Human',
                    'color'       => 12,
                    'icon'        => '',
                    'abilities'   => [],
                ],
                RaceException::INCORRECT_COLOR,
            ],
            [
                // отсутствует icon
                [
                    'id'          => 1,
                    'name'        => 'People',
                    'single_name' => 'Human',
                    'color'       => '#1e72e3',
                    'abilities'   => [],
                ],
                RaceException::INCORRECT_ICON,
            ],
            [
                // icon некорректного типа
                [
                    'id'          => 1,
                    'name'        => 'People',
                    'single_name' => 'Human',
                    'color'       => '#1e72e3',
                    'icon'        => null,
                    'abilities'   => [],
                ],
                RaceException::INCORRECT_ICON,
            ],
            [
                // отсутствует abilities
                [
                    'id'          => 1,
                    'name'        => 'People',
                    'single_name' => 'Human',
                    'color'       => '#1e72e3',
                    'icon'        => '',
                ],
                RaceException::INCORRECT_ABILITIES,
            ],
            [
                // abilities некорректного типа
                [
                    'id'          => 1,
                    'name'        => 'People',
                    'single_name' => 'Human',
                    'color'       => '#1e72e3',
                    'icon'        => '',
                    'abilities'   => '[]',
                ],
                RaceException::INCORRECT_ABILITIES,
            ],
        ];
    }

    /**
     * @return RaceDataProviderInterface
     * @throws ContainerException
     */
    private function getDataProvider(): RaceDataProviderInterface
    {
        return (new Container())->getRaceDataProvider();
    }

    /**
     * @param UnitInterface $unit
     * @param array $abilitiesData
     * @return AbilityCollection
     * @throws Exception
     */
    private function createAbilityCollection(UnitInterface $unit, array $abilitiesData): AbilityCollection
    {
        $collection = new AbilityCollection();
        $factory = new AbilityFactory();

        foreach ($abilitiesData as $abilityData) {
            $collection->add($factory->create($unit, $abilityData));
        }

        return $collection;
    }
}
