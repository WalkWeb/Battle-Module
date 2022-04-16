<?php

declare(strict_types=1);

namespace Tests\Battle\Unit;

use Battle\Unit\Classes\UnitClassFactory;
use Battle\Unit\Race\RaceFactory;
use Battle\Unit\UnitException;
use Battle\Unit\UnitFactory;
use Battle\Unit\UnitInterface;
use Exception;
use Tests\AbstractUnitTest;

class UnitFactoryTest extends AbstractUnitTest
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
        $class = array_key_exists('class', $data) && is_int($data['class']) ? UnitClassFactory::create($data['class']) : null;
        $race = RaceFactory::create($data['race']);

        self::assertEquals($data['name'], $unit->getName());
        self::assertEquals($data['level'], $unit->getLevel());
        self::assertEquals($data['avatar'], $unit->getAvatar());
        self::assertEquals($data['life'], $unit->getTotalLife());
        self::assertEquals($data['life'], $unit->getLife());
        self::assertEquals($data['melee'], $unit->isMelee());
        self::assertEquals($data['command'], $unit->getCommand());
        self::assertEquals($class, $unit->getClass());
        self::assertEquals($race, $unit->getRace());

        self::assertEquals($data['offense']['damage'], $unit->getOffense()->getDamage());
        self::assertEquals($data['offense']['attack_speed'], $unit->getOffense()->getAttackSpeed());
        self::assertEquals($data['offense']['accuracy'], $unit->getOffense()->getAccuracy());
        self::assertEquals($data['offense']['block_ignore'], $unit->getOffense()->getBlockIgnore());
        self::assertEquals(round($data['offense']['damage'] * $data['offense']['attack_speed'], 1), $unit->getDPS());

        self::assertEquals($data['defense']['defense'], $unit->getDefense()->getDefense());
        self::assertEquals($data['defense']['block'], $unit->getDefense()->getBlock());
    }

    /**
     * Тесты на различные варианты невалидных данных для создания юнита
     *
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
     * Тест на замену спецсимволов в имени юнита
     *
     * @throws Exception
     */
    public function testUnitFactoryHtmlspecialchars(): void
    {
        $data = [
            'id'           => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
            'name'         => '<b>Skeleton</b>',
            'level'        => 1,
            'avatar'       => '/images/avas/monsters/003.png',
            'life'         => 80,
            'total_life'   => 80,
            'melee'        => true,
            'class'        => 1,
            'race'         => 8,
            'command'      => 1,
            'offense'    => [
                'damage'       => 15,
                'attack_speed' => 1.2,
                'accuracy'     => 200,
                'block_ignore' => 0,
            ],
            'defense'    => [
                'defense' => 100,
                'block'   => 0,
            ],
        ];

        $unit = UnitFactory::create($data);

        self::assertEquals(htmlspecialchars($data['name']), $unit->getName());
    }

    /**
     * @return array
     */
    public function successDataProvider(): array
    {
        return [
            [
                // attack_speed float
                [
                    'id'         => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
                    'name'       => 'Skeleton',
                    'level'      => 3,
                    'avatar'     => '/images/avas/monsters/003.png',
                    'life'       => 80,
                    'total_life' => 80,
                    'melee'      => true,
                    'class'      => 1,
                    'race'       => 1,
                    'command'    => 1,
                    'offense'    => [
                        'damage'       => 15,
                        'attack_speed' => 1.2,
                        'accuracy'     => 200,
                        'block_ignore' => 0,
                    ],
                    'defense'    => [
                        'defense' => 100,
                        'block'   => 0,
                    ],
                ],
            ],
            [
                // attack_speed int - такой вариант также доступен
                [
                    'id'         => '5aa0d764-e92d-4137-beed-f7f590b08165',
                    'name'       => 'Skeleton',
                    'level'      => 5,
                    'avatar'     => '/images/avas/monsters/003.png',
                    'life'       => 80,
                    'total_life' => 80,
                    'melee'      => true,
                    'class'      => 1,
                    'race'       => 8,
                    'command'    => 1,
                    'offense'    => [
                        'damage'       => 15,
                        'attack_speed' => 1,
                        'accuracy'     => 200,
                        'block_ignore' => 0,
                    ],
                    'defense'    => [
                        'defense' => 100,
                        'block'   => 0,
                    ],
                ],
            ],
            [
                // отсутствует класс
                [
                    'id'         => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
                    'name'       => 'Skeleton',
                    'level'      => 3,
                    'avatar'     => '/images/avas/monsters/003.png',
                    'life'       => 80,
                    'total_life' => 80,
                    'melee'      => true,
                    'race'       => 1,
                    'command'    => 1,
                    'offense'    => [
                        'damage'       => 15,
                        'attack_speed' => 1.2,
                        'accuracy'     => 200,
                        'block_ignore' => 0,
                    ],
                    'defense'    => [
                        'defense' => 100,
                        'block'   => 0,
                    ],
                ],
            ],
            [
                // класс = null
                [
                    'id'         => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
                    'name'       => 'Skeleton',
                    'level'      => 3,
                    'avatar'     => '/images/avas/monsters/003.png',
                    'life'       => 80,
                    'total_life' => 80,
                    'melee'      => true,
                    'class'      => null,
                    'race'       => 1,
                    'command'    => 2,
                    'offense'    => [
                        'damage'       => 15,
                        'attack_speed' => 1.2,
                        'accuracy'     => 200,
                        'block_ignore' => 0,
                    ],
                    'defense'    => [
                        'defense' => 100,
                        'block'   => 0,
                    ],
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
                    'name'       => 'Skeleton',
                    'level'      => 3,
                    'avatar'     => '/images/avas/monsters/003.png',
                    'life'       => 80,
                    'total_life' => 80,
                    'melee'      => true,
                    'class'      => 1,
                    'race'       => 1,
                    'command'    => 1,
                    'offense'    => [
                        'damage'       => 15,
                        'attack_speed' => 1.2,
                        'accuracy'     => 200,
                        'block_ignore' => 0,
                    ],
                    'defense'    => [
                        'defense' => 100,
                        'block'   => 0,
                    ],
                ],
                UnitException::INCORRECT_ID,
            ],
            [
                [
                    // id некорректного типа
                    'id'         => 123123,
                    'name'       => 'Skeleton',
                    'level'      => 3,
                    'avatar'     => '/images/avas/monsters/003.png',
                    'life'       => 80,
                    'total_life' => 80,
                    'melee'      => true,
                    'class'      => 1,
                    'race'       => 1,
                    'command'    => 1,
                    'offense'    => [
                        'damage'       => 15,
                        'attack_speed' => 1.2,
                        'accuracy'     => 200,
                        'block_ignore' => 0,
                    ],
                    'defense'    => [
                        'defense' => 100,
                        'block'   => 0,
                    ],
                ],
                UnitException::INCORRECT_ID,
            ],
            [
                [
                    // id меньше минимальной длины (0)
                    'id'         => '',
                    'name'       => 'Skeleton',
                    'level'      => 3,
                    'avatar'     => '/images/avas/monsters/003.png',
                    'life'       => 80,
                    'total_life' => 80,
                    'melee'      => true,
                    'class'      => 1,
                    'race'       => 1,
                    'command'    => 1,
                    'offense'    => [
                        'damage'       => 15,
                        'attack_speed' => 1.2,
                        'accuracy'     => 200,
                        'block_ignore' => 0,
                    ],
                    'defense'    => [
                        'defense' => 100,
                        'block'   => 0,
                    ],
                ],
                UnitException::INCORRECT_ID_VALUE . UnitInterface::MIN_ID_LENGTH . '-' . UnitInterface::MAX_ID_LENGTH,
            ],
            [
                [
                    // id больше максимальной длины (4)
                    'id'         => 'llllllllllllllllllllllllllllllllllllllll',
                    'name'       => 'Skeleton',
                    'level'      => 3,
                    'avatar'     => '/images/avas/monsters/003.png',
                    'life'       => 80,
                    'total_life' => 80,
                    'melee'      => true,
                    'class'      => 1,
                    'race'       => 1,
                    'command'    => 1,
                    'offense'    => [
                        'damage'       => 15,
                        'attack_speed' => 1.2,
                        'accuracy'     => 200,
                        'block_ignore' => 0,
                    ],
                    'defense'    => [
                        'defense' => 100,
                        'block'   => 0,
                    ],
                ],
                UnitException::INCORRECT_ID_VALUE . UnitInterface::MIN_ID_LENGTH . '-' . UnitInterface::MAX_ID_LENGTH,
            ],
            [
                [
                    // отсутствует name
                    'id'         => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
                    'level'      => 3,
                    'avatar'     => '/images/avas/monsters/003.png',
                    'life'       => 80,
                    'total_life' => 80,
                    'melee'      => true,
                    'class'      => 1,
                    'race'       => 1,
                    'command'    => 1,
                    'offense'    => [
                        'damage'       => 15,
                        'attack_speed' => 1.2,
                        'accuracy'     => 200,
                        'block_ignore' => 0,
                    ],
                    'defense'    => [
                        'defense' => 100,
                        'block'   => 0,
                    ],
                ],
                UnitException::INCORRECT_NAME,
            ],
            [
                [
                    // некорректный name
                    'id'         => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
                    'name'       => 123,
                    'level'      => 3,
                    'avatar'     => '/images/avas/monsters/003.png',
                    'life'       => 80,
                    'total_life' => 80,
                    'melee'      => true,
                    'class'      => 1,
                    'race'       => 1,
                    'command'    => 1,
                    'offense'    => [
                        'damage'       => 15,
                        'attack_speed' => 1.2,
                        'accuracy'     => 200,
                        'block_ignore' => 0,
                    ],
                    'defense'    => [
                        'defense' => 100,
                        'block'   => 0,
                    ],
                ],
                UnitException::INCORRECT_NAME,
            ],
            [
                [
                    // Длина name меньше минимальной длины
                    'id'         => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
                    'name'       => '',
                    'level'      => 3,
                    'avatar'     => '/images/avas/monsters/003.png',
                    'life'       => 80,
                    'total_life' => 80,
                    'melee'      => true,
                    'class'      => 1,
                    'race'       => 1,
                    'command'    => 1,
                    'offense'    => [
                        'damage'       => 15,
                        'attack_speed' => 1.2,
                        'accuracy'     => 200,
                        'block_ignore' => 0,
                    ],
                    'defense'    => [
                        'defense' => 100,
                        'block'   => 0,
                    ],
                ],
                UnitException::INCORRECT_NAME_VALUE . UnitInterface::MIN_NAME_LENGTH . '-' . UnitInterface::MAX_NAME_LENGTH,
            ],
            [
                [
                    // Длина name больше максимальной длины
                    'id'         => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
                    'name'       => 'lllllllllllllllllllll',
                    'level'      => 3,
                    'avatar'     => '/images/avas/monsters/003.png',
                    'life'       => 80,
                    'total_life' => 80,
                    'melee'      => true,
                    'class'      => 1,
                    'race'       => 1,
                    'command'    => 1,
                    'offense'    => [
                        'damage'       => 15,
                        'attack_speed' => 1.2,
                        'accuracy'     => 200,
                        'block_ignore' => 0,
                    ],
                    'defense'    => [
                        'defense' => 100,
                        'block'   => 0,
                    ],
                ],
                UnitException::INCORRECT_NAME_VALUE . UnitInterface::MIN_NAME_LENGTH . '-' . UnitInterface::MAX_NAME_LENGTH,
            ],
            [
                [
                    // отсутствует avatar
                    'id'         => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
                    'name'       => 'Skeleton',
                    'level'      => 3,
                    'life'       => 80,
                    'total_life' => 80,
                    'melee'      => true,
                    'class'      => 1,
                    'race'       => 1,
                    'command'    => 1,
                    'offense'    => [
                        'damage'       => 15,
                        'attack_speed' => 1.2,
                        'accuracy'     => 200,
                        'block_ignore' => 0,
                    ],
                    'defense'    => [
                        'defense' => 100,
                        'block'   => 0,
                    ],
                ],
                UnitException::INCORRECT_AVATAR,
            ],
            [
                [
                    // некорректный avatar
                    'id'         => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
                    'name'       => 'Skeleton',
                    'level'      => 3,
                    'avatar'     => ['ava' => '/images/avas/monsters/003.png'],
                    'life'       => 80,
                    'total_life' => 80,
                    'melee'      => true,
                    'class'      => 1,
                    'race'       => 1,
                    'command'    => 1,
                    'offense'    => [
                        'damage'       => 15,
                        'attack_speed' => 1.2,
                        'accuracy'     => 200,
                        'block_ignore' => 0,
                    ],
                    'defense'    => [
                        'defense' => 100,
                        'block'   => 0,
                    ],
                ],
                UnitException::INCORRECT_AVATAR,
            ],
            [
                [
                    // отсутствует life
                    'id'      => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
                    'name'    => 'Skeleton',
                    'level'   => 3,
                    'avatar'  => '/images/avas/monsters/003.png',
                    'melee'   => true,
                    'class'   => 1,
                    'race'    => 1,
                    'command' => 1,
                    'offense' => [
                        'damage'       => 15,
                        'attack_speed' => 1.2,
                        'accuracy'     => 200,
                        'block_ignore' => 0,
                    ],
                    'defense' => [
                        'defense' => 100,
                        'block'   => 0,
                    ],
                ],
                UnitException::INCORRECT_LIFE,
            ],
            [
                [
                    // некорректный life
                    'id'         => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
                    'name'       => 'Skeleton',
                    'level'      => 3,
                    'avatar'     => '/images/avas/monsters/003.png',
                    'life'       => 80.0,
                    'total_life' => 80,
                    'melee'      => true,
                    'class'      => 1,
                    'race'       => 1,
                    'command'    => 1,
                    'offense'    => [
                        'damage'       => 15,
                        'attack_speed' => 1.2,
                        'accuracy'     => 200,
                        'block_ignore' => 0,
                    ],
                    'defense'    => [
                        'defense' => 100,
                        'block'   => 0,
                    ],
                ],
                UnitException::INCORRECT_LIFE,
            ],
            [
                [
                    // life < UnitInterface::MIN_LIFE
                    'id'         => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
                    'name'       => 'Skeleton',
                    'level'      => 3,
                    'avatar'     => '/images/avas/monsters/003.png',
                    'life'       => UnitInterface::MIN_LIFE - 1,
                    'total_life' => 80,
                    'melee'      => true,
                    'class'      => 1,
                    'race'       => 1,
                    'command'    => 1,
                    'offense'    => [
                        'damage'       => 15,
                        'attack_speed' => 1.2,
                        'accuracy'     => 200,
                        'block_ignore' => 0,
                    ],
                    'defense'    => [
                        'defense' => 100,
                        'block'   => 0,
                    ],
                ],
                UnitException::INCORRECT_LIFE_VALUE . UnitInterface::MIN_LIFE . '-' . UnitInterface::MAX_LIFE,
            ],
            [
                [
                    // life > UnitInterface::MAX_LIFE
                    'id'         => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
                    'name'       => 'Skeleton',
                    'level'      => 3,
                    'avatar'     => '/images/avas/monsters/003.png',
                    'life'       => UnitInterface::MAX_LIFE + 1,
                    'total_life' => 80,
                    'melee'      => true,
                    'class'      => 1,
                    'race'       => 1,
                    'command'    => 1,
                    'offense'    => [
                        'damage'       => 15,
                        'attack_speed' => 1.2,
                        'accuracy'     => 200,
                        'block_ignore' => 0,
                    ],
                    'defense'    => [
                        'defense' => 100,
                        'block'   => 0,
                    ],
                ],
                UnitException::INCORRECT_LIFE_VALUE . UnitInterface::MIN_LIFE . '-' . UnitInterface::MAX_LIFE,
            ],
            [
                [
                    // отсутствует total life
                    'id'      => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
                    'name'    => 'Skeleton',
                    'level'   => 3,
                    'avatar'  => '/images/avas/monsters/003.png',
                    'life'    => 80,
                    'melee'   => true,
                    'class'   => 1,
                    'race'    => 1,
                    'command' => 1,
                    'offense' => [
                        'damage'       => 15,
                        'attack_speed' => 1.2,
                        'accuracy'     => 200,
                        'block_ignore' => 0,
                    ],
                    'defense' => [
                        'defense' => 100,
                        'block'   => 0,
                    ],
                ],
                UnitException::INCORRECT_TOTAL_LIFE,
            ],
            [
                [
                    // некорректный total life
                    'id'         => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
                    'name'       => 'Skeleton',
                    'level'      => 3,
                    'avatar'     => '/images/avas/monsters/003.png',
                    'life'       => 80,
                    'total_life' => 80.0,
                    'melee'      => true,
                    'class'      => 1,
                    'race'       => 1,
                    'command'    => 1,
                    'offense'    => [
                        'damage'       => 15,
                        'attack_speed' => 1.2,
                        'accuracy'     => 200,
                        'block_ignore' => 0,
                    ],
                    'defense'    => [
                        'defense' => 100,
                        'block'   => 0,
                    ],
                ],
                UnitException::INCORRECT_TOTAL_LIFE,
            ],
            [
                [
                    // total life < UnitInterface::MIN_TOTAL_LIFE
                    'id'         => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
                    'name'       => 'Skeleton',
                    'level'      => 3,
                    'avatar'     => '/images/avas/monsters/003.png',
                    'life'       => 0,
                    'total_life' => UnitInterface::MIN_TOTAL_LIFE - 1,
                    'melee'      => true,
                    'class'      => 1,
                    'race'       => 1,
                    'command'    => 1,
                    'offense'    => [
                        'damage'       => 15,
                        'attack_speed' => 1.2,
                        'accuracy'     => 200,
                        'block_ignore' => 0,
                    ],
                    'defense'    => [
                        'defense' => 100,
                        'block'   => 0,
                    ],
                ],
                UnitException::INCORRECT_TOTAL_LIFE_VALUE . UnitInterface::MIN_TOTAL_LIFE . '-' . UnitInterface::MAX_TOTAL_LIFE,
            ],
            [
                [
                    // total life > UnitInterface::MAX_TOTAL_LIFE
                    'id'         => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
                    'name'       => 'Skeleton',
                    'level'      => 3,
                    'avatar'     => '/images/avas/monsters/003.png',
                    'life'       => 0,
                    'total_life' => UnitInterface::MAX_TOTAL_LIFE + 1,
                    'melee'      => true,
                    'class'      => 1,
                    'race'       => 1,
                    'command'    => 1,
                    'offense'    => [
                        'damage'       => 15,
                        'attack_speed' => 1.2,
                        'accuracy'     => 200,
                        'block_ignore' => 0,
                    ],
                    'defense'    => [
                        'defense' => 100,
                        'block'   => 0,
                    ],
                ],
                UnitException::INCORRECT_TOTAL_LIFE_VALUE . UnitInterface::MIN_TOTAL_LIFE . '-' . UnitInterface::MAX_TOTAL_LIFE,
            ],
            [
                [
                    // отсутствует melee
                    'id'         => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
                    'name'       => 'Skeleton',
                    'level'      => 3,
                    'avatar'     => '/images/avas/monsters/003.png',
                    'life'       => 80,
                    'total_life' => 80,
                    'class'      => 1,
                    'race'       => 1,
                    'command'    => 1,
                    'offense'    => [
                        'damage'       => 15,
                        'attack_speed' => 1.2,
                        'accuracy'     => 200,
                        'block_ignore' => 0,
                    ],
                    'defense'    => [
                        'defense' => 100,
                        'block'   => 0,
                    ],
                ],
                UnitException::INCORRECT_MELEE,
            ],
            [
                [
                    // некорректный melee
                    'id'         => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
                    'name'       => 'Skeleton',
                    'level'      => 3,
                    'avatar'     => '/images/avas/monsters/003.png',
                    'life'       => 80,
                    'total_life' => 80,
                    'melee'      => 1,
                    'class'      => 1,
                    'race'       => 1,
                    'command'    => 1,
                    'offense'    => [
                        'damage'       => 15,
                        'attack_speed' => 1.2,
                        'accuracy'     => 200,
                        'block_ignore' => 0,
                    ],
                    'defense'    => [
                        'defense' => 100,
                        'block'   => 0,
                    ],
                ],
                UnitException::INCORRECT_MELEE,
            ],
            [
                [
                    // некорректный class
                    'id'         => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
                    'name'       => 'Skeleton',
                    'level'      => 3,
                    'avatar'     => '/images/avas/monsters/003.png',
                    'life'       => 80,
                    'total_life' => 80,
                    'melee'      => true,
                    'class'      => 'warrior',
                    'race'       => 1,
                    'command'    => 1,
                    'offense'    => [
                        'damage'       => 15,
                        'attack_speed' => 1.2,
                        'accuracy'     => 200,
                        'block_ignore' => 0,
                    ],
                    'defense'    => [
                        'defense' => 100,
                        'block'   => 0,
                    ],
                ],
                UnitException::INCORRECT_CLASS,
            ],
            [
                [
                    // life большие total life
                    'id'         => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
                    'name'       => 'Skeleton',
                    'level'      => 3,
                    'avatar'     => '/images/avas/monsters/003.png',
                    'life'       => 90,
                    'total_life' => 80,
                    'melee'      => true,
                    'class'      => 1,
                    'race'       => 1,
                    'command'    => 1,
                    'offense'    => [
                        'damage'       => 15,
                        'attack_speed' => 1.2,
                        'accuracy'     => 200,
                        'block_ignore' => 0,
                    ],
                    'defense'    => [
                        'defense' => 100,
                        'block'   => 0,
                    ],
                ],
                UnitException::LIFE_MORE_TOTAL_LIFE,
            ],
            [
                [
                    // level отсутствует
                    'id'         => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
                    'name'       => 'Skeleton',
                    'avatar'     => '/images/avas/monsters/003.png',
                    'life'       => 80,
                    'total_life' => 80,
                    'melee'      => true,
                    'class'      => 1,
                    'race'       => 1,
                    'command'    => 1,
                    'offense'    => [
                        'damage'       => 15,
                        'attack_speed' => 1.2,
                        'accuracy'     => 200,
                        'block_ignore' => 0,
                    ],
                    'defense'    => [
                        'defense' => 100,
                        'block'   => 0,
                    ],
                ],
                UnitException::INCORRECT_LEVEL,
            ],
            [
                [
                    // level некорректного типа
                    'id'         => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
                    'name'       => 'Skeleton',
                    'level'      => '3',
                    'avatar'     => '/images/avas/monsters/003.png',
                    'life'       => 80,
                    'total_life' => 80,
                    'melee'      => true,
                    'class'      => 1,
                    'race'       => 1,
                    'command'    => 1,
                    'offense'    => [
                        'damage'       => 15,
                        'attack_speed' => 1.2,
                        'accuracy'     => 200,
                        'block_ignore' => 0,
                    ],
                    'defense'    => [
                        'defense' => 100,
                        'block'   => 0,
                    ],
                ],
                UnitException::INCORRECT_LEVEL,
            ],
            [
                [
                    // level < UnitInterface::MIN_LEVEL
                    'id'         => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
                    'name'       => 'Skeleton',
                    'level'      => UnitInterface::MIN_LEVEL - 1,
                    'avatar'     => '/images/avas/monsters/003.png',
                    'life'       => 80,
                    'total_life' => 80,
                    'melee'      => true,
                    'class'      => 1,
                    'race'       => 1,
                    'command'    => 1,
                    'offense'    => [
                        'damage'       => 15,
                        'attack_speed' => 1.2,
                        'accuracy'     => 200,
                        'block_ignore' => 0,
                    ],
                    'defense'    => [
                        'defense' => 100,
                        'block'   => 0,
                    ],
                ],
                UnitException::INCORRECT_LEVEL_VALUE . UnitInterface::MIN_LEVEL . '-' . UnitInterface::MAX_LEVEL,
            ],
            [
                [
                    // level > UnitInterface::MAX_LEVEL
                    'id'         => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
                    'name'       => 'Skeleton',
                    'level'      => UnitInterface::MAX_LEVEL + 1,
                    'avatar'     => '/images/avas/monsters/003.png',
                    'life'       => 80,
                    'total_life' => 80,
                    'melee'      => true,
                    'class'      => 1,
                    'race'       => 1,
                    'command'    => 1,
                    'offense'    => [
                        'damage'       => 15,
                        'attack_speed' => 1.2,
                        'accuracy'     => 200,
                        'block_ignore' => 0,
                    ],
                    'defense'    => [
                        'defense' => 100,
                        'block'   => 0,
                    ],
                ],
                UnitException::INCORRECT_LEVEL_VALUE . UnitInterface::MIN_LEVEL . '-' . UnitInterface::MAX_LEVEL,
            ],
            [
                [
                    // отсутствует race
                    'id'         => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
                    'name'       => 'Skeleton',
                    'level'      => 3,
                    'avatar'     => '/images/avas/monsters/003.png',
                    'life'       => 80,
                    'total_life' => 80,
                    'melee'      => true,
                    'class'      => 1,
                    'command'    => 1,
                    'offense'    => [
                        'damage'       => 15,
                        'attack_speed' => 1.2,
                        'accuracy'     => 200,
                        'block_ignore' => 0,
                    ],
                    'defense'    => [
                        'defense' => 100,
                        'block'   => 0,
                    ],
                ],
                UnitException::INCORRECT_RACE,
            ],
            [
                [
                    // race некорректного типа
                    'id'         => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
                    'name'       => 'Skeleton',
                    'level'      => 3,
                    'avatar'     => '/images/avas/monsters/003.png',
                    'life'       => 80,
                    'total_life' => 80,
                    'melee'      => true,
                    'class'      => 1,
                    'race'       => 'human',
                    'command'    => 1,
                    'offense'    => [
                        'damage'       => 15,
                        'attack_speed' => 1.2,
                        'accuracy'     => 200,
                        'block_ignore' => 0,
                    ],
                    'defense'    => [
                        'defense' => 100,
                        'block'   => 0,
                    ],
                ],
                UnitException::INCORRECT_RACE,
            ],
            [
                [
                    // отсутствует command
                    'id'         => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
                    'name'       => 'Skeleton',
                    'level'      => 3,
                    'avatar'     => '/images/avas/monsters/003.png',
                    'life'       => 80,
                    'total_life' => 80,
                    'melee'      => true,
                    'class'      => 1,
                    'race'       => 1,
                    'offense'    => [
                        'damage'       => 15,
                        'attack_speed' => 1.2,
                        'accuracy'     => 200,
                        'block_ignore' => 0,
                    ],
                    'defense'    => [
                        'defense' => 100,
                        'block'   => 0,
                    ],
                ],
                UnitException::INCORRECT_COMMAND,
            ],
            [
                [
                    // command некорректного типа
                    'id'         => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
                    'name'       => 'Skeleton',
                    'level'      => 3,
                    'avatar'     => '/images/avas/monsters/003.png',
                    'life'       => 80,
                    'total_life' => 80,
                    'melee'      => true,
                    'class'      => 1,
                    'race'       => 1,
                    'command'    => 'left',
                    'offense'    => [
                        'damage'       => 15,
                        'attack_speed' => 1.2,
                        'accuracy'     => 200,
                        'block_ignore' => 0,
                    ],
                    'defense'    => [
                        'defense' => 100,
                        'block'   => 0,
                    ],
                ],
                UnitException::INCORRECT_COMMAND,
            ],
            [
                [
                    // command != 1 && != 2
                    'id'         => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
                    'name'       => 'Skeleton',
                    'level'      => 3,
                    'avatar'     => '/images/avas/monsters/003.png',
                    'life'       => 80,
                    'total_life' => 80,
                    'melee'      => true,
                    'class'      => 1,
                    'race'       => 1,
                    'command'    => 3,
                    'offense'    => [
                        'damage'       => 15,
                        'attack_speed' => 1.2,
                        'accuracy'     => 200,
                        'block_ignore' => 0,
                    ],
                    'defense'    => [
                        'defense' => 100,
                        'block'   => 0,
                    ],
                ],
                UnitException::INCORRECT_COMMAND,
            ],
            [
                // Отсутствует offense
                [
                    'id'         => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
                    'name'       => 'Skeleton',
                    'level'      => 3,
                    'avatar'     => '/images/avas/monsters/003.png',
                    'life'       => 80,
                    'total_life' => 80,
                    'melee'      => true,
                    'class'      => 1,
                    'race'       => 1,
                    'command'    => 1,
                    'defense'    => [
                        'defense' => 100,
                        'block'   => 0,
                    ],
                ],
                UnitException::INCORRECT_OFFENSE,
            ],
            [
                // offense некорректного типа
                [
                    'id'         => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
                    'name'       => 'Skeleton',
                    'level'      => 3,
                    'avatar'     => '/images/avas/monsters/003.png',
                    'life'       => 80,
                    'total_life' => 80,
                    'melee'      => true,
                    'class'      => 1,
                    'race'       => 1,
                    'command'    => 1,
                    'offense'    => true,
                    'defense'    => [
                        'defense' => 100,
                        'block'   => 0,
                    ],
                ],
                UnitException::INCORRECT_OFFENSE,
            ],
            [
                // Отсутствует defense
                [
                    'id'         => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
                    'name'       => 'Skeleton',
                    'level'      => 3,
                    'avatar'     => '/images/avas/monsters/003.png',
                    'life'       => 80,
                    'total_life' => 80,
                    'melee'      => true,
                    'class'      => 1,
                    'race'       => 1,
                    'command'    => 1,
                    'offense'    => [
                        'damage'       => 15,
                        'attack_speed' => 1.2,
                        'accuracy'     => 200,
                        'block_ignore' => 0,
                    ],
                ],
                UnitException::INCORRECT_DEFENSE,
            ],
            [
                // defense некорректного типа
                [
                    'id'         => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
                    'name'       => 'Skeleton',
                    'level'      => 3,
                    'avatar'     => '/images/avas/monsters/003.png',
                    'life'       => 80,
                    'total_life' => 80,
                    'melee'      => true,
                    'class'      => 1,
                    'race'       => 1,
                    'command'    => 1,
                    'offense'    => [
                        'damage'       => 15,
                        'attack_speed' => 1.2,
                        'accuracy'     => 200,
                        'block_ignore' => 0,
                    ],
                    'defense'    => 100,
                ],
                UnitException::INCORRECT_DEFENSE,
            ],
        ];
    }
}
