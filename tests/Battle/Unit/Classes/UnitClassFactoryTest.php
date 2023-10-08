<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Classes;

use Battle\Unit\Classes\UnitClassException;
use Exception;
use Tests\AbstractUnitTest;

class UnitClassFactoryTest extends AbstractUnitTest
{
    /**
     * Тест на создание класса по ID класса
     *
     * @dataProvider createByIdSuccessDataProvider
     * @param int $classId
     * @throws Exception
     */
    public function testUnitClassFactoryCreateByIdSuccess(int $classId): void
    {
        $class = $this->container->getUnitClassFactory()->create(
            $this->container->getClassDataProvider()->get($classId)
        );
        self::assertEquals($classId, $class->getId());
    }

    /**
     * @throws Exception
     */
    public function testUnitClassFactoryCreateByIdFail(): void
    {
        $classId = 55;
        $this->expectException(UnitClassException::class);
        $this->expectExceptionMessage(UnitClassException::UNDEFINED_CLASS_ID . ': ' . $classId);
        $this->container->getUnitClassFactory()->create(
            $this->container->getClassDataProvider()->get($classId)
        );
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
        $class = ($this->container)->getUnitClassFactory()->create($data);

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
     * @throws Exception
     */
    public function testUnitClassFactoryCreateByArrayFail(array $data, string $error): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage($error);
        $this->container->getUnitClassFactory()->create($data);
    }

    /**
     * @return array
     */
    public function createByIdSuccessDataProvider(): array
    {
        return [
            [
                1
            ],
            [
                2
            ],
            [
                3
            ],
            [
                4
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
