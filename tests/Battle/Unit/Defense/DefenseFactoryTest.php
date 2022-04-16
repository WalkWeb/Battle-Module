<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Defense;

use Battle\Unit\Defense\DefenseException;
use Battle\Unit\Defense\DefenseFactory;
use Battle\Unit\Defense\DefenseInterface;
use Exception;
use Tests\AbstractUnitTest;

class DefenseFactoryTest extends AbstractUnitTest
{
    /**
     * Тест на успешное создание Defense на основе массива с данными
     *
     * @dataProvider successDataProvider
     * @param array $data
     * @throws Exception
     */
    public function testDefenseFactoryCreateSuccess(array $data): void
    {
        $defense = $this->getFactory()->create($data);

        self::assertEquals($data['defense'], $defense->getDefense());
        self::assertEquals($data['block'], $defense->getBlock());
    }

    /**
     * Тест на некорректные данные для создания Defense
     *
     * @dataProvider failDataProvider
     * @param array $data
     * @param string $error
     */
    public function testDefenseFactoryCreateFail(array $data, string $error): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage($error);
        $this->getFactory()->create($data);
    }

    /**
     * @return array
     */
    public function successDataProvider(): array
    {
        return [
            [
                [
                    'defense' => 100,
                    'block'   => 0,
                ],
            ],
            [
                [
                    'defense' => 654,
                    'block'   => 34,
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function failDataProvider(): array
    {
        return [
            // defense
            [
                // Отсутствует defense
                [
                    'block'   => 0,
                ],
                DefenseException::INCORRECT_DEFENSE,
            ],
            [
                // defense некорректного типа
                [
                    'defense' => '100',
                    'block'   => 0,
                ],
                DefenseException::INCORRECT_DEFENSE,
            ],
            [
                // defense меньше минимального значения
                [
                    'defense' => DefenseInterface::MIN_DEFENSE - 1,
                    'block'   => 0,
                ],
                DefenseException::INCORRECT_DEFENSE_VALUE . DefenseInterface::MIN_DEFENSE . '-' . DefenseInterface::MAX_DEFENSE,
            ],
            [
                // defense больше максимального значения
                [
                    'defense' => DefenseInterface::MAX_DEFENSE + 1,
                    'block'   => 0,
                ],
                DefenseException::INCORRECT_DEFENSE_VALUE . DefenseInterface::MIN_DEFENSE . '-' . DefenseInterface::MAX_DEFENSE,
            ],

            // block
            [
                // Отсутствует block
                [
                    'defense' => 100,
                ],
                DefenseException::INCORRECT_BLOCK,
            ],
            [
                // block некорректного типа
                [
                    'defense' => 100,
                    'block'   => 50.5,
                ],
                DefenseException::INCORRECT_BLOCK,
            ],
            [
                // block меньше минимального значения
                [
                    'defense' => 100,
                    'block'   => DefenseInterface::MIN_BLOCK - 1,
                ],
                DefenseException::INCORRECT_BLOCK_VALUE . DefenseInterface::MIN_BLOCK . '-' . DefenseInterface::MAX_BLOCK,
            ],
            [
                // block больше максимального значения
                [
                    'defense' => 100,
                    'block'   => DefenseInterface::MAX_BLOCK + 1,
                ],
                DefenseException::INCORRECT_BLOCK_VALUE . DefenseInterface::MIN_BLOCK . '-' . DefenseInterface::MAX_BLOCK,
            ],
        ];
    }

    /**
     * @return DefenseFactory
     */
    private function getFactory(): DefenseFactory
    {
        return new DefenseFactory();
    }
}
