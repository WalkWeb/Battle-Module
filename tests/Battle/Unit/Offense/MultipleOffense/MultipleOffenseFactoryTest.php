<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Offense\MultipleOffense;

use Battle\BattleException;
use Battle\Unit\Offense\MultipleOffense\MultipleOffenseException;
use Battle\Unit\Offense\MultipleOffense\MultipleOffenseFactory;
use Tests\AbstractUnitTest;

class MultipleOffenseFactoryTest extends AbstractUnitTest
{
    /**
     * Тест на успешное создание MultipleOffense на основе массива параметров
     *
     * @dataProvider successDataProvider
     * @param array $data
     * @throws BattleException
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
     * @throws BattleException
     */
    public function testMultipleOffenseFactoryCreateDefault(): void
    {
        $multipleOffense = $this->getFactory()->create([]);

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
     */
    public function testMultipleOffenseFactoryCreateFail(array $data, string $error): void
    {
        $this->expectException(BattleException::class);
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
