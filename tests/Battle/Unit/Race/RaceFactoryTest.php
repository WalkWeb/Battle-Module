<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Race;

use Battle\BattleException;
use Battle\Unit\Race\RaceException;
use Battle\Unit\Race\RaceFactory;
use Exception;
use Tests\AbstractUnitTest;

class RaceFactoryTest extends AbstractUnitTest
{
    /**
     * Тест на успешное создание расы
     *
     * @dataProvider successDataProvider
     * @param int $id
     * @throws BattleException
     * @throws RaceException
     */
    public function testRaceFactorySuccess(int $id): void
    {
        $race = RaceFactory::createById($id);
        $data = RaceFactory::getData()[$id];

        self::assertEquals($data['id'], $race->getId());
        self::assertEquals($data['name'], $race->getName());
        self::assertEquals($data['single_name'], $race->getSingleName());
        self::assertEquals($data['color'], $race->getColor());
        self::assertEquals($data['icon'], $race->getIcon());
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
        RaceFactory::createByArray($data);
    }

    /**
     * Тест на ситуацию, когда передан id неизвестной расы
     *
     * @throws BattleException
     * @throws RaceException
     */
    public function testRaceFactoryUndefinedRaceId(): void
    {
        $this->expectException(RaceException::class);
        $this->expectExceptionMessage(RaceException::UNDEFINED_RACE_ID);
        RaceFactory::createById(888);
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
}
