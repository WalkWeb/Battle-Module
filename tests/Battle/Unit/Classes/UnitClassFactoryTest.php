<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Classes;

use Battle\Result\Chat\Chat;
use Battle\Unit\Classes\UnitClassException;
use Battle\Unit\Classes\Human\Priest;
use Battle\Unit\Classes\Human\Warrior;
use Battle\Unit\Classes\Undead\DarkMage;
use Battle\Unit\Classes\Undead\DeadKnight;
use Battle\Unit\Classes\UnitClassFactory;
use Tests\AbstractUnitTest;

class UnitClassFactoryTest extends AbstractUnitTest
{
    /**
     * @dataProvider successDataProvider
     * @param int $classId
     * @param string $expectClassName
     * @throws UnitClassException
     */
    public function testUnitClassFactoryCreateSuccess(int $classId, string $expectClassName): void
    {
        $chat = new Chat();
        $class = UnitClassFactory::create($classId);
        $expectClass = new $expectClassName($chat);
        self::assertEquals($expectClass, $class);
    }

    /**
     * @throws UnitClassException
     */
    public function testUnitClassFactoryCreateFail(): void
    {
        $classId = 55;
        $this->expectException(UnitClassException::class);
        $this->expectExceptionMessage(UnitClassException::UNDEFINED_CLASS_ID . ': ' . $classId);
        UnitClassFactory::create($classId);
    }

    /**
     * Тест на некорректный класс юнита - когда класс не реализует интерфейс IncorrectUnitClassForTest
     *
     * @throws UnitClassException
     */
    public function testUnitClassFactoryIncorrectClass(): void
    {
        $classId = 100;
        $this->expectException(UnitClassException::class);
        $this->expectExceptionMessage(UnitClassException::INCORRECT_CLASS);
        UnitClassFactory::create($classId);
    }

    /**
     * @return array
     */
    public function successDataProvider(): array
    {
        return [
            [
                1,
                Warrior::class,
            ],
            [
                2,
                Priest::class,
            ],
            [
                3,
                DeadKnight::class,
            ],
            [
                4,
                DarkMage::class,
            ],
        ];
    }
}
