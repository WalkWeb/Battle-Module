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

        self::assertEquals($data['physical_resist'], $defense->getPhysicalResist());
        self::assertEquals($data['fire_resist'], $defense->getFireResist());
        self::assertEquals($data['water_resist'], $defense->getWaterResist());
        self::assertEquals($data['air_resist'], $defense->getAirResist());
        self::assertEquals($data['earth_resist'], $defense->getEarthResist());
        self::assertEquals($data['life_resist'], $defense->getLifeResist());
        self::assertEquals($data['death_resist'], $defense->getDeathResist());
        self::assertEquals($data['defense'], $defense->getDefense());
        self::assertEquals($data['magic_defense'], $defense->getMagicDefense());
        self::assertEquals($data['block'], $defense->getBlock());
        self::assertEquals($data['magic_block'], $defense->getMagicBlock());
        self::assertEquals($data['mental_barrier'], $defense->getMentalBarrier());
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
                    'physical_resist' => -100,
                    'fire_resist'     => -90,
                    'water_resist'    => -80,
                    'air_resist'      => -70,
                    'earth_resist'    => -60,
                    'life_resist'     => -50,
                    'death_resist'    => -40,
                    'defense'         => 100,
                    'magic_defense'   => 50,
                    'block'           => 0,
                    'magic_block'     => 0,
                    'mental_barrier'  => 0,
                ],
            ],
            [
                [
                    'physical_resist' => 0,
                    'fire_resist'     => 5,
                    'water_resist'    => 10,
                    'air_resist'      => 15,
                    'earth_resist'    => 20,
                    'life_resist'     => 25,
                    'death_resist'    => 30,
                    'defense'         => 654,
                    'magic_defense'   => 150,
                    'block'           => 34,
                    'magic_block'     => 24,
                    'mental_barrier'  => 100,
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

            // physical_resist
            [
                // Отсутствует physical_resist
                [
                    'fire_resist'    => 5,
                    'water_resist'   => 10,
                    'air_resist'     => 15,
                    'earth_resist'   => 20,
                    'life_resist'    => 25,
                    'death_resist'   => 30,
                    'defense'        => 654,
                    'magic_defense'  => 150,
                    'block'          => 34,
                    'magic_block'    => 25,
                    'mental_barrier' => 100,
                ],
                DefenseException::INCORRECT_PHYSICAL_RESIST,
            ],
            [
                // physical_resist некорректного типа
                [
                    'physical_resist' => [30],
                    'fire_resist'     => 5,
                    'water_resist'    => 10,
                    'air_resist'      => 15,
                    'earth_resist'    => 20,
                    'life_resist'     => 25,
                    'death_resist'    => 30,
                    'defense'         => 654,
                    'magic_defense'   => 150,
                    'block'           => 34,
                    'magic_block'     => 25,
                    'mental_barrier'  => 100,
                ],
                DefenseException::INCORRECT_PHYSICAL_RESIST,
            ],
            [
                // physical_resist меньше минимального значения
                [
                    'physical_resist' => DefenseInterface::MIN_RESISTANCE - 1,
                    'fire_resist'     => 5,
                    'water_resist'    => 10,
                    'air_resist'      => 15,
                    'earth_resist'    => 20,
                    'life_resist'     => 25,
                    'death_resist'    => 30,
                    'defense'         => 654,
                    'magic_defense'   => 150,
                    'block'           => 34,
                    'magic_block'     => 25,
                    'mental_barrier'  => 100,
                ],
                DefenseException::INCORRECT_PHYSICAL_RESIST_VALUE . DefenseInterface::MIN_RESISTANCE . '-' . DefenseInterface::MAX_RESISTANCE,
            ],
            [
                // physical_resist больше максимального значения
                [
                    'physical_resist' => DefenseInterface::MAX_RESISTANCE + 1,
                    'fire_resist'     => 5,
                    'water_resist'    => 10,
                    'air_resist'      => 15,
                    'earth_resist'    => 20,
                    'life_resist'     => 25,
                    'death_resist'    => 30,
                    'defense'         => 654,
                    'magic_defense'   => 150,
                    'block'           => 34,
                    'magic_block'     => 25,
                    'mental_barrier'  => 100,
                ],
                DefenseException::INCORRECT_PHYSICAL_RESIST_VALUE . DefenseInterface::MIN_RESISTANCE . '-' . DefenseInterface::MAX_RESISTANCE,
            ],

            // fire_resist
            [
                // Отсутствует fire_resist
                [
                    'physical_resist' => 0,
                    'water_resist'    => 10,
                    'air_resist'      => 15,
                    'earth_resist'    => 20,
                    'life_resist'     => 25,
                    'death_resist'    => 30,
                    'defense'         => 654,
                    'magic_defense'   => 150,
                    'block'           => 34,
                    'magic_block'     => 25,
                    'mental_barrier'  => 100,
                ],
                DefenseException::INCORRECT_FIRE_RESIST,
            ],
            [
                // fire_resist некорректного типа
                [
                    'physical_resist' => 0,
                    'fire_resist'     => '5',
                    'water_resist'    => 10,
                    'air_resist'      => 15,
                    'earth_resist'    => 20,
                    'life_resist'     => 25,
                    'death_resist'    => 30,
                    'defense'         => 654,
                    'magic_defense'   => 150,
                    'block'           => 34,
                    'magic_block'     => 25,
                    'mental_barrier'  => 100,
                ],
                DefenseException::INCORRECT_FIRE_RESIST,
            ],
            [
                // fire_resist меньше минимального значения
                [
                    'physical_resist' => 0,
                    'fire_resist'     => DefenseInterface::MIN_RESISTANCE - 1,
                    'water_resist'    => 10,
                    'air_resist'      => 15,
                    'earth_resist'    => 20,
                    'life_resist'     => 25,
                    'death_resist'    => 30,
                    'defense'         => 654,
                    'magic_defense'   => 150,
                    'block'           => 34,
                    'magic_block'     => 25,
                    'mental_barrier'  => 100,
                ],
                DefenseException::INCORRECT_FIRE_RESIST_VALUE . DefenseInterface::MIN_RESISTANCE . '-' . DefenseInterface::MAX_RESISTANCE,
            ],
            [
                // fire_resist больше максимального значения
                [
                    'physical_resist' => 0,
                    'fire_resist'     => DefenseInterface::MAX_RESISTANCE + 1,
                    'water_resist'    => 10,
                    'air_resist'      => 15,
                    'earth_resist'    => 20,
                    'life_resist'     => 25,
                    'death_resist'    => 30,
                    'defense'         => 654,
                    'magic_defense'   => 150,
                    'block'           => 34,
                    'magic_block'     => 25,
                    'mental_barrier'  => 100,
                ],
                DefenseException::INCORRECT_FIRE_RESIST_VALUE . DefenseInterface::MIN_RESISTANCE . '-' . DefenseInterface::MAX_RESISTANCE,
            ],

            // water_resist
            [
                // Отсутствует water_resist
                [
                    'physical_resist' => 0,
                    'fire_resist'     => 5,
                    'air_resist'      => 15,
                    'earth_resist'    => 20,
                    'life_resist'     => 25,
                    'death_resist'    => 30,
                    'defense'         => 654,
                    'magic_defense'   => 150,
                    'block'           => 34,
                    'magic_block'     => 25,
                    'mental_barrier'  => 100,
                ],
                DefenseException::INCORRECT_WATER_RESIST,
            ],
            [
                // water_resist некорректного типа
                [
                    'physical_resist' => 0,
                    'fire_resist'     => 5,
                    'water_resist'    => null,
                    'air_resist'      => 15,
                    'earth_resist'    => 20,
                    'life_resist'     => 25,
                    'death_resist'    => 30,
                    'defense'         => 654,
                    'magic_defense'   => 150,
                    'block'           => 34,
                    'magic_block'     => 25,
                    'mental_barrier'  => 100,
                ],
                DefenseException::INCORRECT_WATER_RESIST,
            ],
            [
                // water_resist меньше минимального значения
                [
                    'physical_resist' => 0,
                    'fire_resist'     => 5,
                    'water_resist'    => DefenseInterface::MIN_RESISTANCE - 1,
                    'air_resist'      => 15,
                    'earth_resist'    => 20,
                    'life_resist'     => 25,
                    'death_resist'    => 30,
                    'defense'         => 654,
                    'magic_defense'   => 150,
                    'block'           => 34,
                    'magic_block'     => 25,
                    'mental_barrier'  => 100,
                ],
                DefenseException::INCORRECT_WATER_RESIST_VALUE . DefenseInterface::MIN_RESISTANCE . '-' . DefenseInterface::MAX_RESISTANCE,
            ],
            [
                // water_resist больше максимального значения
                [
                    'physical_resist' => 0,
                    'fire_resist'     => 5,
                    'water_resist'    => DefenseInterface::MAX_RESISTANCE + 1,
                    'air_resist'      => 15,
                    'earth_resist'    => 20,
                    'life_resist'     => 25,
                    'death_resist'    => 30,
                    'defense'         => 654,
                    'magic_defense'   => 150,
                    'block'           => 34,
                    'magic_block'     => 25,
                    'mental_barrier'  => 100,
                ],
                DefenseException::INCORRECT_WATER_RESIST_VALUE . DefenseInterface::MIN_RESISTANCE . '-' . DefenseInterface::MAX_RESISTANCE,
            ],

            // air_resist
            [
                // Отсутствует air_resist
                [
                    'physical_resist' => 0,
                    'fire_resist'     => 5,
                    'water_resist'    => 10,
                    'earth_resist'    => 20,
                    'life_resist'     => 25,
                    'death_resist'    => 30,
                    'defense'         => 654,
                    'magic_defense'   => 150,
                    'block'           => 34,
                    'magic_block'     => 25,
                    'mental_barrier'  => 100,
                ],
                DefenseException::INCORRECT_AIR_RESIST,
            ],
            [
                // air_resist некорректного типа
                [
                    'physical_resist' => 0,
                    'fire_resist'     => 5,
                    'water_resist'    => 10,
                    'air_resist'      => 0.0,
                    'earth_resist'    => 20,
                    'life_resist'     => 25,
                    'death_resist'    => 30,
                    'defense'         => 654,
                    'magic_defense'   => 150,
                    'block'           => 34,
                    'magic_block'     => 25,
                    'mental_barrier'  => 100,
                ],
                DefenseException::INCORRECT_AIR_RESIST,
            ],
            [
                // air_resist меньше минимального значения
                [
                    'physical_resist' => 0,
                    'fire_resist'     => 5,
                    'water_resist'    => 10,
                    'air_resist'      => DefenseInterface::MIN_RESISTANCE - 1,
                    'earth_resist'    => 20,
                    'life_resist'     => 25,
                    'death_resist'    => 30,
                    'defense'         => 654,
                    'magic_defense'   => 150,
                    'block'           => 34,
                    'magic_block'     => 25,
                    'mental_barrier'  => 100,
                ],
                DefenseException::INCORRECT_AIR_RESIST_VALUE . DefenseInterface::MIN_RESISTANCE . '-' . DefenseInterface::MAX_RESISTANCE,
            ],
            [
                // air_resist больше максимального значения
                [
                    'physical_resist' => 0,
                    'fire_resist'     => 5,
                    'water_resist'    => 10,
                    'air_resist'      => DefenseInterface::MAX_RESISTANCE + 1,
                    'earth_resist'    => 20,
                    'life_resist'     => 25,
                    'death_resist'    => 30,
                    'defense'         => 654,
                    'magic_defense'   => 150,
                    'block'           => 34,
                    'magic_block'     => 25,
                    'mental_barrier'  => 100,
                ],
                DefenseException::INCORRECT_AIR_RESIST_VALUE . DefenseInterface::MIN_RESISTANCE . '-' . DefenseInterface::MAX_RESISTANCE,
            ],

            // earth_resist
            [
                // Отсутствует earth_resist
                [
                    'physical_resist' => 0,
                    'fire_resist'     => 5,
                    'water_resist'    => 10,
                    'air_resist'      => 15,
                    'life_resist'     => 25,
                    'death_resist'    => 30,
                    'defense'         => 654,
                    'magic_defense'   => 150,
                    'block'           => 34,
                    'magic_block'     => 25,
                    'mental_barrier'  => 100,
                ],
                DefenseException::INCORRECT_EARTH_RESIST,
            ],
            [
                // earth_resist некорректного типа
                [
                    'physical_resist' => 0,
                    'fire_resist'     => 5,
                    'water_resist'    => 10,
                    'air_resist'      => 15,
                    'earth_resist'    => true,
                    'life_resist'     => 25,
                    'death_resist'    => 30,
                    'defense'         => 654,
                    'magic_defense'   => 150,
                    'block'           => 34,
                    'magic_block'     => 25,
                    'mental_barrier'  => 100,
                ],
                DefenseException::INCORRECT_EARTH_RESIST,
            ],
            [
                // earth_resist меньше минимального значения
                [
                    'physical_resist' => 0,
                    'fire_resist'     => 5,
                    'water_resist'    => 10,
                    'air_resist'      => 15,
                    'earth_resist'    => DefenseInterface::MIN_RESISTANCE - 1,
                    'life_resist'     => 25,
                    'death_resist'    => 30,
                    'defense'         => 654,
                    'magic_defense'   => 150,
                    'block'           => 34,
                    'magic_block'     => 25,
                    'mental_barrier'  => 100,
                ],
                DefenseException::INCORRECT_EARTH_RESIST_VALUE . DefenseInterface::MIN_RESISTANCE . '-' . DefenseInterface::MAX_RESISTANCE,
            ],
            [
                // earth_resist больше максимального значения
                [
                    'physical_resist' => 0,
                    'fire_resist'     => 5,
                    'water_resist'    => 10,
                    'air_resist'      => 15,
                    'earth_resist'    => DefenseInterface::MAX_RESISTANCE + 1,
                    'life_resist'     => 25,
                    'death_resist'    => 30,
                    'defense'         => 654,
                    'magic_defense'   => 150,
                    'block'           => 34,
                    'magic_block'     => 25,
                    'mental_barrier'  => 100,
                ],
                DefenseException::INCORRECT_EARTH_RESIST_VALUE . DefenseInterface::MIN_RESISTANCE . '-' . DefenseInterface::MAX_RESISTANCE,
            ],

            // life_resist
            [
                // Отсутствует life_resist
                [
                    'physical_resist' => 0,
                    'fire_resist'     => 5,
                    'water_resist'    => 10,
                    'air_resist'      => 15,
                    'earth_resist'    => 20,
                    'death_resist'    => 30,
                    'defense'         => 654,
                    'magic_defense'   => 150,
                    'block'           => 34,
                    'magic_block'     => 25,
                    'mental_barrier'  => 100,
                ],
                DefenseException::INCORRECT_LIFE_RESIST,
            ],
            [
                // life_resist некорректного типа
                [
                    'physical_resist' => 0,
                    'fire_resist'     => 5,
                    'water_resist'    => 10,
                    'air_resist'      => 15,
                    'earth_resist'    => 20,
                    'life_resist'     => [25],
                    'death_resist'    => 30,
                    'defense'         => 654,
                    'magic_defense'   => 150,
                    'block'           => 34,
                    'magic_block'     => 25,
                    'mental_barrier'  => 100,
                ],
                DefenseException::INCORRECT_LIFE_RESIST,
            ],
            [
                // life_resist меньше минимального значения
                [
                    'physical_resist' => 0,
                    'fire_resist'     => 5,
                    'water_resist'    => 10,
                    'air_resist'      => 15,
                    'earth_resist'    => 20,
                    'life_resist'     => DefenseInterface::MIN_RESISTANCE - 1,
                    'death_resist'    => 30,
                    'defense'         => 654,
                    'magic_defense'   => 150,
                    'block'           => 34,
                    'magic_block'     => 25,
                    'mental_barrier'  => 100,
                ],
                DefenseException::INCORRECT_LIFE_RESIST_VALUE . DefenseInterface::MIN_RESISTANCE . '-' . DefenseInterface::MAX_RESISTANCE,
            ],
            [
                // life_resist больше максимального значения
                [
                    'physical_resist' => 0,
                    'fire_resist'     => 5,
                    'water_resist'    => 10,
                    'air_resist'      => 15,
                    'earth_resist'    => 20,
                    'life_resist'     => DefenseInterface::MAX_RESISTANCE + 1,
                    'death_resist'    => 30,
                    'defense'         => 654,
                    'magic_defense'   => 150,
                    'block'           => 34,
                    'magic_block'     => 25,
                    'mental_barrier'  => 100,
                ],
                DefenseException::INCORRECT_LIFE_RESIST_VALUE . DefenseInterface::MIN_RESISTANCE . '-' . DefenseInterface::MAX_RESISTANCE,
            ],

            // death_resist
            [
                // Отсутствует death_resist
                [
                    'physical_resist' => 0,
                    'fire_resist'     => 5,
                    'water_resist'    => 10,
                    'air_resist'      => 15,
                    'earth_resist'    => 20,
                    'life_resist'     => 25,
                    'defense'         => 654,
                    'magic_defense'   => 150,
                    'block'           => 34,
                    'magic_block'     => 25,
                    'mental_barrier'  => 100,
                ],
                DefenseException::INCORRECT_DEATH_RESIST,
            ],
            [
                // death_resist некорректного типа
                [
                    'physical_resist' => 0,
                    'fire_resist'     => 5,
                    'water_resist'    => 10,
                    'air_resist'      => 15,
                    'earth_resist'    => 20,
                    'life_resist'     => 25,
                    'death_resist'    => '30',
                    'defense'         => 654,
                    'magic_defense'   => 150,
                    'block'           => 34,
                    'magic_block'     => 25,
                    'mental_barrier'  => 100,
                ],
                DefenseException::INCORRECT_DEATH_RESIST,
            ],
            [
                // death_resist меньше минимального значения
                [
                    'physical_resist' => 0,
                    'fire_resist'     => 5,
                    'water_resist'    => 10,
                    'air_resist'      => 15,
                    'earth_resist'    => 20,
                    'life_resist'     => 25,
                    'death_resist'    => DefenseInterface::MIN_RESISTANCE - 1,
                    'defense'         => 654,
                    'magic_defense'   => 150,
                    'block'           => 34,
                    'magic_block'     => 25,
                    'mental_barrier'  => 100,
                ],
                DefenseException::INCORRECT_DEATH_RESIST_VALUE . DefenseInterface::MIN_RESISTANCE . '-' . DefenseInterface::MAX_RESISTANCE,
            ],
            [
                // death_resist больше максимального значения
                [
                    'physical_resist' => 0,
                    'fire_resist'     => 5,
                    'water_resist'    => 10,
                    'air_resist'      => 15,
                    'earth_resist'    => 20,
                    'life_resist'     => 25,
                    'death_resist'    => DefenseInterface::MAX_RESISTANCE + 1,
                    'defense'         => 654,
                    'magic_defense'   => 150,
                    'block'           => 34,
                    'magic_block'     => 25,
                    'mental_barrier'  => 100,
                ],
                DefenseException::INCORRECT_DEATH_RESIST_VALUE . DefenseInterface::MIN_RESISTANCE . '-' . DefenseInterface::MAX_RESISTANCE,
            ],

            // defense
            [
                // Отсутствует defense
                [
                    'physical_resist' => 30,
                    'fire_resist'     => 5,
                    'water_resist'    => 10,
                    'air_resist'      => 15,
                    'earth_resist'    => 20,
                    'life_resist'     => 25,
                    'death_resist'    => 30,
                    'block'           => 0,
                    'magic_block'     => 25,
                    'magic_defense'   => 50,
                    'mental_barrier'  => 0,
                ],
                DefenseException::INCORRECT_DEFENSE,
            ],
            [
                // defense некорректного типа
                [
                    'physical_resist' => 30,
                    'fire_resist'     => 5,
                    'water_resist'    => 10,
                    'air_resist'      => 15,
                    'earth_resist'    => 20,
                    'life_resist'     => 25,
                    'death_resist'    => 30,
                    'defense'         => '100',
                    'magic_defense'   => 50,
                    'block'           => 0,
                    'magic_block'     => 25,
                    'mental_barrier'  => 0,
                ],
                DefenseException::INCORRECT_DEFENSE,
            ],
            [
                // defense меньше минимального значения
                [
                    'physical_resist' => 30,
                    'fire_resist'     => 5,
                    'water_resist'    => 10,
                    'air_resist'      => 15,
                    'earth_resist'    => 20,
                    'life_resist'     => 25,
                    'death_resist'    => 30,
                    'defense'         => DefenseInterface::MIN_DEFENSE - 1,
                    'magic_defense'   => 50,
                    'block'           => 0,
                    'magic_block'     => 25,
                    'mental_barrier'  => 0,
                ],
                DefenseException::INCORRECT_DEFENSE_VALUE . DefenseInterface::MIN_DEFENSE . '-' . DefenseInterface::MAX_DEFENSE,
            ],
            [
                // defense больше максимального значения
                [
                    'physical_resist' => 30,
                    'fire_resist'     => 5,
                    'water_resist'    => 10,
                    'air_resist'      => 15,
                    'earth_resist'    => 20,
                    'life_resist'     => 25,
                    'death_resist'    => 30,
                    'defense'         => DefenseInterface::MAX_DEFENSE + 1,
                    'magic_defense'   => 50,
                    'block'           => 0,
                    'magic_block'     => 25,
                    'mental_barrier'  => 0,
                ],
                DefenseException::INCORRECT_DEFENSE_VALUE . DefenseInterface::MIN_DEFENSE . '-' . DefenseInterface::MAX_DEFENSE,
            ],

            // magic_defense
            [
                // Отсутствует magic_defense
                [
                    'physical_resist' => 30,
                    'fire_resist'     => 5,
                    'water_resist'    => 10,
                    'air_resist'      => 15,
                    'earth_resist'    => 20,
                    'life_resist'     => 25,
                    'death_resist'    => 30,
                    'defense'         => 100,
                    'block'           => 0,
                    'magic_block'     => 25,
                    'mental_barrier'  => 0,
                ],
                DefenseException::INCORRECT_MAGIC_DEFENSE,
            ],
            [
                // magic_defense некорректного типа
                [
                    'physical_resist' => 30,
                    'fire_resist'     => 5,
                    'water_resist'    => 10,
                    'air_resist'      => 15,
                    'earth_resist'    => 20,
                    'life_resist'     => 25,
                    'death_resist'    => 30,
                    'defense'         => 100,
                    'magic_defense'   => true,
                    'block'           => 0,
                    'magic_block'     => 25,
                    'mental_barrier'  => 0,
                ],
                DefenseException::INCORRECT_MAGIC_DEFENSE,
            ],
            [
                // magic_defense меньше минимального значения
                [
                    'physical_resist' => 30,
                    'fire_resist'     => 5,
                    'water_resist'    => 10,
                    'air_resist'      => 15,
                    'earth_resist'    => 20,
                    'life_resist'     => 25,
                    'death_resist'    => 30,
                    'defense'         => 100,
                    'magic_defense'   => DefenseInterface::MIN_MAGIC_DEFENSE - 1,
                    'block'           => 0,
                    'magic_block'     => 25,
                    'mental_barrier'  => 0,
                ],
                DefenseException::INCORRECT_MAGIC_DEFENSE_VALUE . DefenseInterface::MIN_MAGIC_DEFENSE . '-' . DefenseInterface::MAX_MAGIC_DEFENSE,
            ],
            [
                // magic_defense больше максимального значения
                [
                    'physical_resist' => 30,
                    'fire_resist'     => 5,
                    'water_resist'    => 10,
                    'air_resist'      => 15,
                    'earth_resist'    => 20,
                    'life_resist'     => 25,
                    'death_resist'    => 30,
                    'defense'         => 100,
                    'magic_defense'   => DefenseInterface::MAX_MAGIC_DEFENSE + 1,
                    'block'           => 0,
                    'magic_block'     => 25,
                    'mental_barrier'  => 0,
                ],
                DefenseException::INCORRECT_MAGIC_DEFENSE_VALUE . DefenseInterface::MIN_MAGIC_DEFENSE . '-' . DefenseInterface::MAX_MAGIC_DEFENSE,
            ],

            // block
            [
                // Отсутствует block
                [
                    'physical_resist' => 30,
                    'fire_resist'     => 5,
                    'water_resist'    => 10,
                    'air_resist'      => 15,
                    'earth_resist'    => 20,
                    'life_resist'     => 25,
                    'death_resist'    => 30,
                    'defense'         => 100,
                    'magic_defense'   => 50,
                    'magic_block'     => 25,
                    'mental_barrier'  => 0,
                ],
                DefenseException::INCORRECT_BLOCK,
            ],
            [
                // block некорректного типа
                [
                    'physical_resist' => 30,
                    'fire_resist'     => 5,
                    'water_resist'    => 10,
                    'air_resist'      => 15,
                    'earth_resist'    => 20,
                    'life_resist'     => 25,
                    'death_resist'    => 30,
                    'defense'         => 100,
                    'magic_defense'   => 50,
                    'block'           => 50.5,
                    'magic_block'     => 25,
                    'mental_barrier'  => 0,
                ],
                DefenseException::INCORRECT_BLOCK,
            ],
            [
                // block меньше минимального значения
                [
                    'physical_resist' => 30,
                    'fire_resist'     => 5,
                    'water_resist'    => 10,
                    'air_resist'      => 15,
                    'earth_resist'    => 20,
                    'life_resist'     => 25,
                    'death_resist'    => 30,
                    'defense'         => 100,
                    'magic_defense'   => 50,
                    'block'           => DefenseInterface::MIN_BLOCK - 1,
                    'magic_block'     => 25,
                    'mental_barrier'  => 0,
                ],
                DefenseException::INCORRECT_BLOCK_VALUE . DefenseInterface::MIN_BLOCK . '-' . DefenseInterface::MAX_BLOCK,
            ],
            [
                // block больше максимального значения
                [
                    'physical_resist' => 30,
                    'fire_resist'     => 5,
                    'water_resist'    => 10,
                    'air_resist'      => 15,
                    'earth_resist'    => 20,
                    'life_resist'     => 25,
                    'death_resist'    => 30,
                    'defense'         => 100,
                    'magic_defense'   => 50,
                    'block'           => DefenseInterface::MAX_BLOCK + 1,
                    'magic_block'     => 25,
                    'mental_barrier'  => 0,
                ],
                DefenseException::INCORRECT_BLOCK_VALUE . DefenseInterface::MIN_BLOCK . '-' . DefenseInterface::MAX_BLOCK,
            ],

            // magic_block
            [
                // Отсутствует magic_block
                [
                    'physical_resist' => 30,
                    'fire_resist'     => 5,
                    'water_resist'    => 10,
                    'air_resist'      => 15,
                    'earth_resist'    => 20,
                    'life_resist'     => 25,
                    'death_resist'    => 30,
                    'defense'         => 100,
                    'magic_defense'   => 50,
                    'block'           => 0,
                    'mental_barrier'  => 0,
                ],
                DefenseException::INCORRECT_MAGIC_BLOCK,
            ],
            [
                // magic_block некорректного типа
                [
                    'physical_resist' => 30,
                    'fire_resist'     => 5,
                    'water_resist'    => 10,
                    'air_resist'      => 15,
                    'earth_resist'    => 20,
                    'life_resist'     => 25,
                    'death_resist'    => 30,
                    'defense'         => 100,
                    'magic_defense'   => 50,
                    'block'           => 0,
                    'magic_block'     => '0',
                    'mental_barrier'  => 0,
                ],
                DefenseException::INCORRECT_MAGIC_BLOCK,
            ],
            [
                // magic_block меньше минимального значения
                [
                    'physical_resist' => 30,
                    'fire_resist'     => 5,
                    'water_resist'    => 10,
                    'air_resist'      => 15,
                    'earth_resist'    => 20,
                    'life_resist'     => 25,
                    'death_resist'    => 30,
                    'defense'         => 100,
                    'magic_defense'   => 50,
                    'block'           => 0,
                    'magic_block'     => DefenseInterface::MIN_MAGIC_BLOCK - 1,
                    'mental_barrier'  => 0,
                ],
                DefenseException::INCORRECT_MAGIC_BLOCK_VALUE . DefenseInterface::MIN_MAGIC_BLOCK . '-' . DefenseInterface::MAX_MAGIC_BLOCK,
            ],
            [
                // magic_block больше максимального значения
                [
                    'physical_resist' => 30,
                    'fire_resist'     => 5,
                    'water_resist'    => 10,
                    'air_resist'      => 15,
                    'earth_resist'    => 20,
                    'life_resist'     => 25,
                    'death_resist'    => 30,
                    'defense'         => 100,
                    'magic_defense'   => 50,
                    'block'           => 0,
                    'magic_block'     => DefenseInterface::MAX_MAGIC_BLOCK + 1,
                    'mental_barrier'  => 0,
                ],
                DefenseException::INCORRECT_MAGIC_BLOCK_VALUE . DefenseInterface::MIN_MAGIC_BLOCK . '-' . DefenseInterface::MAX_MAGIC_BLOCK,
            ],

            // mental_barrier
            [
                // Отсутствует mental_barrier
                [
                    'physical_resist' => 30,
                    'fire_resist'     => 5,
                    'water_resist'    => 10,
                    'air_resist'      => 15,
                    'earth_resist'    => 20,
                    'life_resist'     => 25,
                    'death_resist'    => 30,
                    'defense'         => 100,
                    'magic_defense'   => 50,
                    'block'           => 75,
                    'magic_block'     => 25,
                ],
                DefenseException::INCORRECT_MENTAL_BARRIER,
            ],
            [
                // mental_barrier некорректного типа
                [
                    'physical_resist' => 30,
                    'fire_resist'     => 5,
                    'water_resist'    => 10,
                    'air_resist'      => 15,
                    'earth_resist'    => 20,
                    'life_resist'     => 25,
                    'death_resist'    => 30,
                    'defense'         => 100,
                    'magic_defense'   => 50,
                    'block'           => 75,
                    'magic_block'     => 25,
                    'mental_barrier'  => '50',
                ],
                DefenseException::INCORRECT_MENTAL_BARRIER,
            ],
            [
                // mental_barrier меньше минимального значения
                [
                    'physical_resist' => 30,
                    'fire_resist'     => 5,
                    'water_resist'    => 10,
                    'air_resist'      => 15,
                    'earth_resist'    => 20,
                    'life_resist'     => 25,
                    'death_resist'    => 30,
                    'defense'         => 100,
                    'magic_defense'   => 50,
                    'block'           => 75,
                    'magic_block'     => 25,
                    'mental_barrier'  => DefenseInterface::MIN_MENTAL_BARRIER - 1,
                ],
                DefenseException::INCORRECT_MENTAL_BARRIER_VALUE . DefenseInterface::MIN_MENTAL_BARRIER . '-' . DefenseInterface::MAX_MENTAL_BARRIER,
            ],
            [
                // mental_barrier больше максимального значения
                [
                    'physical_resist' => 30,
                    'fire_resist'     => 5,
                    'water_resist'    => 10,
                    'air_resist'      => 15,
                    'earth_resist'    => 20,
                    'life_resist'     => 25,
                    'death_resist'    => 30,
                    'defense'         => 100,
                    'magic_defense'   => 50,
                    'block'           => 75,
                    'magic_block'     => 25,
                    'mental_barrier'  => DefenseInterface::MAX_MENTAL_BARRIER + 1,
                ],
                DefenseException::INCORRECT_MENTAL_BARRIER_VALUE . DefenseInterface::MIN_MENTAL_BARRIER . '-' . DefenseInterface::MAX_MENTAL_BARRIER,
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
