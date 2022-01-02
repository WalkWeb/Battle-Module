<?php

declare(strict_types=1);

namespace Tests\Battle;

use Battle\BattleFactory;
use Battle\BattleInterface;
use Battle\Command\CommandException;
use Battle\Command\CommandFactory;
use Battle\BattleException;
use Battle\Unit\UnitException;
use Exception;
use Tests\AbstractUnitTest;

class BattleFactoryTest extends AbstractUnitTest
{
    /**
     * @dataProvider successDataProvider
     * @param array $data
     * @throws Exception
     */
    public function testBattleFactoryCreateSuccess(array $data): void
    {
        $battle = BattleFactory::create($data);
        $result = $battle->handle();
        self::assertIsInt($result->getWinner());
    }

    /**
     * @throws Exception
     */
    public function testBattleFactoryCreateFail(): void
    {
        $this->expectException(CommandException::class);
        $this->expectExceptionMessage(CommandException::NO_UNITS);
        BattleFactory::create([]);
    }

    /**
     * @dataProvider successLeftCommandDataProvider
     * @param array $data
     * @param array $expectedData
     * @param int $command
     * @throws BattleException
     * @throws CommandException
     * @throws UnitException
     */
    public function testBattleFactoryCreateCommandSuccess(array $data, array $expectedData, int $command): void
    {
        $command = BattleFactory::createCommand($data, $command);
        $expectCommand = CommandFactory::create($expectedData);
        self::assertEquals($expectCommand, $command);
    }

