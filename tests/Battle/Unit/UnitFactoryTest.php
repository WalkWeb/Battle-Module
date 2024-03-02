<?php

declare(strict_types=1);

namespace Tests\Battle\Unit;

use Battle\Container\ContainerInterface;
use Battle\Unit\Classes\UnitClassInterface;
use Battle\Unit\Defense\Defense;
use Battle\Unit\Defense\DefenseInterface;
use Battle\Unit\Race\RaceInterface;
use Battle\Unit\UnitException;
use Battle\Unit\UnitFactory;
use Battle\Unit\UnitInterface;
use Battle\Weapon\Type\WeaponTypeInterface;
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
    public function testUnitFactoryCreateSuccess(array $data): void
    {
        $unit = UnitFactory::create($data);
        $class = $this->createClass($data, $this->container);
        $race = $this->createRace($data['race'], $this->container);

        self::assertEquals($data['name'], $unit->getName());
        self::assertEquals($data['level'], $unit->getLevel());
        self::assertEquals($data['avatar'], $unit->getAvatar());
        self::assertEquals($data['life'], $unit->getTotalLife());
        self::assertEquals($data['life'], $unit->getLife());
        self::assertEquals($data['melee'], $unit->isMelee());
        self::assertEquals($data['command'], $unit->getCommand());
        self::assertEquals((int)(UnitInterface::BASE_CUNNING * ((100 + $unit->getCunningMultiplier()) / 100)), $unit->getCunning());
        self::assertEquals($data['add_concentration_multiplier'], $unit->getAddConcentrationMultiplier());
        self::assertEquals($data['cunning_multiplier'], $unit->getCunningMultiplier());
        self::assertEquals($data['add_rage_multiplier'], $unit->getAddRageMultiplier());
        self::assertEquals($class, $unit->getClass());
        self::assertEquals($race, $unit->getRace());

        self::assertEquals(
            $data['offense']['physical_damage'] + $data['offense']['fire_damage'] + $data['offense']['water_damage'] + $data['offense']['air_damage'] + $data['offense']['earth_damage'] + $data['offense']['life_damage'] + $data['offense']['death_damage'],
            $unit->getOffense()->getDamage($this->getDefense())
        );

        self::assertEquals($data['offense']['physical_damage'], $unit->getOffense()->getPhysicalDamage());
        self::assertEquals($data['offense']['fire_damage'], $unit->getOffense()->getFireDamage());
        self::assertEquals($data['offense']['water_damage'], $unit->getOffense()->getWaterDamage());
        self::assertEquals($data['offense']['air_damage'], $unit->getOffense()->getAirDamage());
        self::assertEquals($data['offense']['earth_damage'], $unit->getOffense()->getEarthDamage());
        self::assertEquals($data['offense']['life_damage'], $unit->getOffense()->getLifeDamage());
        self::assertEquals($data['offense']['death_damage'], $unit->getOffense()->getDeathDamage());

        self::assertEquals($data['offense']['attack_speed'], $unit->getOffense()->getAttackSpeed());
        self::assertEquals($data['offense']['accuracy'], $unit->getOffense()->getAccuracy());
        self::assertEquals($data['offense']['block_ignoring'], $unit->getOffense()->getBlockIgnoring());
        self::assertEquals($data['offense']['critical_chance'], $unit->getOffense()->getCriticalChance());
        self::assertEquals($data['offense']['critical_multiplier'], $unit->getOffense()->getCriticalMultiplier());
        self::assertEquals($data['offense']['vampirism'], $unit->getOffense()->getVampirism());

        self::assertEquals($data['defense']['physical_resist'], $unit->getDefense()->getPhysicalResist());
        self::assertEquals($data['defense']['fire_resist'], $unit->getDefense()->getFireResist());
        self::assertEquals($data['defense']['water_resist'], $unit->getDefense()->getWaterResist());
        self::assertEquals($data['defense']['air_resist'], $unit->getDefense()->getAirResist());
        self::assertEquals($data['defense']['earth_resist'], $unit->getDefense()->getEarthResist());
        self::assertEquals($data['defense']['life_resist'], $unit->getDefense()->getLifeResist());
        self::assertEquals($data['defense']['death_resist'], $unit->getDefense()->getDeathResist());

        self::assertEquals($data['defense']['defense'], $unit->getDefense()->getDefense());
        self::assertEquals($data['defense']['magic_defense'], $unit->getDefense()->getMagicDefense());
        self::assertEquals($data['defense']['block'], $unit->getDefense()->getBlock());
        self::assertEquals($data['defense']['magic_block'], $unit->getDefense()->getMagicBlock());
        self::assertEquals($data['defense']['mental_barrier'], $unit->getDefense()->getMentalBarrier());

        // Проверка актуальна только для одного кейса CustomAbility
        if (array_key_exists('class', $data) && $data['class'] === null && $data['abilities'] !== []) {
            self::assertSameSize($data['abilities'], $unit->getAbilities());

            foreach ($unit->getAbilities() as $i => $ability) {
                self::assertEquals($data['abilities'][$i]['name'], $ability->getName());
            }
        }
    }

    /**
     * Тесты на различные варианты невалидных данных для создания юнита
     *
     * @dataProvider failDataProvider
     * @param array $data
     * @param string $error
     * @throws Exception
     */
    public function testUnitFactoryCreateFail(array $data, string $error): void
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
            'id'                           => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
            'name'                         => '<b>Skeleton</b>',
            'level'                        => 1,
            'avatar'                       => '/images/avas/monsters/003.png',
            'life'                         => 80,
            'total_life'                   => 80,
            'mana'                         => 50,
            'total_mana'                   => 50,
            'melee'                        => true,
            'class'                        => 1,
            'race'                         => 8,
            'command'                      => 1,
            'add_concentration_multiplier' => 0,
            'cunning_multiplier'           => 0,
            'add_rage_multiplier'          => 0,
            'offense'                      => self::getDefaultOffenseData(),
            'defense'                      => self::getDefaultDefenseData(),
            'abilities'                    => [],
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
                // attack_speed и cast_speed float
                [
                    'id'                           => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
                    'name'                         => 'Skeleton',
                    'level'                        => 3,
                    'avatar'                       => '/images/avas/monsters/003.png',
                    'life'                         => 80,
                    'total_life'                   => 80,
                    'mana'                         => 50,
                    'total_mana'                   => 50,
                    'melee'                        => true,
                    'class'                        => 1,
                    'race'                         => 1,
                    'command'                      => 1,
                    'add_concentration_multiplier' => 0,
                    'cunning_multiplier'           => 0,
                    'add_rage_multiplier'          => 0,
                    'offense'                      => self::getDefaultOffenseData(),
                    'defense'                      => self::getDefaultDefenseData(),
                    'abilities'                    => [],
                ],
            ],
            [
                // attack_speed и cast_speed int - такой вариант также доступен
                [
                    'id'                           => '5aa0d764-e92d-4137-beed-f7f590b08165',
                    'name'                         => 'Skeleton',
                    'level'                        => 5,
                    'avatar'                       => '/images/avas/monsters/003.png',
                    'life'                         => 80,
                    'total_life'                   => 80,
                    'mana'                         => 50,
                    'total_mana'                   => 50,
                    'melee'                        => true,
                    'class'                        => 1,
                    'race'                         => 8,
                    'command'                      => 1,
                    'add_concentration_multiplier' => 0,
                    'cunning_multiplier'           => 0,
                    'add_rage_multiplier'          => 0,
                    'offense'                      => self::getDefaultOffenseData(),
                    'defense'                      => self::getDefaultDefenseData(),
                    'abilities'                    => [],
                ],
            ],
            [
                // отсутствует класс
                [
                    'id'                           => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
                    'name'                         => 'Skeleton',
                    'level'                        => 3,
                    'avatar'                       => '/images/avas/monsters/003.png',
                    'life'                         => 80,
                    'total_life'                   => 80,
                    'mana'                         => 50,
                    'total_mana'                   => 50,
                    'melee'                        => true,
                    'race'                         => 1,
                    'command'                      => 1,
                    'add_concentration_multiplier' => 0,
                    'cunning_multiplier'           => 0,
                    'add_rage_multiplier'          => 0,
                    'offense'                      => self::getDefaultOffenseData(),
                    'defense'                      => self::getDefaultDefenseData(),
                    'abilities'                    => [],
                ],
            ],
            [
                // класс = null
                [
                    'id'                           => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
                    'name'                         => 'Skeleton',
                    'level'                        => 3,
                    'avatar'                       => '/images/avas/monsters/003.png',
                    'life'                         => 80,
                    'total_life'                   => 80,
                    'mana'                         => 50,
                    'total_mana'                   => 50,
                    'melee'                        => true,
                    'class'                        => null,
                    'race'                         => 1,
                    'command'                      => 2,
                    'add_concentration_multiplier' => 0,
                    'cunning_multiplier'           => 0,
                    'add_rage_multiplier'          => 0,
                    'offense'                      => self::getDefaultOffenseData(),
                    'defense'                      => self::getDefaultDefenseData(),
                    'abilities'                    => [],
                ],
            ],
            [
                // класс = null + переданы способности напрямую
                [
                    'id'                           => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
                    'name'                         => 'CustomAbility',
                    'level'                        => 3,
                    'avatar'                       => '/images/avas/monsters/003.png',
                    'life'                         => 80,
                    'total_life'                   => 80,
                    'mana'                         => 50,
                    'total_mana'                   => 50,
                    'melee'                        => true,
                    'class'                        => null,
                    'race'                         => 2,
                    'command'                      => 2,
                    'add_concentration_multiplier' => 0,
                    'cunning_multiplier'           => 0,
                    'add_rage_multiplier'          => 0,
                    'offense'                      => self::getDefaultOffenseData(),
                    'defense'                      => self::getDefaultDefenseData(),
                    'abilities'                    => [
                        [
                            'name'  => 'Heavy Strike',
                            'level' => 1,
                        ],
                        [
                            'name'  => 'Blessed Shield',
                            'level' => 1,
                        ],
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
                    // 0. отсутствует id
                    'name'                         => 'Skeleton',
                    'level'                        => 3,
                    'avatar'                       => '/images/avas/monsters/003.png',
                    'life'                         => 80,
                    'total_life'                   => 80,
                    'mana'                         => 50,
                    'total_mana'                   => 50,
                    'melee'                        => true,
                    'class'                        => 1,
                    'race'                         => 1,
                    'command'                      => 1,
                    'add_concentration_multiplier' => 0,
                    'cunning_multiplier'           => 0,
                    'add_rage_multiplier'          => 0,
                    'offense'                      => self::getDefaultOffenseData(),
                    'defense'                      => self::getDefaultDefenseData(),
                    'abilities'                    => [],
                ],
                UnitException::INCORRECT_ID,
            ],
            [
                [
                    // 1. id некорректного типа
                    'id'                           => 123123,
                    'name'                         => 'Skeleton',
                    'level'                        => 3,
                    'avatar'                       => '/images/avas/monsters/003.png',
                    'life'                         => 80,
                    'total_life'                   => 80,
                    'mana'                         => 50,
                    'total_mana'                   => 50,
                    'melee'                        => true,
                    'class'                        => 1,
                    'race'                         => 1,
                    'command'                      => 1,
                    'add_concentration_multiplier' => 0,
                    'cunning_multiplier'           => 0,
                    'add_rage_multiplier'          => 0,
                    'offense'                      => self::getDefaultOffenseData(),
                    'defense'                      => self::getDefaultDefenseData(),
                    'abilities'                    => [],
                ],
                UnitException::INCORRECT_ID,
            ],
            [
                [
                    // 2. id меньше минимальной длины (0)
                    'id'                           => '',
                    'name'                         => 'Skeleton',
                    'level'                        => 3,
                    'avatar'                       => '/images/avas/monsters/003.png',
                    'life'                         => 80,
                    'total_life'                   => 80,
                    'mana'                         => 50,
                    'total_mana'                   => 50,
                    'melee'                        => true,
                    'class'                        => 1,
                    'race'                         => 1,
                    'command'                      => 1,
                    'add_concentration_multiplier' => 0,
                    'cunning_multiplier'           => 0,
                    'add_rage_multiplier'          => 0,
                    'offense'                      => self::getDefaultOffenseData(),
                    'defense'                      => self::getDefaultDefenseData(),
                    'abilities'                    => [],
                ],
                UnitException::INCORRECT_ID_VALUE . UnitInterface::MIN_ID_LENGTH . '-' . UnitInterface::MAX_ID_LENGTH,
            ],
            [
                [
                    // 3. id больше максимальной длины (4)
                    'id'                           => 'llllllllllllllllllllllllllllllllllllllll',
                    'name'                         => 'Skeleton',
                    'level'                        => 3,
                    'avatar'                       => '/images/avas/monsters/003.png',
                    'life'                         => 80,
                    'total_life'                   => 80,
                    'mana'                         => 50,
                    'total_mana'                   => 50,
                    'melee'                        => true,
                    'class'                        => 1,
                    'race'                         => 1,
                    'command'                      => 1,
                    'add_concentration_multiplier' => 0,
                    'cunning_multiplier'           => 0,
                    'add_rage_multiplier'          => 0,
                    'offense'                      => self::getDefaultOffenseData(),
                    'defense'                      => self::getDefaultDefenseData(),
                    'abilities'                    => [],
                ],
                UnitException::INCORRECT_ID_VALUE . UnitInterface::MIN_ID_LENGTH . '-' . UnitInterface::MAX_ID_LENGTH,
            ],
            [
                [
                    // 4. отсутствует name
                    'id'                           => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
                    'level'                        => 3,
                    'avatar'                       => '/images/avas/monsters/003.png',
                    'life'                         => 80,
                    'total_life'                   => 80,
                    'mana'                         => 50,
                    'total_mana'                   => 50,
                    'melee'                        => true,
                    'class'                        => 1,
                    'race'                         => 1,
                    'command'                      => 1,
                    'add_concentration_multiplier' => 0,
                    'cunning_multiplier'           => 0,
                    'add_rage_multiplier'          => 0,
                    'offense'                      => self::getDefaultOffenseData(),
                    'defense'                      => self::getDefaultDefenseData(),
                    'abilities'                    => [],
                ],
                UnitException::INCORRECT_NAME,
            ],
            [
                [
                    // 5. некорректный name
                    'id'                           => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
                    'name'                         => 123,
                    'level'                        => 3,
                    'avatar'                       => '/images/avas/monsters/003.png',
                    'life'                         => 80,
                    'total_life'                   => 80,
                    'mana'                         => 50,
                    'total_mana'                   => 50,
                    'melee'                        => true,
                    'class'                        => 1,
                    'race'                         => 1,
                    'command'                      => 1,
                    'add_concentration_multiplier' => 0,
                    'cunning_multiplier'           => 0,
                    'add_rage_multiplier'          => 0,
                    'offense'                      => self::getDefaultOffenseData(),
                    'defense'                      => self::getDefaultDefenseData(),
                    'abilities'                    => [],
                ],
                UnitException::INCORRECT_NAME,
            ],
            [
                [
                    // 6. Длина name меньше минимальной длины
                    'id'                           => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
                    'name'                         => '',
                    'level'                        => 3,
                    'avatar'                       => '/images/avas/monsters/003.png',
                    'life'                         => 80,
                    'total_life'                   => 80,
                    'mana'                         => 50,
                    'total_mana'                   => 50,
                    'melee'                        => true,
                    'class'                        => 1,
                    'race'                         => 1,
                    'command'                      => 1,
                    'add_concentration_multiplier' => 0,
                    'cunning_multiplier'           => 0,
                    'add_rage_multiplier'          => 0,
                    'offense'                      => self::getDefaultOffenseData(),
                    'defense'                      => self::getDefaultDefenseData(),
                    'abilities'                    => [],
                ],
                UnitException::INCORRECT_NAME_VALUE . UnitInterface::MIN_NAME_LENGTH . '-' . UnitInterface::MAX_NAME_LENGTH,
            ],
            [
                [
                    // 7. Длина name больше максимальной длины
                    'id'                           => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
                    'name'                         => 'lllllllllllllllllllll',
                    'level'                        => 3,
                    'avatar'                       => '/images/avas/monsters/003.png',
                    'life'                         => 80,
                    'total_life'                   => 80,
                    'mana'                         => 50,
                    'total_mana'                   => 50,
                    'melee'                        => true,
                    'class'                        => 1,
                    'race'                         => 1,
                    'command'                      => 1,
                    'add_concentration_multiplier' => 0,
                    'cunning_multiplier'           => 0,
                    'add_rage_multiplier'          => 0,
                    'offense'                      => self::getDefaultOffenseData(),
                    'defense'                      => self::getDefaultDefenseData(),
                    'abilities'                    => [],
                ],
                UnitException::INCORRECT_NAME_VALUE . UnitInterface::MIN_NAME_LENGTH . '-' . UnitInterface::MAX_NAME_LENGTH,
            ],
            [
                [
                    // 8. отсутствует avatar
                    'id'                           => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
                    'name'                         => 'Skeleton',
                    'level'                        => 3,
                    'life'                         => 80,
                    'total_life'                   => 80,
                    'mana'                         => 50,
                    'total_mana'                   => 50,
                    'melee'                        => true,
                    'class'                        => 1,
                    'race'                         => 1,
                    'command'                      => 1,
                    'add_concentration_multiplier' => 0,
                    'cunning_multiplier'           => 0,
                    'add_rage_multiplier'          => 0,
                    'offense'                      => self::getDefaultOffenseData(),
                    'defense'                      => self::getDefaultDefenseData(),
                    'abilities'                    => [],
                ],
                UnitException::INCORRECT_AVATAR,
            ],
            [
                [
                    // 9. некорректный avatar
                    'id'                           => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
                    'name'                         => 'Skeleton',
                    'level'                        => 3,
                    'avatar'                       => ['ava' => '/images/avas/monsters/003.png'],
                    'life'                         => 80,
                    'total_life'                   => 80,
                    'mana'                         => 50,
                    'total_mana'                   => 50,
                    'melee'                        => true,
                    'class'                        => 1,
                    'race'                         => 1,
                    'command'                      => 1,
                    'add_concentration_multiplier' => 0,
                    'cunning_multiplier'           => 0,
                    'add_rage_multiplier'          => 0,
                    'offense'                      => self::getDefaultOffenseData(),
                    'defense'                      => self::getDefaultDefenseData(),
                    'abilities'                    => [],
                ],
                UnitException::INCORRECT_AVATAR,
            ],
            [
                [
                    // 10. отсутствует life
                    'id'                           => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
                    'name'                         => 'Skeleton',
                    'level'                        => 3,
                    'avatar'                       => '/images/avas/monsters/003.png',
                    'total_life'                   => 150,
                    'mana'                         => 50,
                    'total_mana'                   => 50,
                    'melee'                        => true,
                    'class'                        => 1,
                    'race'                         => 1,
                    'command'                      => 1,
                    'add_concentration_multiplier' => 0,
                    'cunning_multiplier'           => 0,
                    'add_rage_multiplier'          => 0,
                    'offense'                      => self::getDefaultOffenseData(),
                    'defense'                      => self::getDefaultDefenseData(),
                    'abilities'                    => [],
                ],
                UnitException::INCORRECT_LIFE,
            ],
            [
                [
                    // 11. некорректный life
                    'id'                           => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
                    'name'                         => 'Skeleton',
                    'level'                        => 3,
                    'avatar'                       => '/images/avas/monsters/003.png',
                    'life'                         => 80.0,
                    'total_life'                   => 80,
                    'mana'                         => 50,
                    'total_mana'                   => 50,
                    'melee'                        => true,
                    'class'                        => 1,
                    'race'                         => 1,
                    'command'                      => 1,
                    'add_concentration_multiplier' => 0,
                    'cunning_multiplier'           => 0,
                    'add_rage_multiplier'          => 0,
                    'offense'                      => self::getDefaultOffenseData(),
                    'defense'                      => self::getDefaultDefenseData(),
                    'abilities'                    => [],
                ],
                UnitException::INCORRECT_LIFE,
            ],
            [
                [
                    // 12. life < UnitInterface::MIN_LIFE
                    'id'                           => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
                    'name'                         => 'Skeleton',
                    'level'                        => 3,
                    'avatar'                       => '/images/avas/monsters/003.png',
                    'life'                         => UnitInterface::MIN_LIFE - 1,
                    'total_life'                   => 80,
                    'mana'                         => 50,
                    'total_mana'                   => 50,
                    'melee'                        => true,
                    'class'                        => 1,
                    'race'                         => 1,
                    'command'                      => 1,
                    'add_concentration_multiplier' => 0,
                    'cunning_multiplier'           => 0,
                    'add_rage_multiplier'          => 0,
                    'offense'                      => self::getDefaultOffenseData(),
                    'defense'                      => self::getDefaultDefenseData(),
                    'abilities'                    => [],
                ],
                UnitException::INCORRECT_LIFE_VALUE . UnitInterface::MIN_LIFE . '-' . UnitInterface::MAX_LIFE,
            ],
            [
                [
                    // 13. life > UnitInterface::MAX_LIFE
                    'id'                           => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
                    'name'                         => 'Skeleton',
                    'level'                        => 3,
                    'avatar'                       => '/images/avas/monsters/003.png',
                    'life'                         => UnitInterface::MAX_LIFE + 1,
                    'total_life'                   => 80,
                    'mana'                         => 50,
                    'total_mana'                   => 50,
                    'melee'                        => true,
                    'class'                        => 1,
                    'race'                         => 1,
                    'command'                      => 1,
                    'add_concentration_multiplier' => 0,
                    'cunning_multiplier'           => 0,
                    'add_rage_multiplier'          => 0,
                    'offense'                      => self::getDefaultOffenseData(),
                    'defense'                      => self::getDefaultDefenseData(),
                    'abilities'                    => [],
                ],
                UnitException::INCORRECT_LIFE_VALUE . UnitInterface::MIN_LIFE . '-' . UnitInterface::MAX_LIFE,
            ],
            [
                [
                    // 14. отсутствует total life
                    'id'                           => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
                    'name'                         => 'Skeleton',
                    'level'                        => 3,
                    'avatar'                       => '/images/avas/monsters/003.png',
                    'life'                         => 80,
                    'mana'                         => 50,
                    'total_mana'                   => 50,
                    'melee'                        => true,
                    'class'                        => 1,
                    'race'                         => 1,
                    'command'                      => 1,
                    'add_concentration_multiplier' => 0,
                    'cunning_multiplier'           => 0,
                    'add_rage_multiplier'          => 0,
                    'offense'                      => self::getDefaultOffenseData(),
                    'defense'                      => self::getDefaultDefenseData(),
                    'abilities'                    => [],
                ],
                UnitException::INCORRECT_TOTAL_LIFE,
            ],
            [
                [
                    // 15. некорректный total life
                    'id'                           => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
                    'name'                         => 'Skeleton',
                    'level'                        => 3,
                    'avatar'                       => '/images/avas/monsters/003.png',
                    'life'                         => 80,
                    'total_life'                   => 80.0,
                    'mana'                         => 50,
                    'total_mana'                   => 50,
                    'melee'                        => true,
                    'class'                        => 1,
                    'race'                         => 1,
                    'command'                      => 1,
                    'add_concentration_multiplier' => 0,
                    'cunning_multiplier'           => 0,
                    'add_rage_multiplier'          => 0,
                    'offense'                      => self::getDefaultOffenseData(),
                    'defense'                      => self::getDefaultDefenseData(),
                    'abilities'                    => [],
                ],
                UnitException::INCORRECT_TOTAL_LIFE,
            ],
            [
                [
                    // 16. total life < UnitInterface::MIN_TOTAL_LIFE
                    'id'                           => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
                    'name'                         => 'Skeleton',
                    'level'                        => 3,
                    'avatar'                       => '/images/avas/monsters/003.png',
                    'life'                         => 0,
                    'total_life'                   => UnitInterface::MIN_TOTAL_LIFE - 1,
                    'mana'                         => 50,
                    'total_mana'                   => 50,
                    'melee'                        => true,
                    'class'                        => 1,
                    'race'                         => 1,
                    'command'                      => 1,
                    'add_concentration_multiplier' => 0,
                    'cunning_multiplier'           => 0,
                    'add_rage_multiplier'          => 0,
                    'offense'                      => self::getDefaultOffenseData(),
                    'defense'                      => self::getDefaultDefenseData(),
                    'abilities'                    => [],
                ],
                UnitException::INCORRECT_TOTAL_LIFE_VALUE . UnitInterface::MIN_TOTAL_LIFE . '-' . UnitInterface::MAX_TOTAL_LIFE,
            ],
            [
                [
                    // 17. total life > UnitInterface::MAX_TOTAL_LIFE
                    'id'                           => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
                    'name'                         => 'Skeleton',
                    'level'                        => 3,
                    'avatar'                       => '/images/avas/monsters/003.png',
                    'life'                         => 0,
                    'total_life'                   => UnitInterface::MAX_TOTAL_LIFE + 1,
                    'mana'                         => 50,
                    'total_mana'                   => 50,
                    'melee'                        => true,
                    'class'                        => 1,
                    'race'                         => 1,
                    'command'                      => 1,
                    'add_concentration_multiplier' => 0,
                    'cunning_multiplier'           => 0,
                    'add_rage_multiplier'          => 0,
                    'offense'                      => self::getDefaultOffenseData(),
                    'defense'                      => self::getDefaultDefenseData(),
                    'abilities'                    => [],
                ],
                UnitException::INCORRECT_TOTAL_LIFE_VALUE . UnitInterface::MIN_TOTAL_LIFE . '-' . UnitInterface::MAX_TOTAL_LIFE,
            ],
            [
                [
                    // 18. отсутствует melee
                    'id'                           => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
                    'name'                         => 'Skeleton',
                    'level'                        => 3,
                    'avatar'                       => '/images/avas/monsters/003.png',
                    'life'                         => 80,
                    'total_life'                   => 80,
                    'mana'                         => 50,
                    'total_mana'                   => 50,
                    'class'                        => 1,
                    'race'                         => 1,
                    'command'                      => 1,
                    'add_concentration_multiplier' => 0,
                    'cunning_multiplier'           => 0,
                    'add_rage_multiplier'          => 0,
                    'offense'                      => self::getDefaultOffenseData(),
                    'defense'                      => self::getDefaultDefenseData(),
                    'abilities'                    => [],
                ],
                UnitException::INCORRECT_MELEE,
            ],
            [
                [
                    // 19. некорректный melee
                    'id'                           => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
                    'name'                         => 'Skeleton',
                    'level'                        => 3,
                    'avatar'                       => '/images/avas/monsters/003.png',
                    'life'                         => 80,
                    'total_life'                   => 80,
                    'mana'                         => 50,
                    'total_mana'                   => 50,
                    'melee'                        => 1,
                    'class'                        => 1,
                    'race'                         => 1,
                    'command'                      => 1,
                    'add_concentration_multiplier' => 0,
                    'cunning_multiplier'           => 0,
                    'add_rage_multiplier'          => 0,
                    'offense'                      => self::getDefaultOffenseData(),
                    'defense'                      => self::getDefaultDefenseData(),
                    'abilities'                    => [],
                ],
                UnitException::INCORRECT_MELEE,
            ],
            [
                [
                    // 20. некорректный class
                    'id'                           => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
                    'name'                         => 'Skeleton',
                    'level'                        => 3,
                    'avatar'                       => '/images/avas/monsters/003.png',
                    'life'                         => 80,
                    'total_life'                   => 80,
                    'mana'                         => 50,
                    'total_mana'                   => 50,
                    'melee'                        => true,
                    'class'                        => 'warrior',
                    'race'                         => 1,
                    'command'                      => 1,
                    'add_concentration_multiplier' => 0,
                    'cunning_multiplier'           => 0,
                    'add_rage_multiplier'          => 0,
                    'offense'                      => self::getDefaultOffenseData(),
                    'defense'                      => self::getDefaultDefenseData(),
                    'abilities'                    => [],
                ],
                UnitException::INCORRECT_CLASS,
            ],
            [
                [
                    // 21. life большие total life
                    'id'                           => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
                    'name'                         => 'Skeleton',
                    'level'                        => 3,
                    'avatar'                       => '/images/avas/monsters/003.png',
                    'life'                         => 90,
                    'total_life'                   => 80,
                    'mana'                         => 50,
                    'total_mana'                   => 50,
                    'melee'                        => true,
                    'class'                        => 1,
                    'race'                         => 1,
                    'command'                      => 1,
                    'add_concentration_multiplier' => 0,
                    'cunning_multiplier'           => 0,
                    'add_rage_multiplier'          => 0,
                    'offense'                      => self::getDefaultOffenseData(),
                    'defense'                      => self::getDefaultDefenseData(),
                    'abilities'                    => [],
                ],
                UnitException::LIFE_MORE_TOTAL_LIFE,
            ],
            [
                [
                    // 22. level отсутствует
                    'id'                           => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
                    'name'                         => 'Skeleton',
                    'avatar'                       => '/images/avas/monsters/003.png',
                    'life'                         => 80,
                    'total_life'                   => 80,
                    'mana'                         => 50,
                    'total_mana'                   => 50,
                    'melee'                        => true,
                    'class'                        => 1,
                    'race'                         => 1,
                    'command'                      => 1,
                    'add_concentration_multiplier' => 0,
                    'cunning_multiplier'           => 0,
                    'add_rage_multiplier'          => 0,
                    'offense'                      => self::getDefaultOffenseData(),
                    'defense'                      => self::getDefaultDefenseData(),
                    'abilities'                    => [],
                ],
                UnitException::INCORRECT_LEVEL,
            ],
            [
                [
                    // 23. level некорректного типа
                    'id'                           => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
                    'name'                         => 'Skeleton',
                    'level'                        => '3',
                    'avatar'                       => '/images/avas/monsters/003.png',
                    'life'                         => 80,
                    'total_life'                   => 80,
                    'mana'                         => 50,
                    'total_mana'                   => 50,
                    'melee'                        => true,
                    'class'                        => 1,
                    'race'                         => 1,
                    'command'                      => 1,
                    'add_concentration_multiplier' => 0,
                    'cunning_multiplier'           => 0,
                    'add_rage_multiplier'          => 0,
                    'offense'                      => self::getDefaultOffenseData(),
                    'defense'                      => self::getDefaultDefenseData(),
                    'abilities'                    => [],
                ],
                UnitException::INCORRECT_LEVEL,
            ],
            [
                [
                    // 24. level < UnitInterface::MIN_LEVEL
                    'id'                           => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
                    'name'                         => 'Skeleton',
                    'level'                        => UnitInterface::MIN_LEVEL - 1,
                    'avatar'                       => '/images/avas/monsters/003.png',
                    'life'                         => 80,
                    'total_life'                   => 80,
                    'mana'                         => 50,
                    'total_mana'                   => 50,
                    'melee'                        => true,
                    'class'                        => 1,
                    'race'                         => 1,
                    'command'                      => 1,
                    'add_concentration_multiplier' => 0,
                    'cunning_multiplier'           => 0,
                    'add_rage_multiplier'          => 0,
                    'offense'                      => self::getDefaultOffenseData(),
                    'defense'                      => self::getDefaultDefenseData(),
                    'abilities'                    => [],
                ],
                UnitException::INCORRECT_LEVEL_VALUE . UnitInterface::MIN_LEVEL . '-' . UnitInterface::MAX_LEVEL,
            ],
            [
                [
                    // 25. level > UnitInterface::MAX_LEVEL
                    'id'                           => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
                    'name'                         => 'Skeleton',
                    'level'                        => UnitInterface::MAX_LEVEL + 1,
                    'avatar'                       => '/images/avas/monsters/003.png',
                    'life'                         => 80,
                    'total_life'                   => 80,
                    'mana'                         => 50,
                    'total_mana'                   => 50,
                    'melee'                        => true,
                    'class'                        => 1,
                    'race'                         => 1,
                    'command'                      => 1,
                    'add_concentration_multiplier' => 0,
                    'cunning_multiplier'           => 0,
                    'add_rage_multiplier'          => 0,
                    'offense'                      => self::getDefaultOffenseData(),
                    'defense'                      => self::getDefaultDefenseData(),
                    'abilities'                    => [],
                ],
                UnitException::INCORRECT_LEVEL_VALUE . UnitInterface::MIN_LEVEL . '-' . UnitInterface::MAX_LEVEL,
            ],
            [
                [
                    // 26. отсутствует race
                    'id'                           => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
                    'name'                         => 'Skeleton',
                    'level'                        => 3,
                    'avatar'                       => '/images/avas/monsters/003.png',
                    'life'                         => 80,
                    'total_life'                   => 80,
                    'mana'                         => 50,
                    'total_mana'                   => 50,
                    'melee'                        => true,
                    'class'                        => 1,
                    'command'                      => 1,
                    'add_concentration_multiplier' => 0,
                    'cunning_multiplier'           => 0,
                    'add_rage_multiplier'          => 0,
                    'offense'                      => self::getDefaultOffenseData(),
                    'defense'                      => self::getDefaultDefenseData(),
                    'abilities'                    => [],
                ],
                UnitException::INCORRECT_RACE,
            ],
            [
                [
                    // 27. race некорректного типа
                    'id'                           => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
                    'name'                         => 'Skeleton',
                    'level'                        => 3,
                    'avatar'                       => '/images/avas/monsters/003.png',
                    'life'                         => 80,
                    'total_life'                   => 80,
                    'mana'                         => 50,
                    'total_mana'                   => 50,
                    'melee'                        => true,
                    'class'                        => 1,
                    'race'                         => 'human',
                    'command'                      => 1,
                    'add_concentration_multiplier' => 0,
                    'cunning_multiplier'           => 0,
                    'add_rage_multiplier'          => 0,
                    'offense'                      => self::getDefaultOffenseData(),
                    'defense'                      => self::getDefaultDefenseData(),
                    'abilities'                    => [],
                ],
                UnitException::INCORRECT_RACE,
            ],
            [
                [
                    // 28. отсутствует command
                    'id'                           => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
                    'name'                         => 'Skeleton',
                    'level'                        => 3,
                    'avatar'                       => '/images/avas/monsters/003.png',
                    'life'                         => 80,
                    'total_life'                   => 80,
                    'mana'                         => 50,
                    'total_mana'                   => 50,
                    'melee'                        => true,
                    'class'                        => 1,
                    'race'                         => 1,
                    'add_concentration_multiplier' => 0,
                    'cunning_multiplier'           => 0,
                    'add_rage_multiplier'          => 0,
                    'offense'                      => self::getDefaultOffenseData(),
                    'defense'                      => self::getDefaultDefenseData(),
                    'abilities'                    => [],
                ],
                UnitException::INCORRECT_COMMAND,
            ],
            [
                [
                    // 29. command некорректного типа
                    'id'                           => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
                    'name'                         => 'Skeleton',
                    'level'                        => 3,
                    'avatar'                       => '/images/avas/monsters/003.png',
                    'life'                         => 80,
                    'total_life'                   => 80,
                    'mana'                         => 50,
                    'total_mana'                   => 50,
                    'melee'                        => true,
                    'class'                        => 1,
                    'race'                         => 1,
                    'command'                      => 'left',
                    'add_concentration_multiplier' => 0,
                    'cunning_multiplier'           => 0,
                    'add_rage_multiplier'          => 0,
                    'offense'                      => self::getDefaultOffenseData(),
                    'defense'                      => self::getDefaultDefenseData(),
                    'abilities'                    => [],
                ],
                UnitException::INCORRECT_COMMAND,
            ],
            [
                [
                    // 30. command != 1 && != 2
                    'id'                           => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
                    'name'                         => 'Skeleton',
                    'level'                        => 3,
                    'avatar'                       => '/images/avas/monsters/003.png',
                    'life'                         => 80,
                    'total_life'                   => 80,
                    'mana'                         => 50,
                    'total_mana'                   => 50,
                    'melee'                        => true,
                    'class'                        => 1,
                    'race'                         => 1,
                    'command'                      => 3,
                    'add_concentration_multiplier' => 0,
                    'cunning_multiplier'           => 0,
                    'add_rage_multiplier'          => 0,
                    'offense'                      => self::getDefaultOffenseData(),
                    'defense'                      => self::getDefaultDefenseData(),
                    'abilities'                    => [],
                ],
                UnitException::INCORRECT_COMMAND,
            ],
            [
                // 31. Отсутствует offense
                [
                    'id'                           => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
                    'name'                         => 'Skeleton',
                    'level'                        => 3,
                    'avatar'                       => '/images/avas/monsters/003.png',
                    'life'                         => 80,
                    'total_life'                   => 80,
                    'mana'                         => 50,
                    'total_mana'                   => 50,
                    'melee'                        => true,
                    'class'                        => 1,
                    'race'                         => 1,
                    'command'                      => 1,
                    'add_concentration_multiplier' => 0,
                    'cunning_multiplier'           => 0,
                    'add_rage_multiplier'          => 0,
                    'defense'                      => self::getDefaultDefenseData(),
                    'abilities'                    => [],
                ],
                UnitException::INCORRECT_OFFENSE,
            ],
            [
                // 32. offense некорректного типа
                [
                    'id'                           => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
                    'name'                         => 'Skeleton',
                    'level'                        => 3,
                    'avatar'                       => '/images/avas/monsters/003.png',
                    'life'                         => 80,
                    'total_life'                   => 80,
                    'mana'                         => 50,
                    'total_mana'                   => 50,
                    'melee'                        => true,
                    'class'                        => 1,
                    'race'                         => 1,
                    'command'                      => 1,
                    'add_concentration_multiplier' => 0,
                    'cunning_multiplier'           => 0,
                    'add_rage_multiplier'          => 0,
                    'offense'                      => true,
                    'defense'                      => self::getDefaultDefenseData(),
                    'abilities'                    => [],
                ],
                UnitException::INCORRECT_OFFENSE,
            ],
            [
                // 33. Отсутствует defense
                [
                    'id'                           => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
                    'name'                         => 'Skeleton',
                    'level'                        => 3,
                    'avatar'                       => '/images/avas/monsters/003.png',
                    'life'                         => 80,
                    'total_life'                   => 80,
                    'mana'                         => 50,
                    'total_mana'                   => 50,
                    'melee'                        => true,
                    'class'                        => 1,
                    'race'                         => 1,
                    'command'                      => 1,
                    'add_concentration_multiplier' => 0,
                    'cunning_multiplier'           => 0,
                    'add_rage_multiplier'          => 0,
                    'offense'                      => [
                        'damage_type'         => 1,
                        'weapon_type'         => WeaponTypeInterface::SWORD,
                        'physical_damage'     => 15,
                        'fire_damage'         => 0,
                        'water_damage'        => 0,
                        'air_damage'          => 0,
                        'earth_damage'        => 0,
                        'life_damage'         => 0,
                        'death_damage'        => 0,
                        'attack_speed'        => 1.2,
                        'cast_speed'          => 0,
                        'accuracy'            => 200,
                        'magic_accuracy'      => 100,
                        'block_ignoring'      => 0,
                        'critical_chance'     => 5,
                        'critical_multiplier' => 200,
                        'damage_multiplier'   => 100,
                        'vampirism'           => 0,
                        'magic_vampirism'     => 0,
                    ],
                    'abilities'                    => [],
                ],
                UnitException::INCORRECT_DEFENSE,
            ],
            [
                // 34. defense некорректного типа
                [
                    'id'                           => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
                    'name'                         => 'Skeleton',
                    'level'                        => 3,
                    'avatar'                       => '/images/avas/monsters/003.png',
                    'life'                         => 80,
                    'total_life'                   => 80,
                    'mana'                         => 50,
                    'total_mana'                   => 50,
                    'melee'                        => true,
                    'class'                        => 1,
                    'race'                         => 1,
                    'command'                      => 1,
                    'add_concentration_multiplier' => 0,
                    'cunning_multiplier'           => 0,
                    'add_rage_multiplier'          => 0,
                    'offense'                      => self::getDefaultOffenseData(),
                    'defense'                      => 100,
                    'abilities'                    => [],
                ],
                UnitException::INCORRECT_DEFENSE,
            ],
            // Mana
            [
                // 35. Отсутствует mana
                [
                    'id'                           => '5aa0d764-e92d-4137-beed-f7f590b08165',
                    'name'                         => 'Skeleton',
                    'level'                        => 5,
                    'avatar'                       => '/images/avas/monsters/003.png',
                    'life'                         => 80,
                    'total_life'                   => 80,
                    'total_mana'                   => 50,
                    'melee'                        => true,
                    'class'                        => 1,
                    'race'                         => 8,
                    'command'                      => 1,
                    'add_concentration_multiplier' => 0,
                    'cunning_multiplier'           => 0,
                    'add_rage_multiplier'          => 0,
                    'offense'                      => self::getDefaultOffenseData(),
                    'defense'                      => self::getDefaultDefenseData(),
                    'abilities'                    => [],
                ],
                UnitException::INCORRECT_MANA,
            ],
            [
                // 36. mana некорректного типа
                [
                    'id'                           => '5aa0d764-e92d-4137-beed-f7f590b08165',
                    'name'                         => 'Skeleton',
                    'level'                        => 5,
                    'avatar'                       => '/images/avas/monsters/003.png',
                    'life'                         => 80,
                    'total_life'                   => 80,
                    'mana'                         => 50.0,
                    'total_mana'                   => 50,
                    'melee'                        => true,
                    'class'                        => 1,
                    'race'                         => 8,
                    'command'                      => 1,
                    'add_concentration_multiplier' => 0,
                    'cunning_multiplier'           => 0,
                    'add_rage_multiplier'          => 0,
                    'offense'                      => self::getDefaultOffenseData(),
                    'defense'                      => self::getDefaultDefenseData(),
                    'abilities'                    => [],
                ],
                UnitException::INCORRECT_MANA,
            ],
            [
                // 37. mana меньше минимального значения
                [
                    'id'                           => '5aa0d764-e92d-4137-beed-f7f590b08165',
                    'name'                         => 'Skeleton#37',
                    'level'                        => 5,
                    'avatar'                       => '/images/avas/monsters/003.png',
                    'life'                         => 80,
                    'total_life'                   => 80,
                    'mana'                         => UnitInterface::MIN_MANA - 1,
                    'total_mana'                   => 50,
                    'melee'                        => true,
                    'class'                        => 1,
                    'race'                         => 8,
                    'command'                      => 1,
                    'add_concentration_multiplier' => 0,
                    'cunning_multiplier'           => 0,
                    'add_rage_multiplier'          => 0,
                    'offense'                      => self::getDefaultOffenseData(),
                    'defense'                      => self::getDefaultDefenseData(),
                    'abilities'                    => [],
                ],
                UnitException::INCORRECT_MANA_VALUE . UnitInterface::MIN_MANA . '-' . UnitInterface::MAX_MANA,
            ],
            [
                // 38. mana больше максимального значения
                [
                    'id'                           => '5aa0d764-e92d-4137-beed-f7f590b08165',
                    'name'                         => 'Skeleton',
                    'level'                        => 5,
                    'avatar'                       => '/images/avas/monsters/003.png',
                    'life'                         => 80,
                    'total_life'                   => 80,
                    'mana'                         => UnitInterface::MAX_MANA + 1,
                    'total_mana'                   => 50,
                    'melee'                        => true,
                    'class'                        => 1,
                    'race'                         => 8,
                    'command'                      => 1,
                    'add_concentration_multiplier' => 0,
                    'cunning_multiplier'           => 0,
                    'add_rage_multiplier'          => 0,
                    'offense'                      => self::getDefaultOffenseData(),
                    'defense'                      => self::getDefaultDefenseData(),
                    'abilities'                    => [],
                ],
                UnitException::INCORRECT_MANA_VALUE . UnitInterface::MIN_MANA . '-' . UnitInterface::MAX_MANA,
            ],
            [
                // 39. Отсутствует total_mana
                [
                    'id'                           => '5aa0d764-e92d-4137-beed-f7f590b08165',
                    'name'                         => 'Skeleton',
                    'level'                        => 5,
                    'avatar'                       => '/images/avas/monsters/003.png',
                    'life'                         => 80,
                    'total_life'                   => 80,
                    'mana'                         => 50,
                    'melee'                        => true,
                    'class'                        => 1,
                    'race'                         => 8,
                    'command'                      => 1,
                    'add_concentration_multiplier' => 0,
                    'cunning_multiplier'           => 0,
                    'add_rage_multiplier'          => 0,
                    'offense'                      => self::getDefaultOffenseData(),
                    'defense'                      => self::getDefaultDefenseData(),
                    'abilities'                    => [],
                ],
                UnitException::INCORRECT_TOTAL_MANA,
            ],
            [
                // 40. total_mana некорректного типа
                [
                    'id'                           => '5aa0d764-e92d-4137-beed-f7f590b08165',
                    'name'                         => 'Skeleton',
                    'level'                        => 5,
                    'avatar'                       => '/images/avas/monsters/003.png',
                    'life'                         => 80,
                    'total_life'                   => 80,
                    'mana'                         => 50,
                    'total_mana'                   => '50',
                    'melee'                        => true,
                    'class'                        => 1,
                    'race'                         => 8,
                    'command'                      => 1,
                    'add_concentration_multiplier' => 0,
                    'cunning_multiplier'           => 0,
                    'add_rage_multiplier'          => 0,
                    'offense'                      => self::getDefaultOffenseData(),
                    'defense'                      => self::getDefaultDefenseData(),
                    'abilities'                    => [],
                ],
                UnitException::INCORRECT_TOTAL_MANA,
            ],
            [
                // 41. total_mana меньше минимального значения
                [
                    'id'                           => '5aa0d764-e92d-4137-beed-f7f590b08165',
                    'name'                         => 'Skeleton',
                    'level'                        => 5,
                    'avatar'                       => '/images/avas/monsters/003.png',
                    'life'                         => 80,
                    'total_life'                   => 80,
                    'mana'                         => 50,
                    'total_mana'                   => UnitInterface::MIN_TOTAL_MANA - 1,
                    'melee'                        => true,
                    'class'                        => 1,
                    'race'                         => 8,
                    'command'                      => 1,
                    'add_concentration_multiplier' => 0,
                    'cunning_multiplier'           => 0,
                    'add_rage_multiplier'          => 0,
                    'offense'                      => self::getDefaultOffenseData(),
                    'defense'                      => self::getDefaultDefenseData(),
                    'abilities'                    => [],
                ],
                UnitException::INCORRECT_TOTAL_MANA_VALUE . UnitInterface::MIN_TOTAL_MANA . '-' . UnitInterface::MAX_TOTAL_MANA,
            ],
            [
                // 42. total_mana больше максимального значения
                [
                    'id'                           => '5aa0d764-e92d-4137-beed-f7f590b08165',
                    'name'                         => 'Skeleton',
                    'level'                        => 5,
                    'avatar'                       => '/images/avas/monsters/003.png',
                    'life'                         => 80,
                    'total_life'                   => 80,
                    'mana'                         => 50,
                    'total_mana'                   => UnitInterface::MAX_TOTAL_MANA + 1,
                    'melee'                        => true,
                    'class'                        => 1,
                    'race'                         => 8,
                    'command'                      => 1,
                    'add_concentration_multiplier' => 0,
                    'cunning_multiplier'           => 0,
                    'add_rage_multiplier'          => 0,
                    'offense'                      => self::getDefaultOffenseData(),
                    'defense'                      => self::getDefaultDefenseData(),
                    'abilities'                    => [],
                ],
                UnitException::INCORRECT_TOTAL_MANA_VALUE . UnitInterface::MIN_TOTAL_MANA . '-' . UnitInterface::MAX_TOTAL_MANA,
            ],
            [
                // 43. mana больше total_mana
                [
                    'id'                           => '5aa0d764-e92d-4137-beed-f7f590b08165',
                    'name'                         => 'Skeleton',
                    'level'                        => 5,
                    'avatar'                       => '/images/avas/monsters/003.png',
                    'life'                         => 80,
                    'total_life'                   => 80,
                    'mana'                         => 150,
                    'total_mana'                   => 149,
                    'melee'                        => true,
                    'class'                        => 1,
                    'race'                         => 8,
                    'command'                      => 1,
                    'add_concentration_multiplier' => 0,
                    'cunning_multiplier'           => 0,
                    'add_rage_multiplier'          => 0,
                    'offense'                      => self::getDefaultOffenseData(),
                    'defense'                      => self::getDefaultDefenseData(),
                    'abilities'                    => [],
                ],
                UnitException::MANA_MORE_TOTAL_MANA,
            ],

            // add_concentration_multiplier
            [
                [
                    // отсутствует add_concentration_multiplier
                    'id'                  => 'fb8be211-0782-4c60-8865-68b177ffbedc',
                    'name'                => 'Archer',
                    'level'               => 1,
                    'avatar'              => 'url avatar 3',
                    'life'                => 50,
                    'total_life'          => 75,
                    'mana'                => 50,
                    'total_mana'          => 50,
                    'melee'               => false,
                    'class'               => 2,
                    'race'                => 1,
                    'command'             => 1,
                    'add_rage_multiplier' => 0,
                    'cunning_multiplier'  => 0,
                    'offense'             => self::getDefaultOffenseData(),
                    'defense'             => self::getDefaultDefenseData(),
                    'abilities'           => [],
                ],
                UnitException::INCORRECT_ADD_CONC_MULTIPLIER,
            ],
            [
                [
                    // add_concentration_multiplier некорректного типа
                    'id'                           => 'fb8be211-0782-4c60-8865-68b177ffbedc',
                    'name'                         => 'Archer',
                    'level'                        => 1,
                    'avatar'                       => 'url avatar 3',
                    'life'                         => 50,
                    'total_life'                   => 75,
                    'mana'                         => 50,
                    'total_mana'                   => 50,
                    'melee'                        => false,
                    'class'                        => 2,
                    'race'                         => 1,
                    'command'                      => 1,
                    'add_concentration_multiplier' => true,
                    'cunning_multiplier'           => 0,
                    'add_rage_multiplier'          => 0,
                    'offense'                      => self::getDefaultOffenseData(),
                    'defense'                      => self::getDefaultDefenseData(),
                    'abilities'                    => [],
                ],
                UnitException::INCORRECT_ADD_CONC_MULTIPLIER,
            ],
            [
                [
                    // add_concentration_multiplier меньше минимального значения
                    'id'                           => 'fb8be211-0782-4c60-8865-68b177ffbedc',
                    'name'                         => 'Archer',
                    'level'                        => 1,
                    'avatar'                       => 'url avatar 3',
                    'life'                         => 50,
                    'total_life'                   => 75,
                    'mana'                         => 50,
                    'total_mana'                   => 50,
                    'melee'                        => false,
                    'class'                        => 2,
                    'race'                         => 1,
                    'command'                      => 1,
                    'add_concentration_multiplier' => UnitInterface::MIN_RESOURCE_MULTIPLIER - 1,
                    'add_rage_multiplier'          => 0,
                    'cunning_multiplier'           => 0,
                    'offense'                      => self::getDefaultOffenseData(),
                    'defense'                      => self::getDefaultDefenseData(),
                    'abilities'                    => [],
                ],
                UnitException::INCORRECT_ADD_CONC_MULTIPLIER_VALUE . UnitInterface::MIN_RESOURCE_MULTIPLIER . ' - ' . UnitInterface::MAX_RESOURCE_MULTIPLIER,
            ],
            [
                [
                    // add_concentration_multiplier больше максимального типа
                    'id'                           => 'fb8be211-0782-4c60-8865-68b177ffbedc',
                    'name'                         => 'Archer',
                    'level'                        => 1,
                    'avatar'                       => 'url avatar 3',
                    'life'                         => 50,
                    'total_life'                   => 75,
                    'mana'                         => 50,
                    'total_mana'                   => 50,
                    'melee'                        => false,
                    'class'                        => 2,
                    'race'                         => 1,
                    'command'                      => 1,
                    'add_concentration_multiplier' => UnitInterface::MAX_RESOURCE_MULTIPLIER + 1,
                    'cunning_multiplier'           => 0,
                    'add_rage_multiplier'          => 0,
                    'offense'                      => self::getDefaultOffenseData(),
                    'defense'                      => self::getDefaultDefenseData(),
                    'abilities'                    => [],
                ],
                UnitException::INCORRECT_ADD_CONC_MULTIPLIER_VALUE . UnitInterface::MIN_RESOURCE_MULTIPLIER . ' - ' . UnitInterface::MAX_RESOURCE_MULTIPLIER,
            ],

            // cunning_multiplier
            [
                [
                    // отсутствует cunning_multiplier
                    'id'                           => 'fb8be211-0782-4c60-8865-68b177ffbedc',
                    'name'                         => 'Archer',
                    'level'                        => 1,
                    'avatar'                       => 'url avatar 3',
                    'life'                         => 50,
                    'total_life'                   => 75,
                    'mana'                         => 50,
                    'total_mana'                   => 50,
                    'melee'                        => false,
                    'class'                        => 2,
                    'race'                         => 1,
                    'command'                      => 1,
                    'add_concentration_multiplier' => 0,
                    'add_rage_multiplier'          => 0,
                    'offense'                      => self::getDefaultOffenseData(),
                    'defense'                      => self::getDefaultDefenseData(),
                    'abilities'                    => [],
                ],
                UnitException::INCORRECT_CUNNING_MULTIPLIER,
            ],
            [
                [
                    // cunning_multiplier некорректного типа
                    'id'                           => 'fb8be211-0782-4c60-8865-68b177ffbedc',
                    'name'                         => 'Archer',
                    'level'                        => 1,
                    'avatar'                       => 'url avatar 3',
                    'life'                         => 50,
                    'total_life'                   => 75,
                    'mana'                         => 50,
                    'total_mana'                   => 50,
                    'melee'                        => false,
                    'class'                        => 2,
                    'race'                         => 1,
                    'command'                      => 1,
                    'add_concentration_multiplier' => 0,
                    'cunning_multiplier'           => [0],
                    'add_rage_multiplier'          => 0,
                    'offense'                      => self::getDefaultOffenseData(),
                    'defense'                      => self::getDefaultDefenseData(),
                    'abilities'                    => [],
                ],
                UnitException::INCORRECT_CUNNING_MULTIPLIER,
            ],
            [
                [
                    // cunning_multiplier меньше минимального значения
                    'id'                           => 'fb8be211-0782-4c60-8865-68b177ffbedc',
                    'name'                         => 'Archer',
                    'level'                        => 1,
                    'avatar'                       => 'url avatar 3',
                    'life'                         => 50,
                    'total_life'                   => 75,
                    'mana'                         => 50,
                    'total_mana'                   => 50,
                    'melee'                        => false,
                    'class'                        => 2,
                    'race'                         => 1,
                    'command'                      => 1,
                    'add_concentration_multiplier' => 0,
                    'cunning_multiplier'           => UnitInterface::MIN_RESOURCE_MULTIPLIER - 1,
                    'add_rage_multiplier'          => 0,
                    'offense'                      => self::getDefaultOffenseData(),
                    'defense'                      => self::getDefaultDefenseData(),
                    'abilities'                    => [],
                ],
                UnitException::INCORRECT_CUNNING_MULTIPLIER_VALUE . UnitInterface::MIN_RESOURCE_MULTIPLIER . ' - ' . UnitInterface::MAX_RESOURCE_MULTIPLIER,
            ],
            [
                [
                    // cunning_multiplier больше максимального типа
                    'id'                           => 'fb8be211-0782-4c60-8865-68b177ffbedc',
                    'name'                         => 'Archer',
                    'level'                        => 1,
                    'avatar'                       => 'url avatar 3',
                    'life'                         => 50,
                    'total_life'                   => 75,
                    'mana'                         => 50,
                    'total_mana'                   => 50,
                    'melee'                        => false,
                    'class'                        => 2,
                    'race'                         => 1,
                    'command'                      => 1,
                    'add_concentration_multiplier' => 0,
                    'cunning_multiplier'           => UnitInterface::MAX_RESOURCE_MULTIPLIER + 1,
                    'add_rage_multiplier'          => 0,
                    'offense'                      => self::getDefaultOffenseData(),
                    'defense'                      => self::getDefaultDefenseData(),
                    'abilities'                    => [],
                ],
                UnitException::INCORRECT_CUNNING_MULTIPLIER_VALUE . UnitInterface::MIN_RESOURCE_MULTIPLIER . ' - ' . UnitInterface::MAX_RESOURCE_MULTIPLIER,
            ],

            // add_rage_multiplier
            [
                [
                    // отсутствует add_rage_multiplier
                    'id'                           => 'fb8be211-0782-4c60-8865-68b177ffbedc',
                    'name'                         => 'Archer',
                    'level'                        => 1,
                    'avatar'                       => 'url avatar 3',
                    'life'                         => 50,
                    'total_life'                   => 75,
                    'mana'                         => 50,
                    'total_mana'                   => 50,
                    'melee'                        => false,
                    'class'                        => 2,
                    'race'                         => 1,
                    'command'                      => 1,
                    'add_concentration_multiplier' => 0,
                    'cunning_multiplier'           => 0,
                    'offense'                      => self::getDefaultOffenseData(),
                    'defense'                      => self::getDefaultDefenseData(),
                    'abilities'                    => [],
                ],
                UnitException::INCORRECT_ADD_RAGE_MULTIPLIER,
            ],
            [
                [
                    // add_rage_multiplier некорректного типа
                    'id'                           => 'fb8be211-0782-4c60-8865-68b177ffbedc',
                    'name'                         => 'Archer',
                    'level'                        => 1,
                    'avatar'                       => 'url avatar 3',
                    'life'                         => 50,
                    'total_life'                   => 75,
                    'mana'                         => 50,
                    'total_mana'                   => 50,
                    'melee'                        => false,
                    'class'                        => 2,
                    'race'                         => 1,
                    'command'                      => 1,
                    'add_concentration_multiplier' => 0,
                    'add_rage_multiplier'          => null,
                    'cunning_multiplier'           => 0,
                    'offense'                      => self::getDefaultOffenseData(),
                    'defense'                      => self::getDefaultDefenseData(),
                    'abilities'                    => [],
                ],
                UnitException::INCORRECT_ADD_RAGE_MULTIPLIER,
            ],
            [
                [
                    // add_rage_multiplier меньше минимального значения
                    'id'                           => 'fb8be211-0782-4c60-8865-68b177ffbedc',
                    'name'                         => 'Archer',
                    'level'                        => 1,
                    'avatar'                       => 'url avatar 3',
                    'life'                         => 50,
                    'total_life'                   => 75,
                    'mana'                         => 50,
                    'total_mana'                   => 50,
                    'melee'                        => false,
                    'class'                        => 2,
                    'race'                         => 1,
                    'command'                      => 1,
                    'add_concentration_multiplier' => 0,
                    'cunning_multiplier'           => 0,
                    'add_rage_multiplier'          => UnitInterface::MIN_RESOURCE_MULTIPLIER - 1,
                    'offense'                      => self::getDefaultOffenseData(),
                    'defense'                      => self::getDefaultDefenseData(),
                    'abilities'                    => [],
                ],
                UnitException::INCORRECT_ADD_RAGE_MULTIPLIER_VALUE . UnitInterface::MIN_RESOURCE_MULTIPLIER . ' - ' . UnitInterface::MAX_RESOURCE_MULTIPLIER,
            ],
            [
                [
                    // add_rage_multiplier больше максимального типа
                    'id'                           => 'fb8be211-0782-4c60-8865-68b177ffbedc',
                    'name'                         => 'Archer',
                    'level'                        => 1,
                    'avatar'                       => 'url avatar 3',
                    'life'                         => 50,
                    'total_life'                   => 75,
                    'mana'                         => 50,
                    'total_mana'                   => 50,
                    'melee'                        => false,
                    'class'                        => 2,
                    'race'                         => 1,
                    'command'                      => 1,
                    'add_concentration_multiplier' => 0,
                    'cunning_multiplier'           => 0,
                    'add_rage_multiplier'          => UnitInterface::MAX_RESOURCE_MULTIPLIER + 1,
                    'offense'                      => self::getDefaultOffenseData(),
                    'defense'                      => self::getDefaultDefenseData(),
                    'abilities'                    => [],
                ],
                UnitException::INCORRECT_ADD_RAGE_MULTIPLIER_VALUE . UnitInterface::MIN_RESOURCE_MULTIPLIER . ' - ' . UnitInterface::MAX_RESOURCE_MULTIPLIER,
            ],
            [
                // Отсутствует abilities
                [
                    'id'                           => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
                    'name'                         => 'Skeleton',
                    'level'                        => 3,
                    'avatar'                       => '/images/avas/monsters/003.png',
                    'life'                         => 80,
                    'total_life'                   => 80,
                    'mana'                         => 50,
                    'total_mana'                   => 50,
                    'melee'                        => true,
                    'class'                        => 1,
                    'race'                         => 1,
                    'command'                      => 1,
                    'add_concentration_multiplier' => 0,
                    'cunning_multiplier'           => 0,
                    'add_rage_multiplier'          => 0,
                    'offense'                      => self::getDefaultOffenseData(),
                    'defense'                      => self::getDefaultDefenseData(),
                ],
                UnitException::INCORRECT_ABILITIES_DATA,
            ],
            [
                // abilities некорректного типа
                [
                    'id'                           => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
                    'name'                         => 'Skeleton',
                    'level'                        => 3,
                    'avatar'                       => '/images/avas/monsters/003.png',
                    'life'                         => 80,
                    'total_life'                   => 80,
                    'mana'                         => 50,
                    'total_mana'                   => 50,
                    'melee'                        => true,
                    'class'                        => 1,
                    'race'                         => 1,
                    'command'                      => 1,
                    'add_concentration_multiplier' => 0,
                    'cunning_multiplier'           => 0,
                    'add_rage_multiplier'          => 0,
                    'offense'                      => self::getDefaultOffenseData(),
                    'defense'                      => self::getDefaultDefenseData(),
                    'abilities'                    => false,
                ],
                UnitException::INCORRECT_ABILITIES_DATA,
            ],
            [
                // abilities содержит не массивы
                [
                    'id'                           => '5a9e559a-954d-4b7c-98fe-4e9609523e6e',
                    'name'                         => 'Skeleton',
                    'level'                        => 3,
                    'avatar'                       => '/images/avas/monsters/003.png',
                    'life'                         => 80,
                    'total_life'                   => 80,
                    'mana'                         => 50,
                    'total_mana'                   => 50,
                    'melee'                        => true,
                    'class'                        => 1,
                    'race'                         => 1,
                    'command'                      => 1,
                    'add_concentration_multiplier' => 0,
                    'cunning_multiplier'           => 0,
                    'add_rage_multiplier'          => 0,
                    'offense'                      => self::getDefaultOffenseData(),
                    'defense'                      => self::getDefaultDefenseData(),
                    'abilities'                    => ['invalid_data'],
                ],
                UnitException::INCORRECT_ABILITY_DATA,
            ],
        ];
    }

    /**
     * @param array $data
     * @param ContainerInterface $container
     * @return UnitClassInterface|null
     * @throws Exception
     */
    private function createClass(array $data, ContainerInterface $container): ?UnitClassInterface
    {
        if (!array_key_exists('class', $data)) {
            return null;
        }

        if (!is_int($data['class'])) {
            return null;
        }

        return $container->getUnitClassFactory()->create(
            $container->getClassDataProvider()->get($data['class'])
        );
    }

    /**
     * @param int $raceId
     * @param ContainerInterface $container
     * @return RaceInterface
     * @throws Exception
     */
    private function createRace(int $raceId, ContainerInterface $container): RaceInterface
    {
        return $container->getRaceFactory()->create(
            $container->getRaceDataProvider()->get($raceId)
        );
    }

    /**
     * @return DefenseInterface
     * @throws Exception
     */
    private function getDefense(): DefenseInterface
    {
        return new Defense(
            0,
            0,
            0,
            0,
            0,
            0,
            0,
            10,
            10,
            10,
            5,
            0,
            75,
            75,
            75,
            75,
            75,
            75,
            75,
            0,
            0
        );
    }

    private static function getDefaultOffenseData(): array
    {
        return [
            'damage_type'         => 1,
            'weapon_type'         => WeaponTypeInterface::SWORD,
            'physical_damage'     => 15,
            'fire_damage'         => 18,
            'water_damage'        => 19,
            'air_damage'          => 20,
            'earth_damage'        => 21,
            'life_damage'         => 22,
            'death_damage'        => 23,
            'attack_speed'        => 1.2,
            'cast_speed'          => 1.5,
            'accuracy'            => 200,
            'magic_accuracy'      => 100,
            'block_ignoring'      => 0,
            'critical_chance'     => 5,
            'critical_multiplier' => 200,
            'damage_multiplier'   => 100,
            'vampirism'           => 0,
            'magic_vampirism'     => 0,
        ];
    }

    private static function getDefaultDefenseData(): array
    {
        return [
            'physical_resist'     => 0,
            'fire_resist'         => 0,
            'water_resist'        => 0,
            'air_resist'          => 0,
            'earth_resist'        => 0,
            'life_resist'         => 0,
            'death_resist'        => 0,
            'defense'             => 100,
            'magic_defense'       => 50,
            'block'               => 0,
            'magic_block'         => 10,
            'mental_barrier'      => 0,
            'max_physical_resist' => 75,
            'max_fire_resist'     => 75,
            'max_water_resist'    => 75,
            'max_air_resist'      => 75,
            'max_earth_resist'    => 75,
            'max_life_resist'     => 75,
            'max_death_resist'    => 75,
            'global_resist'       => 0,
            'dodge'               => 0,
        ];
    }
}
