<?php

declare(strict_types=1);

namespace Tests\Battle\Unit;

use Battle\Classes\ClassFactoryException;
use Battle\Classes\UnitClassFactory;
use Battle\Unit\UnitException;
use Battle\Unit\UnitFactory;
use Battle\Unit\UnitInterface;
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
        self::assertEquals($data['level'], $unit->getLevel());
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

    /**
     * @throws UnitException
     * @throws ClassFactoryException
     */
    public function testUnitFactoryHtmlspecialchars(): void
    {
        $data = [
            'id'           => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
            'name'         => '<b>Skeleton</b>',
            'level'        => 1,
            'avatar'       => '/images/avas/monsters/003.png',
            'damage'       => 15,
            'attack_speed' => 1.2,
            'life'         => 80,
            'total_life'   => 80,
            'melee'        => true,
            'class'        => 1,
        ];

        $unit = UnitFactory::create($data);

        self::assertEquals(htmlspecialchars($data['name']), $unit->getName());
    }

    public function successDataProvider(): array
    {
        return [
            [
                // attack_speed float
                [
                    'id'           => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
                    'name'         => 'Skeleton',
                    'level'        => 3,
                    'avatar'       => '/images/avas/monsters/003.png',
                    'damage'       => 15,
                    'attack_speed' => 1.2,
                    'life'         => 80,
                    'total_life'   => 80,
                    'melee'        => true,
                    'class'        => 1,
                ],
                // attack_speed int - такой вариант также доступен
                [
                    'id'           => '5aa0d764-e92d-4137-beed-f7f590b08165',
                    'name'         => 'Skeleton',
                    'level'        => 5,
                    'avatar'       => '/images/avas/monsters/003.png',
                    'damage'       => 15,
                    'attack_speed' => 1,
                    'life'         => 80,
                    'total_life'   => 80,
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
                    'level'        => 3,
                    'avatar'       => '/images/avas/monsters/003.png',
                    'damage'       => 15,
                    'attack_speed' => 1.2,
                    'life'         => 80,
                    'total_life'   => 80,
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
                    'level'        => 3,
                    'avatar'       => '/images/avas/monsters/003.png',
                    'damage'       => 15,
                    'attack_speed' => 1.2,
                    'life'         => 80,
                    'total_life'   => 80,
                    'melee'        => true,
                    'class'        => 1,
                ],
                'error' => UnitException::INCORRECT_ID,
            ],
            // todo Временно допускаем такой вариант
//            [
//                [
//                    // id пустая строка
//                    'id'           => '',
//                    'name'         => 'Skeleton',
//                    'level'        => 3,
//                    'avatar'       => '/images/avas/monsters/003.png',
//                    'damage'       => 15,
//                    'attack_speed' => 1.2,
//                    'life'         => 80,
//                    'total_life'   => 80,
//                    'melee'        => true,
//                    'class'        => 1,
//                ],
//                'error' => UnitException::INCORRECT_ID,
//            ],
            [
                [
                    // отсутствует name
                    'id'           => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
                    'level'        => 3,
                    'avatar'       => '/images/avas/monsters/003.png',
                    'damage'       => 15,
                    'attack_speed' => 1.2,
                    'life'         => 80,
                    'total_life'   => 80,
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
                    'level'        => 3,
                    'avatar'       => '/images/avas/monsters/003.png',
                    'damage'       => 15,
                    'attack_speed' => 1.2,
                    'life'         => 80,
                    'total_life'   => 80,
                    'melee'        => true,
                    'class'        => 1,
                ],
                'error' => UnitException::INCORRECT_NAME,
            ],
            [
                [
                    // name length < UnitInterface::MIN_NAME_LENGTH
                    'id'           => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
                    'name'         => '',
                    'level'        => 3,
                    'avatar'       => '/images/avas/monsters/003.png',
                    'damage'       => 15,
                    'attack_speed' => 1.2,
                    'life'         => 80,
                    'total_life'   => 80,
                    'melee'        => true,
                    'class'        => 1,
                ],
                'error' => UnitException::INCORRECT_NAME_VALUE . UnitInterface::MIN_NAME_LENGTH . '-' . UnitInterface::MAX_NAME_LENGTH,
            ],
            [
                [
                    // name length > UnitInterface::MAX_NAME_LENGTH
                    'id'           => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
                    'name'         => 'lllllllllllllllllllll',
                    'level'        => 3,
                    'avatar'       => '/images/avas/monsters/003.png',
                    'damage'       => 15,
                    'attack_speed' => 1.2,
                    'life'         => 80,
                    'total_life'   => 80,
                    'melee'        => true,
                    'class'        => 1,
                ],
                'error' => UnitException::INCORRECT_NAME_VALUE . UnitInterface::MIN_NAME_LENGTH . '-' . UnitInterface::MAX_NAME_LENGTH,
            ],
            [
                [
                    // отсутствует avatar
                    'id'           => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
                    'name'         => 'Skeleton',
                    'level'        => 3,
                    'damage'       => 15,
                    'attack_speed' => 1.2,
                    'life'         => 80,
                    'total_life'   => 80,
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
                    'level'        => 3,
                    'avatar'       => ['ava' => '/images/avas/monsters/003.png'],
                    'damage'       => 15,
                    'attack_speed' => 1.2,
                    'life'         => 80,
                    'total_life'   => 80,
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
                    'level'        => 3,
                    'avatar'       => '/images/avas/monsters/003.png',
                    'attack_speed' => 1.2,
                    'life'         => 80,
                    'total_life'   => 80,
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
                    'level'        => 3,
                    'avatar'       => '/images/avas/monsters/003.png',
                    'damage'       => 15.3,
                    'attack_speed' => 1.2,
                    'life'         => 80,
                    'total_life'   => 80,
                    'melee'        => true,
                    'class'        => 1,
                ],
                'error' => UnitException::INCORRECT_DAMAGE,
            ],
            [
                [
                    // damage < UnitInterface::MIN_DAMAGE
                    'id'           => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
                    'name'         => 'Skeleton',
                    'level'        => 3,
                    'avatar'       => '/images/avas/monsters/003.png',
                    'damage'       => UnitInterface::MIN_DAMAGE - 1,
                    'attack_speed' => 1.2,
                    'life'         => 80,
                    'total_life'   => 80,
                    'melee'        => true,
                    'class'        => 1,
                ],
                'error' => UnitException::INCORRECT_DAMAGE_VALUE . UnitInterface::MIN_DAMAGE . '-' . UnitInterface::MAX_DAMAGE,
            ],
            [
                [
                    // damage > UnitInterface::MAX_DAMAGE
                    'id'           => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
                    'name'         => 'Skeleton',
                    'level'        => 3,
                    'avatar'       => '/images/avas/monsters/003.png',
                    'damage'       => UnitInterface::MAX_DAMAGE + 1,
                    'attack_speed' => 1.2,
                    'life'         => 80,
                    'total_life'   => 80,
                    'melee'        => true,
                    'class'        => 1,
                ],
                'error' => UnitException::INCORRECT_DAMAGE_VALUE . UnitInterface::MIN_DAMAGE . '-' . UnitInterface::MAX_DAMAGE,
            ],
            [
                [
                    // отсутствует attack_speed
                    'id'           => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
                    'name'         => 'Skeleton',
                    'level'        => 3,
                    'avatar'       => '/images/avas/monsters/003.png',
                    'damage'       => 15,
                    'life'         => 80,
                    'total_life'   => 80,
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
                    'level'        => 3,
                    'avatar'       => '/images/avas/monsters/003.png',
                    'damage'       => 15,
                    'attack_speed' => '1',
                    'life'         => 80,
                    'total_life'   => 80,
                    'melee'        => true,
                    'class'        => 1,
                ],
                'error' => UnitException::INCORRECT_ATTACK_SPEED,
            ],
            [
                [
                    // attack_speed < UnitInterface::MIN_ATTACK_SPEED
                    'id'           => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
                    'name'         => 'Skeleton',
                    'level'        => 3,
                    'avatar'       => '/images/avas/monsters/003.png',
                    'damage'       => 15,
                    'attack_speed' => UnitInterface::MIN_ATTACK_SPEED - 0.1,
                    'life'         => 80,
                    'total_life'   => 80,
                    'melee'        => true,
                    'class'        => 1,
                ],
                'error' => UnitException::INCORRECT_ATTACK_SPEED_VALUE . UnitInterface::MIN_ATTACK_SPEED . '-' . UnitInterface::MAX_ATTACK_SPEED,
            ],
            [
                [
                    // attack_speed > UnitInterface::MAX_ATTACK_SPEED
                    'id'           => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
                    'name'         => 'Skeleton',
                    'level'        => 3,
                    'avatar'       => '/images/avas/monsters/003.png',
                    'damage'       => 15,
                    'attack_speed' =>  UnitInterface::MAX_ATTACK_SPEED + 0.1,
                    'life'         => 80,
                    'total_life'   => 80,
                    'melee'        => true,
                    'class'        => 1,
                ],
                'error' => UnitException::INCORRECT_ATTACK_SPEED_VALUE . UnitInterface::MIN_ATTACK_SPEED . '-' . UnitInterface::MAX_ATTACK_SPEED,
            ],
            [
                [
                    // отсутствует life
                    'id'           => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
                    'name'         => 'Skeleton',
                    'level'        => 3,
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
                    'level'        => 3,
                    'avatar'       => '/images/avas/monsters/003.png',
                    'damage'       => 15,
                    'attack_speed' => 1.2,
                    'life'         => 80.0,
                    'total_life'   => 80,
                    'melee'        => true,
                    'class'        => 1,
                ],
                'error' => UnitException::INCORRECT_LIFE,
            ],
            [
                [
                    // life < UnitInterface::MIN_LIFE
                    'id'           => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
                    'name'         => 'Skeleton',
                    'level'        => 3,
                    'avatar'       => '/images/avas/monsters/003.png',
                    'damage'       => 15,
                    'attack_speed' => 1.2,
                    'life'         => UnitInterface::MIN_LIFE - 1,
                    'total_life'   => 80,
                    'melee'        => true,
                    'class'        => 1,
                ],
                'error' => UnitException::INCORRECT_LIFE_VALUE . UnitInterface::MIN_LIFE . '-' . UnitInterface::MAX_LIFE,
            ],
            [
                [
                    // life > UnitInterface::MAX_LIFE
                    'id'           => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
                    'name'         => 'Skeleton',
                    'level'        => 3,
                    'avatar'       => '/images/avas/monsters/003.png',
                    'damage'       => 15,
                    'attack_speed' => 1.2,
                    'life'         => UnitInterface::MAX_LIFE + 1,
                    'total_life'   => 80,
                    'melee'        => true,
                    'class'        => 1,
                ],
                'error' => UnitException::INCORRECT_LIFE_VALUE . UnitInterface::MIN_LIFE . '-' . UnitInterface::MAX_LIFE,
            ],
            [
                [
                    // отсутствует total life
                    'id'           => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
                    'name'         => 'Skeleton',
                    'level'        => 3,
                    'avatar'       => '/images/avas/monsters/003.png',
                    'damage'       => 15,
                    'attack_speed' => 1.2,
                    'life'         => 80,
                    'melee'        => true,
                    'class'        => 1,
                ],
                'error' => UnitException::INCORRECT_TOTAL_LIFE,
            ],
            [
                [
                    // некорректный total life
                    'id'           => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
                    'name'         => 'Skeleton',
                    'level'        => 3,
                    'avatar'       => '/images/avas/monsters/003.png',
                    'damage'       => 15,
                    'attack_speed' => 1.2,
                    'life'         => 80,
                    'total_life'   => 80.0,
                    'melee'        => true,
                    'class'        => 1,
                ],
                'error' => UnitException::INCORRECT_TOTAL_LIFE,
            ],
            [
                [
                    // total life < UnitInterface::MIN_TOTAL_LIFE
                    'id'           => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
                    'name'         => 'Skeleton',
                    'level'        => 3,
                    'avatar'       => '/images/avas/monsters/003.png',
                    'damage'       => 15,
                    'attack_speed' => 1.2,
                    'life'         => 0,
                    'total_life'   => UnitInterface::MIN_TOTAL_LIFE - 1,
                    'melee'        => true,
                    'class'        => 1,
                ],
                'error' => UnitException::INCORRECT_TOTAL_LIFE_VALUE . UnitInterface::MIN_TOTAL_LIFE . '-' . UnitInterface::MAX_TOTAL_LIFE,
            ],
            [
                [
                    // total life > UnitInterface::MAX_TOTAL_LIFE
                    'id'           => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
                    'name'         => 'Skeleton',
                    'level'        => 3,
                    'avatar'       => '/images/avas/monsters/003.png',
                    'damage'       => 15,
                    'attack_speed' => 1.2,
                    'life'         => 0,
                    'total_life'   => UnitInterface::MAX_TOTAL_LIFE + 1,
                    'melee'        => true,
                    'class'        => 1,
                ],
                'error' => UnitException::INCORRECT_TOTAL_LIFE_VALUE . UnitInterface::MIN_TOTAL_LIFE . '-' . UnitInterface::MAX_TOTAL_LIFE,
            ],
            [
                [
                    // отсутствует melee
                    'id'           => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
                    'name'         => 'Skeleton',
                    'level'        => 3,
                    'avatar'       => '/images/avas/monsters/003.png',
                    'damage'       => 15,
                    'attack_speed' => 1.2,
                    'life'         => 80,
                    'total_life'   => 80,
                    'class'        => 1,
                ],
                'error' => UnitException::INCORRECT_MELEE,
            ],
            [
                [
                    // некорректный melee
                    'id'           => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
                    'name'         => 'Skeleton',
                    'level'        => 3,
                    'avatar'       => '/images/avas/monsters/003.png',
                    'damage'       => 15,
                    'attack_speed' => 1.2,
                    'life'         => 80,
                    'total_life'   => 80,
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
                    'level'        => 3,
                    'avatar'       => '/images/avas/monsters/003.png',
                    'damage'       => 15,
                    'attack_speed' => 1.2,
                    'life'         => 80,
                    'total_life'   => 80,
                    'melee'        => true,
                ],
                'error' => UnitException::INCORRECT_CLASS,
            ],
            [
                [
                    // некорректный class
                    'id'           => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
                    'name'         => 'Skeleton',
                    'level'        => 3,
                    'avatar'       => '/images/avas/monsters/003.png',
                    'damage'       => 15,
                    'attack_speed' => 1.2,
                    'life'         => 80,
                    'total_life'   => 80,
                    'melee'        => true,
                    'class'        => 'warrior',
                ],
                'error' => UnitException::INCORRECT_CLASS,
            ],
            [
                [
                    // life большие total life
                    'id'           => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
                    'name'         => 'Skeleton',
                    'level'        => 3,
                    'avatar'       => '/images/avas/monsters/003.png',
                    'damage'       => 15,
                    'attack_speed' => 1.2,
                    'life'         => 90,
                    'total_life'   => 80,
                    'melee'        => true,
                    'class'        => 1,
                ],
                'error' => UnitException::LIFE_MORE_TOTAL_LIFE,
            ],
            [
                [
                    // level отсутствует
                    'id'           => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
                    'name'         => 'Skeleton',
                    'avatar'       => '/images/avas/monsters/003.png',
                    'damage'       => 15,
                    'attack_speed' => 1.2,
                    'life'         => 80,
                    'total_life'   => 80,
                    'melee'        => true,
                    'class'        => 1,
                ],
                'error' => UnitException::INCORRECT_LEVEL,
            ],
            [
                [
                    // level некорректного типа
                    'id'           => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
                    'name'         => 'Skeleton',
                    'level'        => '3',
                    'avatar'       => '/images/avas/monsters/003.png',
                    'damage'       => 15,
                    'attack_speed' => 1.2,
                    'life'         => 80,
                    'total_life'   => 80,
                    'melee'        => true,
                    'class'        => 1,
                ],
                'error' => UnitException::INCORRECT_LEVEL,
            ],
            [
                [
                    // level < UnitInterface::MIN_LEVEL
                    'id'           => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
                    'name'         => 'Skeleton',
                    'level'        => UnitInterface::MIN_LEVEL - 1,
                    'avatar'       => '/images/avas/monsters/003.png',
                    'damage'       => 15,
                    'attack_speed' => 1.2,
                    'life'         => 80,
                    'total_life'   => 80,
                    'melee'        => true,
                    'class'        => 1,
                ],
                'error' => UnitException::INCORRECT_LEVEL_VALUE . UnitInterface::MIN_LEVEL . '-' . UnitInterface::MAX_LEVEL,
            ],
            [
                [
                    // level > UnitInterface::MAX_LEVEL
                    'id'           => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
                    'name'         => 'Skeleton',
                    'level'        => UnitInterface::MAX_LEVEL + 1,
                    'avatar'       => '/images/avas/monsters/003.png',
                    'damage'       => 15,
                    'attack_speed' => 1.2,
                    'life'         => 80,
                    'total_life'   => 80,
                    'melee'        => true,
                    'class'        => 1,
                ],
                'error' => UnitException::INCORRECT_LEVEL_VALUE . UnitInterface::MIN_LEVEL . '-' . UnitInterface::MAX_LEVEL,
            ],
        ];
    }
}