    /**
     * @dataProvider failDataProvider
     * @param array $data
     * @param string $error
     * @throws Exception
     */
    public function testBattleFactoryCreateCommandFail(array $data, string $error): void
    {
        $this->expectException(BattleException::class);
        $this->expectExceptionMessage($error);
        BattleFactory::create($data);
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
                        'id'           => 'f2f093cc-da4f-490b-aba0-9c0c89cc564d',
                        'name'         => 'Skeleton',
                        'level'        => 1,
                        'avatar'       => '/images/avas/monsters/003.png',
                        'damage'       => 15,
                        'attack_speed' => 1.2,
                        'life'         => 80,
                        'total_life'   => 80,
                        'melee'        => true,
                        'class'        => 1,
                        'race'         => 1,
                        'command'      => 1,
                    ],
                    [
                        'id'           => '3132614f-e8de-4cc1-a562-31cd29459c33',
                        'name'         => 'Skeleton',
                        'level'        => 1,
                        'avatar'       => '/images/avas/monsters/003.png',
                        'damage'       => 15,
                        'attack_speed' => 1.2,
                        'life'         => 80,
                        'total_life'   => 80,
                        'melee'        => true,
                        'class'        => 1,
                        'race'         => 8,
                        'command'      => 2,
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function successLeftCommandDataProvider(): array
    {
        return [

            [
                // Передаваемые данные по команде
                [
                    [
                        'id'           => '26137d24-426c-4727-ac62-32ae0f9030fb',
                        'name'         => 'Skeleton',
                        'level'        => 1,
                        'avatar'       => '/images/avas/monsters/003.png',
                        'damage'       => 15,
                        'attack_speed' => 1.2,
                        'life'         => 80,
                        'total_life'   => 80,
                        'melee'        => true,
                        'class'        => 1,
                        'race'         => 8,
                        'command'      => 1,
                    ],
                ],
                // Какая команда должна получиться
                [
                    [
                        'id'           => '26137d24-426c-4727-ac62-32ae0f9030fb',
                        'name'         => 'Skeleton',
                        'level'        => 1,
                        'avatar'       => '/images/avas/monsters/003.png',
                        'damage'       => 15,
                        'attack_speed' => 1.2,
                        'life'         => 80,
                        'total_life'   => 80,
                        'melee'        => true,
                        'class'        => 1,
                        'race'         => 8,
                        'command'      => 1,
                    ],
                ],
                BattleInterface::LEFT_COMMAND,
            ],

            [
                // Передаваемые данные по команде
                [
                    [
                        'id'           => '26137d24-426c-4727-ac62-32ae0f9030fb',
                        'name'         => 'Skeleton',
                        'level'        => 1,
                        'avatar'       => '/images/avas/monsters/003.png',
                        'damage'       => 15,
                        'attack_speed' => 1.2,
                        'life'         => 80,
                        'total_life'   => 80,
                        'melee'        => true,
                        'class'        => 1,
                        'race'         => 8,
                        'command'      => 1,
                    ],
                    [
                        'id'           => 'c11d1ac0-5c39-4bf8-bc5f-7d8fdadc7ec3',
                        'name'         => 'Warrior',
                        'level'        => 1,
                        'avatar'       => '/images/avas/monsters/003.png',
                        'damage'       => 15,
                        'attack_speed' => 1.2,
                        'life'         => 80,
                        'total_life'   => 80,
                        'melee'        => true,
                        'class'        => 1,
                        'race'         => 1,
                        'command'      => 2,
                    ],
                ],
                // Какая команда должна получиться
                [
                    [
                        'id'           => '26137d24-426c-4727-ac62-32ae0f9030fb',
                        'name'         => 'Skeleton',
                        'level'        => 1,
                        'avatar'       => '/images/avas/monsters/003.png',
                        'damage'       => 15,
                        'attack_speed' => 1.2,
                        'life'         => 80,
                        'total_life'   => 80,
                        'melee'        => true,
                        'class'        => 1,
                        'race'         => 8,
                        'command'      => 1,
                    ],
                ],
                BattleInterface::LEFT_COMMAND,
            ],

            [
                // Передаваемые данные по команде
                [
                    [
                        'id'           => 'c11d1ac0-5c39-4bf8-bc5f-7d8fdadc7ec3',
                        'name'         => 'Skeleton',
                        'level'        => 1,
                        'avatar'       => '/images/avas/monsters/003.png',
                        'damage'       => 15,
                        'attack_speed' => 1.2,
                        'life'         => 80,
                        'total_life'   => 80,
                        'melee'        => true,
                        'class'        => 1,
                        'race'         => 8,
                        'command'      => 1,
                    ],
                    [
                        'id'           => '99d77ca0-7a8f-47d0-bacc-e297331d89c8',
                        'name'         => 'Warrior',
                        'level'        => 1,
                        'avatar'       => '/images/avas/monsters/003.png',
                        'damage'       => 15,
                        'attack_speed' => 1.2,
                        'life'         => 80,
                        'total_life'   => 80,
                        'melee'        => true,
                        'class'        => 1,
                        'race'         => 1,
                        'command'      => 2,
                    ],
                ],
                // Какая команда должна получиться
                [
                    [
                        'id'           => '99d77ca0-7a8f-47d0-bacc-e297331d89c8',
                        'name'         => 'Warrior',
                        'level'        => 1,
                        'avatar'       => '/images/avas/monsters/003.png',
                        'damage'       => 15,
                        'attack_speed' => 1.2,
                        'life'         => 80,
                        'total_life'   => 80,
                        'melee'        => true,
                        'class'        => 1,
                        'race'         => 1,
                        'command'      => 2,
                    ],
                ],
                BattleInterface::RIGHT_COMMAND,
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
                    'unit_string_data'
                ],
                BattleException::INCORRECT_UNIT_DATA,
            ],
            [
                [
                    [
                        'id'           => '828595c4-66f5-4d1a-867b-9a33693ccee2',
                        'name'         => 'Skeleton',
                        'level'        => 1,
                        'avatar'       => '/images/avas/monsters/003.png',
                        'damage'       => 15,
                        'attack_speed' => 1.2,
                        'life'         => 80,
                        'total_life'   => 80,
                        'melee'        => true,
                        'class'        => 1,
                        'race'         => 8,
                    ],
                ],
                BattleException::NO_COMMAND_PARAMETER
            ],
        ];
    }
}
