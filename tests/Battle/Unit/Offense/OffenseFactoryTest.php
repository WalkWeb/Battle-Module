<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Offense;

use Battle\Unit\Offense\OffenseException;
use Battle\Unit\Offense\OffenseFactory;
use Battle\Unit\Offense\OffenseInterface;
use Exception;
use Tests\AbstractUnitTest;

class OffenseFactoryTest extends AbstractUnitTest
{
    /**
     * Тест на успешное создание Offense
     *
     * @dataProvider successDataProvider
     * @param array $data
     * @throws Exception
     */
    public function testOffenseFactoryCreateSuccess(array $data): void
    {
        $offense = $this->getFactory()->create($data);

        self::assertEquals($data['damage'], $offense->getDamage());
        self::assertEquals($data['attack_speed'], $offense->getAttackSpeed());
        self::assertEquals($data['accuracy'], $offense->getAccuracy());
        self::assertEquals($data['block_ignore'], $offense->getBlockIgnore());
    }

    /**
     * Тест на некорректные данные для создания Offense
     *
     * @dataProvider failDataProvider
     * @param array $data
     * @param string $error
     * @throws Exception
     */
    public function testOffenseFactoryCreateFail(array $data, string $error): void
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
                // Скорость атаки int
                [
                    'damage'       => 20,
                    'attack_speed' => 1,
                    'accuracy'     => 200,
                    'block_ignore' => 0,
                ],
            ],
            [
                // Скорость атаки float
                [
                    'damage'       => 20,
                    'attack_speed' => 1.3,
                    'accuracy'     => 200,
                    'block_ignore' => 0,
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
            // damage
            [
                // Отсутствует damage
                [
                    'attack_speed' => 1.3,
                    'accuracy'     => 200,
                    'block_ignore' => 0,
                ],
                OffenseException::INCORRECT_DAMAGE,
            ],
            [
                // damage некорректного типа
                [
                    'damage'       => '20',
                    'attack_speed' => 1.3,
                    'accuracy'     => 200,
                    'block_ignore' => 0,
                ],
                OffenseException::INCORRECT_DAMAGE,
            ],
            [
                // damage меньше минимального значения
                [
                    'damage'       => OffenseInterface::MIN_DAMAGE - 1,
                    'attack_speed' => 1.3,
                    'accuracy'     => 200,
                    'block_ignore' => 0,
                ],
                OffenseException::INCORRECT_DAMAGE_VALUE . OffenseInterface::MIN_DAMAGE . '-' . OffenseInterface::MAX_DAMAGE,
            ],
            [
                // damage больше максимального значения
                [
                    'damage'       => OffenseInterface::MAX_DAMAGE + 1,
                    'attack_speed' => 1.3,
                    'accuracy'     => 200,
                    'block_ignore' => 0,
                ],
                OffenseException::INCORRECT_DAMAGE_VALUE . OffenseInterface::MIN_DAMAGE . '-' . OffenseInterface::MAX_DAMAGE,
            ],

            // attack_speed
            [
                // Отсутствует attack_speed
                [
                    'damage'       => 20,
                    'accuracy'     => 200,
                    'block_ignore' => 0,
                ],
                OffenseException::INCORRECT_ATTACK_SPEED,
            ],
            [
                // attack_speed некорректного типа
                [
                    'damage'       => 20,
                    'attack_speed' => true,
                    'accuracy'     => 200,
                    'block_ignore' => 0,
                ],
                OffenseException::INCORRECT_ATTACK_SPEED,
            ],
            [
                // attack_speed меньше минимального значения
                [
                    'damage'       => 20,
                    'attack_speed' => OffenseInterface::MIN_ATTACK_SPEED - 0.1,
                    'accuracy'     => 200,
                    'block_ignore' => 0,
                ],
                OffenseException::INCORRECT_ATTACK_SPEED_VALUE . OffenseInterface::MIN_ATTACK_SPEED . '-' . OffenseInterface::MAX_ATTACK_SPEED,
            ],
            [
                // attack_speed больше максимального значения
                [
                    'damage'       => 20,
                    'attack_speed' => OffenseInterface::MAX_ATTACK_SPEED + 0.1,
                    'accuracy'     => 200,
                    'block_ignore' => 0,
                ],
                OffenseException::INCORRECT_ATTACK_SPEED_VALUE . OffenseInterface::MIN_ATTACK_SPEED . '-' . OffenseInterface::MAX_ATTACK_SPEED,
            ],

            // accuracy
            [
                // Отсутствует accuracy
                [
                    'damage'       => 20,
                    'attack_speed' => 1.3,
                    'block_ignore' => 0,
                ],
                OffenseException::INCORRECT_ACCURACY,
            ],
            [
                // accuracy некорректного типа
                [
                    'damage'       => 20,
                    'attack_speed' => 1.3,
                    'accuracy'     => [200],
                    'block_ignore' => 0,
                ],
                OffenseException::INCORRECT_ACCURACY,
            ],
            [
                // accuracy меньше минимального значения
                [
                    'damage'       => 20,
                    'attack_speed' => 1.3,
                    'accuracy'     => OffenseInterface::MIN_ACCURACY - 1,
                    'block_ignore' => 0,
                ],
                OffenseException::INCORRECT_ACCURACY_VALUE . OffenseInterface::MIN_ACCURACY,
            ],

            // block_ignore
            [
                // Отсутствует block_ignore
                [
                    'damage'       => 20,
                    'attack_speed' => 1.3,
                    'accuracy'     => 200,
                ],
                OffenseException::INCORRECT_BLOCK_IGNORE,
            ],
            [
                // block_ignore некорректного типа
                [
                    'damage'       => 20,
                    'attack_speed' => 1.3,
                    'accuracy'     => 200,
                    'block_ignore' => 0.0,
                ],
                OffenseException::INCORRECT_BLOCK_IGNORE,
            ],
            [
                // block_ignore меньше минимального значения
                [
                    'damage'       => 20,
                    'attack_speed' => 1.3,
                    'accuracy'     => 200,
                    'block_ignore' => OffenseInterface::MIN_BLOCK_IGNORE - 1,
                ],
                OffenseException::INCORRECT_BLOCK_IGNORE_VALUE . OffenseInterface::MIN_BLOCK_IGNORE . '-' . OffenseInterface::MAX_BLOCK_IGNORE,
            ],
            [
                // block_ignore больше максимального значения
                [
                    'damage'       => 20,
                    'attack_speed' => 1.3,
                    'accuracy'     => 200,
                    'block_ignore' => OffenseInterface::MAX_BLOCK_IGNORE + 1,
                ],
                OffenseException::INCORRECT_BLOCK_IGNORE_VALUE . OffenseInterface::MIN_BLOCK_IGNORE . '-' . OffenseInterface::MAX_BLOCK_IGNORE,
            ],
        ];
    }

    /**
     * @return OffenseFactory
     */
    private function getFactory(): OffenseFactory
    {
        return new OffenseFactory();
    }
}
