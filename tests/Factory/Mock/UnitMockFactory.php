<?php

declare(strict_types=1);

namespace Tests\Factory\Mock;

use Battle\Unit\Unit;
use Battle\Unit\UnitInterface;
use PHPUnit\Framework\MockObject\Exception;
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
     * @throws Exception
     */
    public function create(): UnitInterface
    {
        $mock = $this->createMock(Unit::class);

        $mock->expects($this->any())
            ->method('getId')
            ->willReturn('123', '456');

        $mock->expects($this->any())
            ->method('isAlive')
            ->willReturn(true, true, false);

        return $mock;
    }
}
