<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Offense;

use Battle\Unit\Defense\Defense;
use Battle\Unit\Defense\DefenseInterface;
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

        self::assertEquals($data['physical_damage'], $offense->getDamage($this->getDefense()));
        self::assertEquals($data['physical_damage'], $offense->getPhysicalDamage());
        self::assertEquals($data['attack_speed'], $offense->getAttackSpeed());
        self::assertEquals($data['accuracy'], $offense->getAccuracy());
        self::assertEquals($data['magic_accuracy'], $offense->getMagicAccuracy());
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
                    'type_damage'     => 1,
                    'physical_damage' => 10,
                    'attack_speed'    => 1,
                    'accuracy'        => 200,
                    'magic_accuracy'  => 100,
                    'block_ignore'    => 0,
                ],
            ],
            [
                // Скорость атаки float
                [
                    'type_damage'     => 2,
                    'physical_damage' => 10,
                    'attack_speed'    => 1.3,
                    'accuracy'        => 200,
                    'magic_accuracy'  => 100,
                    'block_ignore'    => 0,
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

            // type_damage
            [
                // Отсутствует type_damage
                [
                    'physical_damage' => 10,
                    'attack_speed'    => 1,
                    'accuracy'        => 200,
                    'magic_accuracy'  => 100,
                    'block_ignore'    => 0,
                ],
                OffenseException::INCORRECT_TYPE_DAMAGE,
            ],
            [
                // type_damage некорректного типа
                [
                    'type_damage'     => '1',
                    'physical_damage' => 10,
                    'attack_speed'    => 1,
                    'accuracy'        => 200,
                    'magic_accuracy'  => 100,
                    'block_ignore'    => 0,
                ],
                OffenseException::INCORRECT_TYPE_DAMAGE,
            ],
            [
                // type_damage выходит за пределы допустимых значений (1 или 2)
                [
                    'type_damage'     => 3,
                    'physical_damage' => 10,
                    'attack_speed'    => 1,
                    'accuracy'        => 200,
                    'magic_accuracy'  => 100,
                    'block_ignore'    => 0,
                ],
                OffenseException::INCORRECT_TYPE_DAMAGE_VALUE,
            ],

            // physical_damage
            [
                // Отсутствует physical_damage
                [
                    'type_damage'     => 1,
                    'attack_speed'    => 1,
                    'accuracy'        => 200,
                    'magic_accuracy'  => 100,
                    'block_ignore'    => 0,
                ],
                OffenseException::INCORRECT_PHYSICAL_DAMAGE,
            ],
            [
                // physical_damage некорректного типа
                [
                    'type_damage'     => 1,
                    'physical_damage' => 10.5,
                    'attack_speed'    => 1,
                    'accuracy'        => 200,
                    'magic_accuracy'  => 100,
                    'block_ignore'    => 0,
                ],
                OffenseException::INCORRECT_PHYSICAL_DAMAGE,
            ],
            [
                // physical_damage меньше минимального значения
                [
                    'type_damage'     => 1,
                    'physical_damage' => OffenseInterface::MIN_DAMAGE - 1,
                    'attack_speed'    => 1,
                    'accuracy'        => 200,
                    'magic_accuracy'  => 100,
                    'block_ignore'    => 0,
                ],
                OffenseException::INCORRECT_PHYSICAL_DAMAGE_VALUE . OffenseInterface::MIN_DAMAGE . '-' . OffenseInterface::MAX_DAMAGE,
            ],
            [
                // physical_damage больше максимального значения
                [
                    'type_damage'     => 1,
                    'physical_damage' => OffenseInterface::MAX_DAMAGE + 1,
                    'attack_speed'    => 1,
                    'accuracy'        => 200,
                    'magic_accuracy'  => 100,
                    'block_ignore'    => 0,
                ],
                OffenseException::INCORRECT_PHYSICAL_DAMAGE_VALUE . OffenseInterface::MIN_DAMAGE . '-' . OffenseInterface::MAX_DAMAGE,
            ],

            // attack_speed
            [
                // Отсутствует attack_speed
                [
                    'type_damage'     => 1,
                    'physical_damage' => 10,
                    'accuracy'        => 200,
                    'magic_accuracy'  => 100,
                    'block_ignore'    => 0,
                ],
                OffenseException::INCORRECT_ATTACK_SPEED,
            ],
            [
                // attack_speed некорректного типа
                [
                    'type_damage'     => 1,
                    'physical_damage' => 10,
                    'attack_speed'    => true,
                    'accuracy'        => 200,
                    'magic_accuracy'  => 100,
                    'block_ignore'    => 0,
                ],
                OffenseException::INCORRECT_ATTACK_SPEED,
            ],
            [
                // attack_speed меньше минимального значения
                [
                    'type_damage'     => 1,
                    'physical_damage' => 10,
                    'attack_speed'    => OffenseInterface::MIN_ATTACK_SPEED - 0.1,
                    'accuracy'        => 200,
                    'magic_accuracy'  => 100,
                    'block_ignore'    => 0,
                ],
                OffenseException::INCORRECT_ATTACK_SPEED_VALUE . OffenseInterface::MIN_ATTACK_SPEED . '-' . OffenseInterface::MAX_ATTACK_SPEED,
            ],
            [
                // attack_speed больше максимального значения
                [
                    'type_damage'     => 1,
                    'physical_damage' => 10,
                    'attack_speed'    => OffenseInterface::MAX_ATTACK_SPEED + 0.1,
                    'accuracy'        => 200,
                    'magic_accuracy'  => 100,
                    'block_ignore'    => 0,
                ],
                OffenseException::INCORRECT_ATTACK_SPEED_VALUE . OffenseInterface::MIN_ATTACK_SPEED . '-' . OffenseInterface::MAX_ATTACK_SPEED,
            ],

            // accuracy
            [
                // Отсутствует accuracy
                [
                    'type_damage'     => 1,
                    'physical_damage' => 10,
                    'attack_speed'    => 1.3,
                    'magic_accuracy'  => 100,
                    'block_ignore'    => 0,
                ],
                OffenseException::INCORRECT_ACCURACY,
            ],
            [
                // accuracy некорректного типа
                [
                    'type_damage'     => 1,
                    'physical_damage' => 10,
                    'attack_speed'    => 1.3,
                    'accuracy'        => [200],
                    'magic_accuracy'  => 100,
                    'block_ignore'    => 0,
                ],
                OffenseException::INCORRECT_ACCURACY,
            ],
            [
                // accuracy меньше минимального значения
                [
                    'type_damage'     => 1,
                    'physical_damage' => 10,
                    'attack_speed'    => 1.3,
                    'accuracy'        => OffenseInterface::MIN_ACCURACY - 1,
                    'magic_accuracy'  => 100,
                    'block_ignore'    => 0,
                ],
                OffenseException::INCORRECT_ACCURACY_VALUE . OffenseInterface::MIN_ACCURACY . '-' . OffenseInterface::MAX_ACCURACY,
            ],
            [
                // accuracy больше максимального значения
                [
                    'type_damage'     => 1,
                    'physical_damage' => 10,
                    'attack_speed'    => 1.3,
                    'accuracy'        => OffenseInterface::MAX_ACCURACY + 1,
                    'magic_accuracy'  => 100,
                    'block_ignore'    => 0,
                ],
                OffenseException::INCORRECT_ACCURACY_VALUE . OffenseInterface::MIN_ACCURACY . '-' . OffenseInterface::MAX_ACCURACY,
            ],

            // magic_accuracy
            [
                // Отсутствует magic_accuracy
                [
                    'type_damage'     => 1,
                    'physical_damage' => 10,
                    'attack_speed'    => 1,
                    'accuracy'        => 200,
                    'block_ignore'    => 0,
                ],
                OffenseException::INCORRECT_MAGIC_ACCURACY,
            ],
            [
                // magic_accuracy некорректного типа
                [
                    'type_damage'     => 1,
                    'physical_damage' => 10,
                    'attack_speed'    => 1,
                    'accuracy'        => 200,
                    'magic_accuracy'  => '100',
                    'block_ignore'    => 0,
                ],
                OffenseException::INCORRECT_MAGIC_ACCURACY,
            ],
            [
                // magic_accuracy меньше минимального значения
                [
                    'type_damage'     => 1,
                    'physical_damage' => 10,
                    'attack_speed'    => 1,
                    'accuracy'        => 200,
                    'magic_accuracy'  => OffenseInterface::MIN_MAGIC_ACCURACY - 1,
                    'block_ignore'    => 0,
                ],
                OffenseException::INCORRECT_MAGIC_ACCURACY_VALUE . OffenseInterface::MIN_MAGIC_ACCURACY . '-' . OffenseInterface::MAX_MAGIC_ACCURACY,
            ],
            [
                // magic_accuracy больше максимального значения
                [
                    'type_damage'     => 1,
                    'physical_damage' => 10,
                    'attack_speed'    => 1,
                    'accuracy'        => 200,
                    'magic_accuracy'  => OffenseInterface::MAX_MAGIC_ACCURACY + 1,
                    'block_ignore'    => 0,
                ],
                OffenseException::INCORRECT_MAGIC_ACCURACY_VALUE . OffenseInterface::MIN_MAGIC_ACCURACY . '-' . OffenseInterface::MAX_MAGIC_ACCURACY,
            ],

            // block_ignore
            [
                // Отсутствует block_ignore
                [
                    'type_damage'     => 1,
                    'physical_damage' => 10,
                    'attack_speed'    => 1.3,
                    'accuracy'        => 200,
                    'magic_accuracy'  => 100,
                ],
                OffenseException::INCORRECT_BLOCK_IGNORE,
            ],
            [
                // block_ignore некорректного типа
                [
                    'type_damage'     => 1,
                    'physical_damage' => 10,
                    'attack_speed'    => 1.3,
                    'accuracy'        => 200,
                    'magic_accuracy'  => 100,
                    'block_ignore'    => 0.0,
                ],
                OffenseException::INCORRECT_BLOCK_IGNORE,
            ],
            [
                // block_ignore меньше минимального значения
                [
                    'type_damage'     => 1,
                    'physical_damage' => 10,
                    'attack_speed'    => 1.3,
                    'accuracy'        => 200,
                    'magic_accuracy'  => 100,
                    'block_ignore'    => OffenseInterface::MIN_BLOCK_IGNORE - 1,
                ],
                OffenseException::INCORRECT_BLOCK_IGNORE_VALUE . OffenseInterface::MIN_BLOCK_IGNORE . '-' . OffenseInterface::MAX_BLOCK_IGNORE,
            ],
            [
                // block_ignore больше максимального значения
                [
                    'type_damage'     => 1,
                    'physical_damage' => 10,
                    'attack_speed'    => 1.3,
                    'accuracy'        => 200,
                    'magic_accuracy'  => 100,
                    'block_ignore'    => OffenseInterface::MAX_BLOCK_IGNORE + 1,
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

    /**
     * @return DefenseInterface
     * @throws Exception
     */
    private function getDefense(): DefenseInterface
    {
        return new Defense(0, 10, 10, 10, 5, 0);
    }
}
