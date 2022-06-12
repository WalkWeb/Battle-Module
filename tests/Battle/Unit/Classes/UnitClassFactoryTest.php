<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Classes;

use Battle\Unit\Classes\UnitClassException;
use Battle\Unit\Classes\Human\Priest;
use Battle\Unit\Classes\Human\Warrior;
use Battle\Unit\Classes\Undead\DarkMage;
use Battle\Unit\Classes\Undead\DeadKnight;
use Battle\Unit\Classes\UnitClassFactory;
use Exception;
use Tests\AbstractUnitTest;

class UnitClassFactoryTest extends AbstractUnitTest
{
    /**
     * Тест на создание класса по ID класса
     *
     * @dataProvider createByIdSuccessDataProvider
     * @param int $classId
     * @param string $expectClassName
     * @throws Exception
     */
    public function testUnitClassFactoryCreateByIdSuccess(int $classId, string $expectClassName): void
    {
        $class = UnitClassFactory::createById($classId);
        $expectClass = new $expectClassName();
        self::assertEquals($expectClass, $class);
    }

    /**
     * @throws Exception
     */
    public function testUnitClassFactoryCreateByIdFail(): void
    {
        $classId = 55;
        $this->expectException(UnitClassException::class);
        $this->expectExceptionMessage(UnitClassException::UNDEFINED_CLASS_ID . ': ' . $classId);
        UnitClassFactory::createById($classId);
    }

    /**
     * Тест на некорректный класс юнита - когда класс не реализует интерфейс IncorrectUnitClassForTest
     *
     * @throws Exception
     */
    public function testUnitClassFactoryCreateByIdIncorrectClass(): void
    {
        $classId = 100;
        $this->expectException(UnitClassException::class);
        $this->expectExceptionMessage(UnitClassException::INCORRECT_CLASS);
        UnitClassFactory::createById($classId);
    }

    /**
     * Тест на создание класса на основе массива параметров
     *
     * @dataProvider createByArraySuccessDataProvider
     * @param array $data
     * @throws Exception
     */
    public function testUnitClassFactoryCreateByArraySuccess(array $data): void
    {
        $class = UnitClassFactory::createByArray($data);

        // Проверка базовых параметров
        self::assertEquals($data['id'], $class->getId());
        self::assertEquals($data['name'], $class->getName());
        self::assertEquals($data['small_icon'], $class->getSmallIcon());

        // Проверка способностей делается в UnitClassTest::testUnitClassCreate()
    }

    /**
     * Тесты на различные варианты невалидных данных
     *
     * @dataProvider createByArrayFailDataProvider
     * @param array $data
     * @param string $error
     */
    public function testUnitClassFactoryCreateByArrayFail(array $data, string $error): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage($error);
        UnitClassFactory::createByArray($data);
    }

    /**
     * @return array
     */
    public function createByIdSuccessDataProvider(): array
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

    /**
     * @return array
     */
    public function createByArraySuccessDataProvider(): array
    {
        return [
            [
                [
                    'id'         => 1,
                    'name'       => 'Warrior',
                    'small_icon' => '/images/icons/small/warrior.png',
                    'abilities'  => [],
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function createByArrayFailDataProvider(): array
    {
        return [
            [
                // Отсутствует id
                [
                    'name'       => 'Warrior',
                    'small_icon' => '/images/icons/small/warrior.png',
                    'abilities'  => [],
                ],
                UnitClassException::INVALID_ID_DATA,
            ],
            [
                // id некорректного типа
                [
                    'id'         => '1',
                    'name'       => 'Warrior',
                    'small_icon' => '/images/icons/small/warrior.png',
                    'abilities'  => [],
                ],
                UnitClassException::INVALID_ID_DATA,
            ],
            [
                // Отсутствует name
                [
                    'id'         => 1,
                    'small_icon' => '/images/icons/small/warrior.png',
                    'abilities'  => [],
                ],
                UnitClassException::INVALID_NAME_DATA,
            ],
            [
                // name некорректного типа
                [
                    'id'         => 1,
                    'name'       => true,
                    'small_icon' => '/images/icons/small/warrior.png',
                    'abilities'  => [],
                ],
                UnitClassException::INVALID_NAME_DATA,
            ],
            [
                // Отсутствует small_icon
                [
                    'id'         => 1,
                    'name'       => 'Warrior',
                    'abilities'  => [],
                ],
                UnitClassException::INVALID_SMALL_ICON_DATA,
            ],
            [
                // small_icon некорректного типа
                [
                    'id'         => 1,
                    'name'       => 'Warrior',
                    'small_icon' => null,
                    'abilities'  => [],
                ],
                UnitClassException::INVALID_SMALL_ICON_DATA,
            ],
            [
                // Отсутствует abilities
                [
                    'id'         => 1,
                    'name'       => 'Warrior',
                    'small_icon' => '/images/icons/small/warrior.png',
                ],
                UnitClassException::INVALID_ABILITIES_DATA,
            ],
            [
                // abilities некорректного типа
                [
                    'id'         => 1,
                    'name'       => 'Warrior',
                    'small_icon' => '/images/icons/small/warrior.png',
                    'abilities'  => 100,
                ],
                UnitClassException::INVALID_ABILITIES_DATA,
            ],
            [
                // abilities содержит не массивы
                [
                    'id'         => 1,
                    'name'       => 'Warrior',
                    'small_icon' => '/images/icons/small/warrior.png',
                    'abilities'  => ['invalid_data'],
                ],
                UnitClassException::INVALID_ABILITY_DATA,
            ],
        ];
    }
}
