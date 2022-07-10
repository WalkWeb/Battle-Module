<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Race\DataProvider;

use Battle\Container\Container;
use Battle\Unit\Race\DataProvider\ExampleRaceDataProvider;
use Battle\Unit\Race\RaceException;
use Exception;
use Tests\AbstractUnitTest;

class ExampleRaceDataProviderTest extends AbstractUnitTest
{
    /**
     * Тест на успешное создание расы юнита на основе данных из RaceDataProvider
     *
     * @dataProvider successDataProvider
     * @param int $raceId
     * @throws Exception
     */
    public function testExampleRaceDataProviderGetSuccess(int $raceId): void
    {
        $container = new Container();
        $data = $this->getDataProvider()->get($raceId);

        $race = $container->getRaceFactory()->create($data);

        self::assertEquals($data['id'], $race->getId());
        self::assertEquals($data['name'], $race->getName());
        self::assertEquals($data['single_name'], $race->getSingleName());
        self::assertEquals($data['color'], $race->getColor());
        self::assertEquals($data['icon'], $race->getIcon());
    }

    /**
     * Тест на ситуацию, когда передан неизвестный id расы
     *
     * @throws RaceException
     */
    public function testExampleRaceDataProviderUnknownId(): void
    {
        $raceId = 9999;
        $this->expectException(RaceException::class);
        $this->expectExceptionMessage(RaceException::UNDEFINED_RACE_ID . ': ' . $raceId);
        $this->getDataProvider()->get(9999);
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

    /**
     * @return ExampleRaceDataProvider
     */
    private function getDataProvider(): ExampleRaceDataProvider
    {
        return new ExampleRaceDataProvider(new Container());
    }
}
