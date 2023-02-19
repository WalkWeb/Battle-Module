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
        $multipleOffense = $this->getFactory()->create($data);

        self::assertEquals($data['damage'], $multipleOffense->getDamageMultiplier());
        self::assertEquals($data['attack_speed'], $multipleOffense->getAttackSpeedMultiplier());
        self::assertEquals($data['cast_speed'], $multipleOffense->getCastSpeedMultiplier());
        self::assertEquals($data['accuracy'], $multipleOffense->getAccuracyMultiplier());
        self::assertEquals($data['magic_accuracy'], $multipleOffense->getMagicAccuracyMultiplier());
        self::assertEquals($data['critical_chance'], $multipleOffense->getCriticalChanceMultiplier());
        self::assertEquals($data['critical_multiplier'], $multipleOffense->getCriticalMultiplierMultiplier());
    }

    /**
     * Тест на заполнение MultipleOffense значениями по умолчанию (множителем х1)
     *
     * @throws Exception
     */
    public function testMultipleOffenseFactoryCreateDefault(): void
    {
        // Передаются мусорные данные, чтобы массив был чем-то заполнен, иначе будет другое исключение
        $multipleOffense = $this->getFactory()->create([123]);

        self::assertEquals(1.0, $multipleOffense->getDamageMultiplier());
        self::assertEquals(1.0, $multipleOffense->getAttackSpeedMultiplier());
        self::assertEquals(1.0, $multipleOffense->getCastSpeedMultiplier());
        self::assertEquals(1.0, $multipleOffense->getAccuracyMultiplier());
        self::assertEquals(1.0, $multipleOffense->getMagicAccuracyMultiplier());
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
                    'damage'              => 2.1,
                    'attack_speed'        => 2.8,
                    'cast_speed'          => 2.9,
                    'accuracy'            => 3.0,
                    'magic_accuracy'      => 3.1,
                    'critical_chance'     => 3.2,
                    'critical_multiplier' => 3.3,
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
                    'attack_speed'        => 2.8,
                    'cast_speed'          => 2.9,
                    'accuracy'            => 3.0,
                    'magic_accuracy'      => 3.1,
                    'critical_chance'     => 3.2,
                    'critical_multiplier' => 3.3,
                ],
                MultipleOffenseException::INVALID_DAMAGE,
            ],
            [
                // damage меньше минимального значения
                [
                    'damage'              => MultipleOffenseInterface::MIN_MULTIPLIER - 0.1,
                    'attack_speed'        => 2.8,
                    'cast_speed'          => 2.9,
                    'accuracy'            => 3.0,
                    'magic_accuracy'      => 3.1,
                    'critical_chance'     => 3.2,
                    'critical_multiplier' => 3.3,
                ],
                MultipleOffenseException::INVALID_DAMAGE_VALUE . MultipleOffenseInterface::MIN_MULTIPLIER . '-' . MultipleOffenseInterface::MAX_MULTIPLIER,
            ],
            [
                // damage больше максимального значения
                [
                    'damage'              => MultipleOffenseInterface::MAX_MULTIPLIER + 0.1,
                    'attack_speed'        => 2.8,
                    'cast_speed'          => 2.9,
                    'accuracy'            => 3.0,
                    'magic_accuracy'      => 3.1,
                    'critical_chance'     => 3.2,
                    'critical_multiplier' => 3.3,
                ],
                MultipleOffenseException::INVALID_DAMAGE_VALUE . MultipleOffenseInterface::MIN_MULTIPLIER . '-' . MultipleOffenseInterface::MAX_MULTIPLIER,
            ],
            [
                // attack_speed некорректного типа
                [
                    'damage'              => 2.1,
                    'attack_speed'        => null,
                    'cast_speed'          => 2.9,
                    'accuracy'            => 3.0,
                    'magic_accuracy'      => 3.1,
                    'critical_chance'     => 3.2,
                    'critical_multiplier' => 3.3,
                ],
                MultipleOffenseException::INVALID_ATTACK_SPEED,
            ],
            [
                // attack_speed меньше минимального значения
                [
                    'damage'              => 2.0,
                    'attack_speed'        => MultipleOffenseInterface::MIN_MULTIPLIER - 0.1,
                    'cast_speed'          => 2.9,
                    'accuracy'            => 3.0,
                    'magic_accuracy'      => 3.1,
                    'critical_chance'     => 3.2,
                    'critical_multiplier' => 3.3,
                ],
                MultipleOffenseException::INVALID_ATTACK_SPEED_VALUE . MultipleOffenseInterface::MIN_MULTIPLIER . '-' . MultipleOffenseInterface::MAX_MULTIPLIER,
            ],
            [
                // attack_speed больше максимального значения
                [
                    'damage'              => 2.0,
                    'attack_speed'        => MultipleOffenseInterface::MAX_MULTIPLIER + 0.1,
                    'cast_speed'          => 2.9,
                    'accuracy'            => 3.0,
                    'magic_accuracy'      => 3.1,
                    'critical_chance'     => 3.2,
                    'critical_multiplier' => 3.3,
                ],
                MultipleOffenseException::INVALID_ATTACK_SPEED_VALUE . MultipleOffenseInterface::MIN_MULTIPLIER . '-' . MultipleOffenseInterface::MAX_MULTIPLIER,
            ],
            [
                // cast_speed некорректного типа
                [
                    'damage'              => 2.1,
                    'attack_speed'        => 2.8,
                    'cast_speed'          => null,
                    'accuracy'            => 3.0,
                    'magic_accuracy'      => 3.1,
                    'critical_chance'     => 3.2,
                    'critical_multiplier' => 3.3,
                ],
                MultipleOffenseException::INVALID_CAST_SPEED,
            ],
            [
                // cast_speed меньше минимального значения
                [
                    'damage'              => 2.0,
                    'attack_speed'        => 2.1,
                    'cast_speed'          => MultipleOffenseInterface::MIN_MULTIPLIER - 0.1,
                    'accuracy'            => 3.0,
                    'magic_accuracy'      => 3.1,
                    'critical_chance'     => 3.2,
                    'critical_multiplier' => 3.3,
                ],
                MultipleOffenseException::INVALID_CAST_SPEED_VALUE . MultipleOffenseInterface::MIN_MULTIPLIER . '-' . MultipleOffenseInterface::MAX_MULTIPLIER,
            ],
            [
                // cast_speed больше максимального значения
                [
                    'damage'              => 2.0,
                    'attack_speed'        => 2.1,
                    'cast_speed'          => MultipleOffenseInterface::MAX_MULTIPLIER + 0.1,
                    'accuracy'            => 3.0,
                    'magic_accuracy'      => 3.1,
                    'critical_chance'     => 3.2,
                    'critical_multiplier' => 3.3,
                ],
                MultipleOffenseException::INVALID_CAST_SPEED_VALUE . MultipleOffenseInterface::MIN_MULTIPLIER . '-' . MultipleOffenseInterface::MAX_MULTIPLIER,
            ],
            [
                // accuracy некорректного типа
                [
                    'damage'              => 2.1,
                    'attack_speed'        => 2.8,
                    'cast_speed'          => 2.9,
                    'accuracy'            => null,
                    'magic_accuracy'      => 3.1,
                    'critical_chance'     => 3.2,
                    'critical_multiplier' => 3.3,
                ],
                MultipleOffenseException::INVALID_ACCURACY,
            ],
            [
                // accuracy меньше минимального значения
                [
                    'damage'              => 2.0,
                    'attack_speed'        => 2.1,
                    'cast_speed'          => 2.9,
                    'accuracy'            => MultipleOffenseInterface::MIN_MULTIPLIER - 0.1,
                    'magic_accuracy'      => 3.1,
                    'critical_chance'     => 3.2,
                    'critical_multiplier' => 3.3,
                ],
                MultipleOffenseException::INVALID_ACCURACY_VALUE . MultipleOffenseInterface::MIN_MULTIPLIER . '-' . MultipleOffenseInterface::MAX_MULTIPLIER,
            ],
            [
                // accuracy больше максимального значения
                [
                    'damage'              => 2.0,
                    'attack_speed'        => 2.1,
                    'cast_speed'          => 2.9,
                    'accuracy'            => MultipleOffenseInterface::MAX_MULTIPLIER + 0.1,
                    'magic_accuracy'      => 3.1,
                    'critical_chance'     => 3.2,
                    'critical_multiplier' => 3.3,
                ],
                MultipleOffenseException::INVALID_ACCURACY_VALUE . MultipleOffenseInterface::MIN_MULTIPLIER . '-' . MultipleOffenseInterface::MAX_MULTIPLIER,
            ],
            [
                // magic_accuracy некорректного типа
                [
                    'damage'              => 2.1,
                    'attack_speed'        => 2.8,
                    'cast_speed'          => 2.9,
                    'accuracy'            => 3.0,
                    'magic_accuracy'      => null,
                    'critical_chance'     => 3.2,
                    'critical_multiplier' => 3.3,
                ],
                MultipleOffenseException::INVALID_MAGIC_ACCURACY,
            ],
            [
                // magic_accuracy меньше минимального значения
                [
                    'damage'              => 2.0,
                    'attack_speed'        => 2.1,
                    'cast_speed'          => 2.9,
                    'accuracy'            => 3.0,
                    'magic_accuracy'      => MultipleOffenseInterface::MIN_MULTIPLIER - 0.1,
                    'critical_chance'     => 3.2,
                    'critical_multiplier' => 3.3,
                ],
                MultipleOffenseException::INVALID_MAGIC_ACCURACY_VALUE . MultipleOffenseInterface::MIN_MULTIPLIER . '-' . MultipleOffenseInterface::MAX_MULTIPLIER,
            ],
            [
                // magic_accuracy больше максимального значения
                [
                    'damage'              => 2.0,
                    'attack_speed'        => 2.1,
                    'cast_speed'          => 2.9,
                    'accuracy'            => 3.0,
                    'magic_accuracy'      => MultipleOffenseInterface::MAX_MULTIPLIER + 0.1,
                    'critical_chance'     => 3.2,
                    'critical_multiplier' => 3.3,
                ],
                MultipleOffenseException::INVALID_MAGIC_ACCURACY_VALUE . MultipleOffenseInterface::MIN_MULTIPLIER . '-' . MultipleOffenseInterface::MAX_MULTIPLIER,
            ],
            [
                // critical_chance некорректного типа
                [
                    'damage'              => 2.1,
                    'attack_speed'        => 2.8,
                    'cast_speed'          => 2.9,
                    'accuracy'            => 3.0,
                    'magic_accuracy'      => 3.1,
                    'critical_chance'     => null,
                    'critical_multiplier' => 3.3,
                ],
                MultipleOffenseException::INVALID_CRITICAL_CHANCE,
            ],
            [
                // critical_chance меньше минимального значения
                [
                    'damage'              => 2.0,
                    'attack_speed'        => 2.1,
                    'cast_speed'          => 2.9,
                    'accuracy'            => 3.0,
                    'magic_accuracy'      => 3.1,
                    'critical_chance'     => MultipleOffenseInterface::MIN_MULTIPLIER - 0.1,
                    'critical_multiplier' => 3.3,
                ],
                MultipleOffenseException::INVALID_CRITICAL_CHANCE_VALUE . MultipleOffenseInterface::MIN_MULTIPLIER . '-' . MultipleOffenseInterface::MAX_MULTIPLIER,
            ],
            [
                // critical_chance больше максимального значения
                [
                    'damage'              => 2.0,
                    'attack_speed'        => 2.1,
                    'cast_speed'          => 2.9,
                    'accuracy'            => 3.0,
                    'magic_accuracy'      => 3.1,
                    'critical_chance'     => MultipleOffenseInterface::MAX_MULTIPLIER + 0.1,
                    'critical_multiplier' => 3.3,
                ],
                MultipleOffenseException::INVALID_CRITICAL_CHANCE_VALUE . MultipleOffenseInterface::MIN_MULTIPLIER . '-' . MultipleOffenseInterface::MAX_MULTIPLIER,
            ],
            [
                // critical_multiplier некорректного типа
                [
                    'damage'              => 2.1,
                    'attack_speed'        => 2.8,
                    'cast_speed'          => 2.9,
                    'accuracy'            => 3.0,
                    'magic_accuracy'      => 3.1,
                    'critical_chance'     => 3.2,
                    'critical_multiplier' => null,
                ],
                MultipleOffenseException::INVALID_CRITICAL_MULTIPLIER,
            ],
            [
                // critical_multiplier меньше минимального значения
                [
                    'damage'              => 2.0,
                    'attack_speed'        => 2.1,
                    'cast_speed'          => 2.9,
                    'accuracy'            => 3.0,
                    'magic_accuracy'      => 3.1,
                    'critical_chance'     => 3.2,
                    'critical_multiplier' => MultipleOffenseInterface::MIN_MULTIPLIER - 0.1,
                ],
                MultipleOffenseException::INVALID_CRITICAL_MULTIPLIER_VALUE . MultipleOffenseInterface::MIN_MULTIPLIER . '-' . MultipleOffenseInterface::MAX_MULTIPLIER,
            ],
            [
                // critical_multiplier больше максимального значения
                [
                    'damage'              => 2.0,
                    'attack_speed'        => 2.1,
                    'cast_speed'          => 2.9,
                    'accuracy'            => 3.0,
                    'magic_accuracy'      => 3.1,
                    'critical_chance'     => 3.2,
                    'critical_multiplier' => MultipleOffenseInterface::MAX_MULTIPLIER + 0.1,
                ],
                MultipleOffenseException::INVALID_CRITICAL_MULTIPLIER_VALUE . MultipleOffenseInterface::MIN_MULTIPLIER . '-' . MultipleOffenseInterface::MAX_MULTIPLIER,
            ],
        ];
    }

    /**
     * @return MultipleOffenseFactory
     */
    private function getFactory(): MultipleOffenseFactory
    {
        return new MultipleOffenseFactory();
    }
}
