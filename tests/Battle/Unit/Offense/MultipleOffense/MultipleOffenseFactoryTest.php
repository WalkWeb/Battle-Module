<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Offense\MultipleOffense;

use Battle\Unit\Offense\MultipleOffense\MultipleOffenseException;
use Battle\Unit\Offense\MultipleOffense\MultipleOffenseFactory;
use Battle\Unit\Offense\MultipleOffense\MultipleOffenseInterface;
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

        if (array_key_exists('damage_convert_to', $data)) {
            self::assertEquals($data['damage_convert_to'], $multipleOffense->getDamageConvertTo());
        }
    }

    /**
     * Тест на заполнение MultipleOffense значениями по умолчанию (множителем х1)
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
                    'damage_convert_to'   => MultipleOffenseInterface::CONVERT_PHYSICAL,
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
                // damage_convert_to некорректного типа
                [
                    'damage'              => 2.0,
                    'speed'               => 2.1,
                    'accuracy'            => 3.0,
                    'critical_chance'     => 3.2,
                    'critical_multiplier' => 2.0,
                    'damage_convert_to'   => true,
                ],
                MultipleOffenseException::INVALID_CRITICAL_DAMAGE_CONVERT,
            ],
            [
                // damage_convert_to некорректного значения
                [
                    'damage'              => 2.0,
                    'speed'               => 2.1,
                    'accuracy'            => 3.0,
                    'critical_chance'     => 3.2,
                    'critical_multiplier' => 2.0,
                    'damage_convert_to'   => 'to_fire',
                ],
                MultipleOffenseException::INVALID_CRITICAL_DAMAGE_CONVERT_VALUE . ': to_fire',
            ],
        ];
    }
}
