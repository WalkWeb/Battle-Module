<?php

declare(strict_types=1);

namespace Tests\Battle;

use Battle\BattleFactory;
use Battle\BattleInterface;
use Battle\Command\CommandException;
use Battle\Command\CommandFactory;
use Battle\Exception\BattleException;
use Exception;
use PHPUnit\Framework\TestCase;

class BattleFactoryTest extends TestCase
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
     * @param string $command
     * @throws BattleException
     * @throws CommandException
     */
    public function testBattleFactoryCreateCommandSuccess(array $data, array $expectedData, string $command): void
    {
        $command = BattleFactory::createCommand($data, $command);
        $expectCommand = CommandFactory::create($expectedData);
        self::assertEquals($expectCommand, $command);
    }

    // todo testBattleFactoryCreateCommandFail

    /**
     * @return array
     */
    public function successDataProvider(): array
    {
        return [
            [
                [
                    [
                        'name'         => 'Skeleton',
                        'avatar'       => '/images/avas/monsters/003.png',
                        'damage'       => 15,
                        'attack_speed' => 1.2,
                        'life'         => 80,
                        'melee'        => true,
                        'class'        => 1,
                        'command'      => 'left',
                    ],
                    [
                        'name'         => 'Skeleton',
                        'avatar'       => '/images/avas/monsters/003.png',
                        'damage'       => 15,
                        'attack_speed' => 1.2,
                        'life'         => 80,
                        'melee'        => true,
                        'class'        => 1,
                        'command'      => 'right',
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
                        'name'         => 'Skeleton',
                        'avatar'       => '/images/avas/monsters/003.png',
                        'damage'       => 15,
                        'attack_speed' => 1.2,
                        'life'         => 80,
                        'melee'        => true,
                        'class'        => 1,
                        'command'      => 'left',
                    ],
                ],
                // Какая команда должна получиться
                [
                    [
                        'name'         => 'Skeleton',
                        'avatar'       => '/images/avas/monsters/003.png',
                        'damage'       => 15,
                        'attack_speed' => 1.2,
                        'life'         => 80,
                        'melee'        => true,
                        'class'        => 1,
                        'command'      => 'left',
                    ],
                ],
                BattleInterface::LEFT_COMMAND,
            ],

            [
                // Передаваемые данные по команде
                [
                    [
                        'name'         => 'Skeleton',
                        'avatar'       => '/images/avas/monsters/003.png',
                        'damage'       => 15,
                        'attack_speed' => 1.2,
                        'life'         => 80,
                        'melee'        => true,
                        'class'        => 1,
                        'command'      => 'left',
                    ],
                    [
                        'name'         => 'Skeleton',
                        'avatar'       => '/images/avas/monsters/003.png',
                        'damage'       => 15,
                        'attack_speed' => 1.2,
                        'life'         => 80,
                        'melee'        => true,
                        'class'        => 1,
                        'command'      => 'right',
                    ],
                ],
                // Какая команда должна получиться
                [
                    [
                        'name'         => 'Skeleton',
                        'avatar'       => '/images/avas/monsters/003.png',
                        'damage'       => 15,
                        'attack_speed' => 1.2,
                        'life'         => 80,
                        'melee'        => true,
                        'class'        => 1,
                        'command'      => 'left',
                    ],
                ],
                BattleInterface::LEFT_COMMAND,
            ],

            [
                // Передаваемые данные по команде
                [
                    [
                        'name'         => 'Skeleton',
                        'avatar'       => '/images/avas/monsters/003.png',
                        'damage'       => 15,
                        'attack_speed' => 1.2,
                        'life'         => 80,
                        'melee'        => true,
                        'class'        => 1,
                        'command'      => 'left',
                    ],
                    [
                        'name'         => 'Skeleton',
                        'avatar'       => '/images/avas/monsters/003.png',
                        'damage'       => 15,
                        'attack_speed' => 1.2,
                        'life'         => 80,
                        'melee'        => true,
                        'class'        => 1,
                        'command'      => 'right',
                    ],
                ],
                // Какая команда должна получиться
                [
                    [
                        'name'         => 'Skeleton',
                        'avatar'       => '/images/avas/monsters/003.png',
                        'damage'       => 15,
                        'attack_speed' => 1.2,
                        'life'         => 80,
                        'melee'        => true,
                        'class'        => 1,
                        'command'      => 'right',
                    ],
                ],
                BattleInterface::RIGHT_COMMAND,
            ],

        ];
    }
}
