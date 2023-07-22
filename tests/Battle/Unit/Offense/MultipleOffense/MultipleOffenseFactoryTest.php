<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Offense\MultipleOffense;

use Battle\Unit\Offense\MultipleOffense\MultipleOffenseException;
use Battle\Unit\Offense\MultipleOffense\MultipleOffenseFactory;
use Battle\Unit\Offense\MultipleOffense\MultipleOffenseInterface;
use Battle\Unit\Offense\OffenseInterface;
use Exception;
use Tests\AbstractUnitTest;

class MultipleOffenseFactoryTest extends AbstractUnitTest
{
    /**
     * Тест на успешное создание MultipleOffense на основе массива параметров
     *
     * @dataProvider successDataProvider
     * @param array $data
     * @throws Exception
     */
    public function testMultipleOffenseFactoryCreateSuccess(array $data): void
    {
        $multipleOffense = MultipleOffenseFactory::create($data);

        self::assertEquals($data['damage'], $multipleOffense->getDamageMultiplier());
        self::assertEquals($data['speed'], $multipleOffense->getSpeedMultiplier());
        self::assertEquals($data['accuracy'], $multipleOffense->getAccuracyMultiplier());
        self::assertEquals($data['critical_chance'], $multipleOffense->getCriticalChanceMultiplier());
        self::assertEquals($data['critical_multiplier'], $multipleOffense->getCriticalMultiplierMultiplier());

        if (array_key_exists('damage_convert', $data)) {
            self::assertEquals($data['damage_convert'], $multipleOffense->getDamageConvert());
        }

        if (array_key_exists('vampirism', $data)) {
            self::assertEquals($data['vampirism'], $multipleOffense->getVampirism());
        }

        if (array_key_exists('block_ignoring', $data)) {
            self::assertEquals($data['block_ignoring'], $multipleOffense->getBlockIgnoring());
        }
    }

    /**
     * Тест на заполнение MultipleOffense значениями по умолчанию
     *
     * @throws Exception
     */
    public function testMultipleOffenseFactoryCreateDefault(): void
    {
        // Передаются мусорные данные, чтобы массив был чем-то заполнен, иначе будет другое исключение
        $multipleOffense = MultipleOffenseFactory::create([123]);

        self::assertEquals(1.0, $multipleOffense->getDamageMultiplier());
        self::assertEquals(1.0, $multipleOffense->getSpeedMultiplier());
        self::assertEquals(1.0, $multipleOffense->getAccuracyMultiplier());
        self::assertEquals(1.0, $multipleOffense->getCriticalChanceMultiplier());
        self::assertEquals(1.0, $multipleOffense->getCriticalMultiplierMultiplier());

        self::assertEquals(0, $multipleOffense->getVampirism());
        self::assertEquals(0, $multipleOffense->getBlockIgnoring());
    }

