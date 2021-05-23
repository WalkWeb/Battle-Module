<?php

declare(strict_types=1);

namespace Tests\Battle\Classes;

use Battle\Classes\ClassFactoryException;
use Battle\Classes\Human\Priest;
use Battle\Classes\Human\Warrior;
use Battle\Classes\Undead\DarkMage;
use Battle\Classes\Undead\DeadKnight;
use Battle\Classes\UnitClassFactory;
use Battle\Result\Chat\Message;
use PHPUnit\Framework\TestCase;

class UnitClassFactoryTest extends TestCase
{
    /**
     * @dataProvider successDataProvider
     * @param int $classId
     * @param string $expectClassName
     * @throws ClassFactoryException
     */
    public function testUnitClassFactoryCreateSuccess(int $classId, string $expectClassName): void
    {
        $message = new Message();
        $class = UnitClassFactory::create($classId);
        $expectClass = new $expectClassName($message);
        self::assertEquals($expectClass, $class);
    }

    /**
     * @throws ClassFactoryException
     */
    public function testUnitClassFactoryCreateFail(): void
    {
        $classId = 55;
        $this->expectException(ClassFactoryException::class);
        $this->expectExceptionMessage(ClassFactoryException::UNDEFINED_CLASS_ID . ': ' . $classId);
        UnitClassFactory::create($classId);
    }

    /**
     * Тест на некорректный класс юнита - когда класс не реализует интерфейс IncorrectUnitClassForTest
     *
     * @throws ClassFactoryException
     */
    public function testUnitClassFactoryIncorrectClass(): void
    {
        $classId = 100;
        $this->expectException(ClassFactoryException::class);
        $this->expectExceptionMessage(ClassFactoryException::INCORRECT_CLASS);
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
