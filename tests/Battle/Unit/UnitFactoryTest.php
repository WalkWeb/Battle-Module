<?php

declare(strict_types=1);

namespace Tests\Battle\Unit;

use Battle\Classes\UnitClassFactory;
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
        $class = UnitClassFactory::create($data['class']);

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
                // attack_speed float
                [
                    'id'           => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
                    'name'         => 'Skeleton',
                    'avatar'       => '/images/avas/monsters/003.png',
                    'damage'       => 15,
                    'attack_speed' => 1.2,
                    'life'         => 80,
                    'melee'        => true,
                    'class'        => 1,
                ],
                // attack_speed int - такой вариант также доступен
                [
                    'id'           => '5aa0d764-e92d-4137-beed-f7f590b08165',
                    'name'         => 'Skeleton',
                    'avatar'       => '/images/avas/monsters/003.png',
                    'damage'       => 15,
                    'attack_speed' => 1,
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
                    // отсутствует id
                    'name'         => 'Skeleton',
                    'avatar'       => '/images/avas/monsters/003.png',
                    'damage'       => 15,
                    'attack_speed' => 1.2,
                    'life'         => 80,
                    'melee'        => true,
                    'class'        => 1,
                ],
                'error' => UnitException::INCORRECT_ID,
            ],
            [
                [
                    // id некорректного типа
                    'id'           => 123123,
                    'name'         => 'Skeleton',
                    'avatar'       => '/images/avas/monsters/003.png',
                    'damage'       => 15,
                    'attack_speed' => 1.2,
                    'life'         => 80,
                    'melee'        => true,
                    'class'        => 1,
                ],
                'error' => UnitException::INCORRECT_ID,
            ],
            [
                [
                    // id пустая строка
                    'id'           => '',
                    'name'         => 'Skeleton',
                    'avatar'       => '/images/avas/monsters/003.png',
                    'damage'       => 15,
                    'attack_speed' => 1.2,
                    'life'         => 80,
                    'melee'        => true,
                    'class'        => 1,
                ],
                'error' => UnitException::INCORRECT_ID,
            ],
            [
                [
                    // отсутствует name
                    'id'           => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
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
                    'id'           => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
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
                    'id'           => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
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
                    'id'           => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
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
                    'id'           => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
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
                    'id'           => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
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
                    'id'           => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
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
                    'id'           => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
                    'name'         => 'Skeleton',
                    'avatar'       => '/images/avas/monsters/003.png',
                    'damage'       => 15,
                    'attack_speed' => '1',
                    'life'         => 80,
                    'melee'        => true,
                    'class'        => 1,
                ],
                'error' => UnitException::INCORRECT_ATTACK_SPEED,
            ],
            [
                [
                    // отсутствует life
                    'id'           => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
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
                    'id'           => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
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
                    'id'           => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
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
                    'id'           => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
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
                    'id'           => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
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
                    'id'           => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
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
