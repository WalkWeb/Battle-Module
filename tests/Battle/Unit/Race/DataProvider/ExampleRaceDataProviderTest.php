<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Race\DataProvider;

use Battle\Unit\Race\DataProvider\ExampleRaceDataProvider;
use Battle\Unit\Race\RaceException;
use Exception;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\AbstractTestCase;

class ExampleRaceDataProviderTest extends AbstractTestCase
{
    /**
     * Тест на успешное создание расы юнита на основе данных из RaceDataProvider
     *
     * @throws Exception
     */
    #[DataProvider('successDataProvider')]
    public function testExampleRaceDataProviderGetSuccess(int $raceId): void
    {
        $data = $this->getDataProvider()->get($raceId);

        $race = $this->container->getRaceFactory()->create($data);

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
    public static function successDataProvider(): array
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
        return new ExampleRaceDataProvider($this->container);
    }
}