    /**
     * Тесты на различные варианты невалидных данных
     *
     * @dataProvider failDataProvider
     * @param array $data
     * @param string $error
     * @throws Exception
     */
    public function testMultipleOffenseFactoryCreateFail(array $data, string $error): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage($error);
        MultipleOffenseFactory::create($data);
    }

    /**
     * @return array
     */
    public function successDataProvider(): array
    {
        return [
            [
                [
                    'damage'              => 2.1,
                    'speed'               => 2.8,
                    'accuracy'            => 3.0,
                    'critical_chance'     => 3.2,
                    'critical_multiplier' => 3.3,
                ],
            ],
            [
                [
                    'damage'              => 2.1,
                    'speed'               => 2.8,
                    'accuracy'            => 3.0,
                    'critical_chance'     => 3.2,
                    'critical_multiplier' => 3.3,
                    'damage_convert'      => MultipleOffenseInterface::CONVERT_PHYSICAL,
                ],
            ],
            [
                [
                    'damage'              => 2.1,
                    'speed'               => 2.8,
                    'accuracy'            => 3.0,
                    'critical_chance'     => 3.2,
                    'critical_multiplier' => 3.3,
                    'vampirism'           => 15,
                ],
            ],
            [
                [
                    'damage'              => 2.1,
                    'speed'               => 2.8,
                    'accuracy'            => 3.0,
                    'critical_chance'     => 3.2,
                    'critical_multiplier' => 3.3,
                    'block_ignoring'      => 100,
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
            [
                // Пустые данные - в этом случае MultipleOffense не нужно указывать и создавать вовсе
                [],
                MultipleOffenseException::EMPTY_DATA,
            ],
            [
                // damage некорректного типа
                [
                    'damage'              => 200,
                    'speed'               => 2.8,
                    'accuracy'            => 3.0,
                    'critical_chance'     => 3.2,
                    'critical_multiplier' => 3.3,
                ],
                MultipleOffenseException::INVALID_DAMAGE,
            ],
            [
                // damage меньше минимального значения
                [
                    'damage'              => MultipleOffenseInterface::MIN_MULTIPLIER - 0.1,
                    'speed'               => 2.8,
                    'accuracy'            => 3.0,
                    'critical_chance'     => 3.2,
                    'critical_multiplier' => 3.3,
                ],
                MultipleOffenseException::INVALID_DAMAGE_VALUE . MultipleOffenseInterface::MIN_MULTIPLIER . '-' . MultipleOffenseInterface::MAX_MULTIPLIER,
            ],
            [
                // damage больше максимального значения
                [
                    'damage'              => MultipleOffenseInterface::MAX_MULTIPLIER + 0.1,
                    'speed'               => 2.8,
                    'accuracy'            => 3.0,
                    'critical_chance'     => 3.2,
                    'critical_multiplier' => 3.3,
                ],
                MultipleOffenseException::INVALID_DAMAGE_VALUE . MultipleOffenseInterface::MIN_MULTIPLIER . '-' . MultipleOffenseInterface::MAX_MULTIPLIER,
            ],
            [
                // attack_speed некорректного типа
                [
                    'damage'              => 2.1,
                    'speed'               => null,
                    'accuracy'            => 3.0,
                    'critical_chance'     => 3.2,
                    'critical_multiplier' => 3.3,
                ],
                MultipleOffenseException::INVALID_SPEED,
            ],
            [
                // attack_speed меньше минимального значения
                [
                    'damage'              => 2.0,
                    'speed'               => MultipleOffenseInterface::MIN_MULTIPLIER - 0.1,
                    'accuracy'            => 3.0,
                    'critical_chance'     => 3.2,
                    'critical_multiplier' => 3.3,
                ],
                MultipleOffenseException::INVALID_SPEED_VALUE . MultipleOffenseInterface::MIN_MULTIPLIER . '-' . MultipleOffenseInterface::MAX_MULTIPLIER,
            ],
            [
                // attack_speed больше максимального значения
                [
                    'damage'              => 2.0,
                    'speed'               => MultipleOffenseInterface::MAX_MULTIPLIER + 0.1,
                    'accuracy'            => 3.0,
                    'critical_chance'     => 3.2,
                    'critical_multiplier' => 3.3,
                ],
                MultipleOffenseException::INVALID_SPEED_VALUE . MultipleOffenseInterface::MIN_MULTIPLIER . '-' . MultipleOffenseInterface::MAX_MULTIPLIER,
            ],
            [
                // accuracy некорректного типа
                [
                    'damage'              => 2.1,
                    'speed'               => 2.8,
                    'accuracy'            => null,
                    'critical_chance'     => 3.2,
                    'critical_multiplier' => 3.3,
                ],
                MultipleOffenseException::INVALID_ACCURACY,
            ],
            [
                // accuracy меньше минимального значения
                [
                    'damage'              => 2.0,
                    'speed'               => 2.1,
                    'accuracy'            => MultipleOffenseInterface::MIN_MULTIPLIER - 0.1,
                    'critical_chance'     => 3.2,
                    'critical_multiplier' => 3.3,
                ],
                MultipleOffenseException::INVALID_ACCURACY_VALUE . MultipleOffenseInterface::MIN_MULTIPLIER . '-' . MultipleOffenseInterface::MAX_MULTIPLIER,
            ],
            [
                // accuracy больше максимального значения
                [
                    'damage'              => 2.0,
                    'speed'               => 2.1,
                    'accuracy'            => MultipleOffenseInterface::MAX_MULTIPLIER + 0.1,
                    'critical_chance'     => 3.2,
                    'critical_multiplier' => 3.3,
                ],
                MultipleOffenseException::INVALID_ACCURACY_VALUE . MultipleOffenseInterface::MIN_MULTIPLIER . '-' . MultipleOffenseInterface::MAX_MULTIPLIER,
            ],
            [
                // critical_chance некорректного типа
                [
                    'damage'              => 2.1,
                    'speed'               => 2.8,
                    'accuracy'            => 3.0,
                    'critical_chance'     => null,
                    'critical_multiplier' => 3.3,
                ],
                MultipleOffenseException::INVALID_CRITICAL_CHANCE,
            ],
            [
                // critical_chance меньше минимального значения
                [
                    'damage'              => 2.0,
                    'speed'               => 2.1,
                    'accuracy'            => 3.0,
                    'critical_chance'     => MultipleOffenseInterface::MIN_MULTIPLIER - 0.1,
                    'critical_multiplier' => 3.3,
                ],
                MultipleOffenseException::INVALID_CRITICAL_CHANCE_VALUE . MultipleOffenseInterface::MIN_MULTIPLIER . '-' . MultipleOffenseInterface::MAX_MULTIPLIER,
            ],
            [
                // critical_chance больше максимального значения
                [
                    'damage'              => 2.0,
                    'speed'               => 2.1,
                    'accuracy'            => 3.0,
                    'critical_chance'     => MultipleOffenseInterface::MAX_MULTIPLIER + 0.1,
                    'critical_multiplier' => 3.3,
                ],
                MultipleOffenseException::INVALID_CRITICAL_CHANCE_VALUE . MultipleOffenseInterface::MIN_MULTIPLIER . '-' . MultipleOffenseInterface::MAX_MULTIPLIER,
            ],
            [
                // critical_multiplier некорректного типа
                [
                    'damage'              => 2.1,
                    'speed'               => 2.8,
                    'accuracy'            => 3.0,
                    'critical_chance'     => 3.2,
                    'critical_multiplier' => null,
                ],
                MultipleOffenseException::INVALID_CRITICAL_MULTIPLIER,
            ],
            [
                // critical_multiplier меньше минимального значения
                [
                    'damage'              => 2.0,
                    'speed'               => 2.1,
                    'accuracy'            => 3.0,
                    'critical_chance'     => 3.2,
                    'critical_multiplier' => MultipleOffenseInterface::MIN_MULTIPLIER - 0.1,
                ],
                MultipleOffenseException::INVALID_CRITICAL_MULTIPLIER_VALUE . MultipleOffenseInterface::MIN_MULTIPLIER . '-' . MultipleOffenseInterface::MAX_MULTIPLIER,
            ],
            [
                // critical_multiplier больше максимального значения
                [
                    'damage'              => 2.0,
                    'speed'               => 2.1,
                    'accuracy'            => 3.0,
                    'critical_chance'     => 3.2,
                    'critical_multiplier' => MultipleOffenseInterface::MAX_MULTIPLIER + 0.1,
                ],
                MultipleOffenseException::INVALID_CRITICAL_MULTIPLIER_VALUE . MultipleOffenseInterface::MIN_MULTIPLIER . '-' . MultipleOffenseInterface::MAX_MULTIPLIER,
            ],

            [
                // damage_convert некорректного типа
                [
                    'damage'              => 2.0,
                    'speed'               => 2.1,
                    'accuracy'            => 3.0,
                    'critical_chance'     => 3.2,
                    'critical_multiplier' => 2.0,
                    'damage_convert'      => true,
                ],
                MultipleOffenseException::INVALID_CRITICAL_DAMAGE_CONVERT,
            ],
            [
                // damage_convert некорректного значения
                [
                    'damage'              => 2.0,
                    'speed'               => 2.1,
                    'accuracy'            => 3.0,
                    'critical_chance'     => 3.2,
                    'critical_multiplier' => 2.0,
                    'damage_convert'      => 'to_fire',
                ],
                MultipleOffenseException::INVALID_CRITICAL_DAMAGE_CONVERT_VALUE . ': to_fire',
            ],

            [
                // vampirism некорректного типа
                [
                    'damage'              => 2.0,
                    'speed'               => 2.1,
                    'accuracy'            => 3.0,
                    'critical_chance'     => 3.2,
                    'critical_multiplier' => 2.0,
                    'vampirism'           => 2.0,
                ],
                MultipleOffenseException::INVALID_VAMPIRISM,
            ],
            [
                // vampirism меньше минимального значения
                [
                    'damage'              => 2.0,
                    'speed'               => 2.1,
                    'accuracy'            => 3.0,
                    'critical_chance'     => 3.2,
                    'critical_multiplier' => 2.0,
                    'vampirism'           => OffenseInterface::MIN_VAMPIRISM - 1,
                ],
                MultipleOffenseException::INVALID_VAMPIRISM_VALUE . OffenseInterface::MIN_VAMPIRISM . '-' . OffenseInterface::MAX_VAMPIRISM,
            ],
            [
                // vampirism больше максимального значения
                [
                    'damage'              => 2.0,
                    'speed'               => 2.1,
                    'accuracy'            => 3.0,
                    'critical_chance'     => 3.2,
                    'critical_multiplier' => 2.0,
                    'vampirism'           => OffenseInterface::MAX_VAMPIRISM + 1,
                ],
                MultipleOffenseException::INVALID_VAMPIRISM_VALUE . OffenseInterface::MIN_VAMPIRISM . '-' . OffenseInterface::MAX_VAMPIRISM,
            ],

            [
                // block_ignoring некорректного типа
                [
                    'damage'              => 2.0,
                    'speed'               => 2.1,
                    'accuracy'            => 3.0,
                    'critical_chance'     => 3.2,
                    'critical_multiplier' => 2.0,
                    'block_ignoring'      => null,
                ],
                MultipleOffenseException::INVALID_BLOCK_IGNORING,
            ],
            [
                // block_ignoring меньше минимального значения
                [
                    'damage'              => 2.0,
                    'speed'               => 2.1,
                    'accuracy'            => 3.0,
                    'critical_chance'     => 3.2,
                    'critical_multiplier' => 2.0,
                    'block_ignoring'      => OffenseInterface::MIN_BLOCK_IGNORING - 1,
                ],
                MultipleOffenseException::INVALID_BLOCK_IGNORING_VALUE . OffenseInterface::MIN_BLOCK_IGNORING . '-' . OffenseInterface::MAX_BLOCK_IGNORING,
            ],
            [
                // block_ignoring больше максимального значения
                [
                    'damage'              => 2.0,
                    'speed'               => 2.1,
                    'accuracy'            => 3.0,
                    'critical_chance'     => 3.2,
                    'critical_multiplier' => 2.0,
                    'block_ignoring'      => OffenseInterface::MAX_BLOCK_IGNORING + 1,
                ],
                MultipleOffenseException::INVALID_BLOCK_IGNORING_VALUE . OffenseInterface::MIN_BLOCK_IGNORING . '-' . OffenseInterface::MAX_BLOCK_IGNORING,
            ],
        ];
    }
}
