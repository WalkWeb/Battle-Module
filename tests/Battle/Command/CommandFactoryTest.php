<?php

declare(strict_types=1);

namespace Tests\Battle\Command;

use Battle\Container\Container;
use Battle\Result\FullLog\FullLog;
use Battle\Command\CommandException;
use Battle\Command\CommandFactory;
use Battle\Unit\Defense\Defense;
use Battle\Unit\Defense\DefenseInterface;
use Battle\Unit\UnitException;
use Battle\Weapon\Type\WeaponTypeInterface;
use Exception;
use Tests\AbstractUnitTest;
use Tests\Battle\Factory\UnitFactory;

class CommandFactoryTest extends AbstractUnitTest
{
    /**
     * @dataProvider successDataProvider
     * @param array $data
     * @throws Exception
     */
    public function testCommandFactoryCreateFromDataSuccess(array $data): void
    {
        $container = new Container();
        $command = CommandFactory::create($data);

        self::assertSameSize($data, $command->getUnits());

        $i = 0;
        foreach ($command->getUnits() as $unit) {
            $class = $container->getUnitClassFactory()->create(
                $container->getClassDataProvider()->get($data[$i]['class'])
            );

            self::assertEquals($data[$i]['name'], $unit->getName());
            self::assertEquals($data[$i]['level'], $unit->getLevel());
            self::assertEquals($data[$i]['avatar'], $unit->getAvatar());
            self::assertEquals($data[$i]['offense']['physical_damage'], $unit->getOffense()->getDamage($this->getDefense()));
            self::assertEquals($data[$i]['offense']['attack_speed'], $unit->getOffense()->getAttackSpeed());
            self::assertEquals($data[$i]['offense']['cast_speed'], $unit->getOffense()->getCastSpeed());
            self::assertEquals($data[$i]['offense']['accuracy'], $unit->getOffense()->getAccuracy());
            self::assertEquals($data[$i]['offense']['block_ignoring'], $unit->getOffense()->getBlockIgnoring());
            self::assertEquals($data[$i]['defense']['defense'], $unit->getDefense()->getDefense());
            self::assertEquals($data[$i]['defense']['block'], $unit->getDefense()->getBlock());
            self::assertEquals($data[$i]['defense']['mental_barrier'], $unit->getDefense()->getMentalBarrier());
            self::assertEquals($data[$i]['life'], $unit->getTotalLife());
            self::assertEquals($data[$i]['life'], $unit->getLife());
            self::assertEquals($data[$i]['melee'], $unit->isMelee());
            self::assertEquals($class, $unit->getClass());

            $i++;
        }
    }

    /**
     * @dataProvider failDataProvider
     * @param array $data
     * @param string $error
     * @throws CommandException
     * @throws UnitException
     */
    public function testCommandFactoryCreateFromDataFail(array $data, string $error): void
    {
        $this->expectException(CommandException::class);
        $this->expectErrorMessage($error);
        CommandFactory::create($data);
    }

    /**
     * @throws CommandException
     * @throws UnitException
     * @throws Exception
     */
    public function testCommandFactoryCreateFromUsersSuccess(): void
    {
        $unit = UnitFactory::createByTemplate(1);

        $command = CommandFactory::create([$unit]);

        foreach ($command->getUnits() as $commandUnit) {
            self::assertEquals($unit, $commandUnit);
        }
    }

    /**
     * @throws CommandException
     * @throws UnitException
     */
    public function testCommandFactoryCreateFromUsersFail(): void
    {
        $unit = new FullLog();
        $this->expectException(CommandException::class);
        $this->expectErrorMessage(CommandException::INCORRECT_OBJECT_UNIT);
        CommandFactory::create([$unit]);
    }

    /**
     * @return array
     */
    public function successDataProvider(): array
    {
        return [
            [
                [
                    [
                        'id'                           => '63ad76c6-6a11-44ef-997b-fea1778bebe5',
                        'name'                         => 'Skeleton',
                        'level'                        => 3,
                        'avatar'                       => 'url avatar 1',
                        'life'                         => 80,
                        'total_life'                   => 80,
                        'mana'                         => 50,
                        'total_mana'                   => 50,
                        'melee'                        => true,
                        'class'                        => 1,
                        'race'                         => 8,
                        'command'                      => 1,
                        'add_concentration_multiplier' => 0,
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
                            'vampirism'           => 0,
                        ],
                        'defense'                      => [
                            'physical_resist' => 0,
                            'fire_resist'     => 0,
                            'water_resist'    => 0,
                            'air_resist'      => 0,
                            'earth_resist'    => 0,
                            'life_resist'     => 0,
                            'death_resist'    => 0,
                            'defense'         => 100,
                            'magic_defense'   => 50,
                            'block'           => 0,
                            'magic_block'     => 0,
                            'mental_barrier'  => 0,
                        ],
                    ],
                    [
                        'id'                           => 'fb8be211-0782-4c60-8865-68b177ffbe0c',
                        'name'                         => 'Ghost',
                        'level'                        => 12,
                        'avatar'                       => 'url avatar 2',
                        'life'                         => 75,
                        'total_life'                   => 75,
                        'mana'                         => 50,
                        'total_mana'                   => 50,
                        'melee'                        => false,
                        'class'                        => 2,
                        'race'                         => 8,
                        'command'                      => 1,
                        'add_concentration_multiplier' => 0,
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
                            'vampirism'           => 0,
                        ],
                        'defense'                      => [
                            'physical_resist' => 0,
                            'fire_resist'     => 0,
                            'water_resist'    => 0,
                            'air_resist'      => 0,
                            'earth_resist'    => 0,
                            'life_resist'     => 0,
                            'death_resist'    => 0,
                            'defense'         => 100,
                            'magic_defense'   => 50,
                            'block'           => 0,
                            'magic_block'     => 0,
                            'mental_barrier'  => 0,
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
                // отсутствует name
                [
                    [
                        'id'                           => 'f83b1152-b186-4a17-a3dd-88ac75e3cd23',
                        'level'                        => 1,
                        'avatar'                       => 'url avatar 1',
                        'life'                         => 80,
                        'total_life'                   => 80,
                        'mana'                         => 50,
                        'total_mana'                   => 50,
                        'melee'                        => true,
                        'class'                        => 1,
                        'race'                         => 1,
                        'add_concentration_multiplier' => 0,
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
                            'vampirism'           => 0,
                        ],
                        'defense'                      => [
                            'physical_resist' => 0,
                            'fire_resist'     => 0,
                            'water_resist'    => 0,
                            'air_resist'      => 0,
                            'earth_resist'    => 0,
                            'life_resist'     => 0,
                            'death_resist'    => 0,
                            'defense'         => 100,
                            'magic_defense'   => 50,
                            'block'           => 0,
                            'magic_block'     => 0,
                            'mental_barrier'  => 0,
                        ],
                    ],
                ],
                UnitException::INCORRECT_NAME . ' (1 element)',
            ],
            [
                // string вместо array данных по юниту
                [
                    'string',
                ],
                CommandException::INCORRECT_UNIT_DATA,
            ],
        ];
    }

    /**
     * @return DefenseInterface
     * @throws Exception
     */
    private function getDefense(): DefenseInterface
    {
        return new Defense(0, 0, 0, 0, 0, 0, 0, 10, 10, 10, 5, 0);
    }
}
