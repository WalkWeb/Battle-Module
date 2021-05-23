<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Race;

use Battle\BattleException;
use Battle\Unit\Race\RaceException;
use Battle\Unit\Race\RaceFactory;
use PHPUnit\Framework\TestCase;

class RaceFactoryTest extends TestCase
{
    /**
     * @dataProvider successDataProvider
     * @param int $id
     * @throws BattleException
     * @throws RaceException
     */
    public function testRaceFactorySuccess(int $id): void
    {
        $race = RaceFactory::create($id);

        $data = RaceFactory::getData()[$id];

        self::assertEquals($data['id'], $race->getId());
        self::assertEquals($data['name'], $race->getName());
        self::assertEquals($data['single_name'], $race->getSingleName());
        self::assertEquals($data['color'], $race->getColor());
        self::assertEquals($data['icon'], $race->getIcon());
    }

    /**
     * @throws BattleException
     * @throws RaceException
     */
    public function testRaceFactoryFail(): void
    {
        $this->expectException(RaceException::class);
        $this->expectExceptionMessage(RaceException::UNDEFINED_RACE_ID);
        RaceFactory::create(10);
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
        ];
    }
}
