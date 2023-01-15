<?php

declare(strict_types=1);

namespace Tests\Factory\Mock;

use Battle\Unit\Unit;
use Battle\Unit\UnitInterface;
use PHPUnit\Framework\TestCase;

class UnitMockFactory extends TestCase
{
    /**
     * Единственное применение этому моку - в тесте CommandTest::testCommandGetUnitForActionBroken()
     *
     * Тест на необычную ситуацию, когда юниты в команде вначале сообщают, что есть готовые ходить, а при попытке
     * вернуть такого юнита - его нет
     *
     * @return UnitInterface
     */
    public function create(): UnitInterface
    {
        $mock = $this->createMock(Unit::class);

        $mock->expects(self::at(0))
            ->method('getId')
            ->willReturn('123');

        $mock->expects(self::at(1))
            ->method('getId')
            ->willReturn('456');

        $mock->expects(self::at(2))
            ->method('isAlive')
            ->willReturn(true);

        $mock->expects(self::at(3))
            ->method('isAction')
            ->willReturn(false);

        $mock->expects(self::at(4))
            ->method('isAlive')
            ->willReturn(true);

        $mock->expects(self::at(5))
            ->method('isAlive')
            ->willReturn(true);

        $mock->expects(self::at(6))
            ->method('isAction')
            ->willReturn(true);

        return $mock;
    }
}
