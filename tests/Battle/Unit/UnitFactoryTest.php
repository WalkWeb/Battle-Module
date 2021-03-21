<?php

declare(strict_types=1);

namespace Tests\Battle\Unit;

use Battle\Classes\ClassFactory;
use Battle\Unit\UnitException;
use Battle\Unit\UnitFactory;
use Exception;
use PHPUnit\Framework\TestCase;

class UnitFactoryTest extends TestCase
{
    /**
     * Тест на успешное создание юнита через UnitFactory
     *
     * @dataProvider successDataProvider
     * @param array $data
     * @throws Exception
     */
    public function testUnitFactorySuccess(array $data): void
    {
        $unit = UnitFactory::create($data);
        $class = ClassFactory::create($data['class']);

        self::assertEquals($data['name'], $unit->getName());
        self::assertEquals($data['avatar'], $unit->getAvatar());
        self::assertEquals($data['damage'], $unit->getDamage());
        self::assertEquals($data['attack_speed'], $unit->getAttackSpeed());
        self::assertEquals($data['life'], $unit->getTotalLife());
        self::assertEquals($data['life'], $unit->getLife());
        self::assertEquals($data['melee'], $unit->isMelee());
        self::assertEquals($class, $unit->getClass());
    }

    /**
     * @dataProvider failDataProvider
     * @param array $data
     * @param string $error
     * @throws Exception
     */
    public function testUnitFactoryFail(array $data, string $error): void
    {
        $this->expectException(Exception::class);
        $this->expectErrorMessage($error);
        UnitFactory::create($data);
    }

    public function successDataProvider(): array
    {
        return [
            [
                [
                    'name'         => 'Skeleton',
                    'avatar'       => '/images/avas/monsters/003.png',
                    'damage'       => 15,
                    'attack_speed' => 1.2,
                    'life'         => 80,
                    'melee'        => true,
                    'class'        => 1,
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
                [
                    // отсутствует name
                    'avatar'       => '/images/avas/monsters/003.png',
                    'damage'       => 15,
                    'attack_speed' => 1.2,
                    'life'         => 80,
                    'melee'        => true,
                    'class'        => 1,
                ],
                'error' => UnitException::INCORRECT_NAME,
            ],
            [
                [
                    // некорректный name
                    'name'         => 123,
                    'avatar'       => '/images/avas/monsters/003.png',
                    'damage'       => 15,
                    'attack_speed' => 1.2,
                    'life'         => 80,
                    'melee'        => true,
                    'class'        => 1,
                ],
                'error' => UnitException::INCORRECT_NAME,
            ],
            [
                [
                    // отсутствует avatar
                    'name'         => 'Skeleton',
                    'damage'       => 15,
                    'attack_speed' => 1.2,
                    'life'         => 80,
                    'melee'        => true,
                    'class'        => 1,
                ],
                'error' => UnitException::INCORRECT_AVATAR,
            ],
            [
                [
                    // некорректный avatar
                    'name'         => 'Skeleton',
                    'avatar'       => ['ava' => '/images/avas/monsters/003.png'],
                    'damage'       => 15,
                    'attack_speed' => 1.2,
                    'life'         => 80,
                    'melee'        => true,
                    'class'        => 1,
                ],
                'error' => UnitException::INCORRECT_AVATAR,
            ],
            [
                [
                    // отсутствует damage
                    'name'         => 'Skeleton',
                    'avatar'       => '/images/avas/monsters/003.png',
                    'attack_speed' => 1.2,
                    'life'         => 80,
                    'melee'        => true,
                    'class'        => 1,
                ],
                'error' => UnitException::INCORRECT_DAMAGE,
            ],
            [
                [
                    // некорректный damage
                    'name'         => 'Skeleton',
                    'avatar'       => '/images/avas/monsters/003.png',
                    'damage'       => 15.3,
                    'attack_speed' => 1.2,
                    'life'         => 80,
                    'melee'        => true,
                    'class'        => 1,
                ],
                'error' => UnitException::INCORRECT_DAMAGE,
            ],
            [
                [
                    // отсутствует attack_speed
                    'name'         => 'Skeleton',
                    'avatar'       => '/images/avas/monsters/003.png',
                    'damage'       => 15,
                    'life'         => 80,
                    'melee'        => true,
                    'class'        => 1,
                ],
                'error' => UnitException::INCORRECT_ATTACK_SPEED,
            ],
            [
                [
                    // некорректный attack_speed
                    'name'         => 'Skeleton',
                    'avatar'       => '/images/avas/monsters/003.png',
                    'damage'       => 15,
                    'attack_speed' => 1,
                    'life'         => 80,
                    'melee'        => true,
                    'class'        => 1,
                ],
                'error' => UnitException::INCORRECT_ATTACK_SPEED,
            ],
            [
                [
                    // отсутствует life
                    'name'         => 'Skeleton',
                    'avatar'       => '/images/avas/monsters/003.png',
                    'damage'       => 15,
                    'attack_speed' => 1.2,
                    'melee'        => true,
                    'class'        => 1,
                ],
                'error' => UnitException::INCORRECT_LIFE,
            ],
            [
                [
                    // некорректный life
                    'name'         => 'Skeleton',
                    'avatar'       => '/images/avas/monsters/003.png',
                    'damage'       => 15,
                    'attack_speed' => 1.2,
                    'life'         => 80.0,
                    'melee'        => true,
                    'class'        => 1,
                ],
                'error' => UnitException::INCORRECT_LIFE,
            ],
            [
                [
                    // отсутствует melee
                    'name'         => 'Skeleton',
                    'avatar'       => '/images/avas/monsters/003.png',
                    'damage'       => 15,
                    'attack_speed' => 1.2,
                    'life'         => 80,
                    'class'        => 1,
                ],
                'error' => UnitException::INCORRECT_MELEE,
            ],
            [
                [
                    // некорректный melee
                    'name'         => 'Skeleton',
                    'avatar'       => '/images/avas/monsters/003.png',
                    'damage'       => 15,
                    'attack_speed' => 1.2,
                    'life'         => 80,
                    'melee'        => 1,
                    'class'        => 1,
                ],
                'error' => UnitException::INCORRECT_MELEE,
            ],
            [
                [
                    // отсутствует class
                    'name'         => 'Skeleton',
                    'avatar'       => '/images/avas/monsters/003.png',
                    'damage'       => 15,
                    'attack_speed' => 1.2,
                    'life'         => 80,
                    'melee'        => true,
                ],
                'error' => UnitException::INCORRECT_CLASS,
            ],
            [
                [
                    // некорректный class
                    'name'         => 'Skeleton',
                    'avatar'       => '/images/avas/monsters/003.png',
                    'damage'       => 15,
                    'attack_speed' => 1.2,
                    'life'         => 80,
                    'melee'        => true,
                    'class'        => 'warrior',
                ],
                'error' => UnitException::INCORRECT_CLASS,
            ],
        ];
    }
}
