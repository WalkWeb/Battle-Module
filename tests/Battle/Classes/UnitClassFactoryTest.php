<?php

declare(strict_types=1);

namespace Tests\Battle\Classes;

use Battle\Classes\ClassFactoryException;
use Battle\Classes\Human\Priest;
use Battle\Classes\Human\Warrior;
use Battle\Classes\UnitClassFactory;
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
        $class = UnitClassFactory::create($classId);
        $expectClass = new $expectClassName;
        self::assertEquals($expectClass, $class);
    }

    /**
     * @throws ClassFactoryException
     */
    public function testUnitClassFactoryCreateFail(): void
    {
        $classId = 3;
        $this->expectException(ClassFactoryException::class);
        $this->expectExceptionMessage(ClassFactoryException::UNDEFINED_CLASS_ID);
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
        ];
    }
}
