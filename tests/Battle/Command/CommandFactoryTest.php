<?php

declare(strict_types=1);

namespace Tests\Battle\Command;

use Battle\Result\FullLog\FullLog;
use Battle\Unit\Classes\UnitClassFactory;
use Battle\Unit\Classes\ClassFactoryException;
use Battle\Command\CommandException;
use Battle\Command\CommandFactory;
use Battle\Unit\UnitException;
use Exception;
use Tests\AbstractUnitTest;
use Tests\Battle\Factory\UnitFactory;

class CommandFactoryTest extends AbstractUnitTest
{
    /**
     * @dataProvider successDataProvider
     * @param array $data
     * @throws ClassFactoryException
     * @throws CommandException
     * @throws UnitException
     */
    public function testCommandFactoryCreateFromDataSuccess(array $data): void
    {
        $command = CommandFactory::create($data);

        self::assertSameSize($data, $command->getUnits());

        $i = 0;
        foreach ($command->getUnits() as $unit) {
            $class = UnitClassFactory::create($data[$i]['class']);

            self::assertEquals($data[$i]['name'], $unit->getName());
            self::assertEquals($data[$i]['level'], $unit->getLevel());
            self::assertEquals($data[$i]['avatar'], $unit->getAvatar());
            self::assertEquals($data[$i]['damage'], $unit->getDamage());
            self::assertEquals($data[$i]['attack_speed'], $unit->getAttackSpeed());
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
                        'id'           => '63ad76c6-6a11-44ef-997b-fea1778bebe5',
                        'name'         => 'Skeleton',
                        'level'        => 3,
                        'avatar'       => 'url avatar 1',
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
                        'id'           => 'fb8be211-0782-4c60-8865-68b177ffbe0c',
                        'name'         => 'Ghost',
                        'level'        => 12,
                        'avatar'       => 'url avatar 2',
                        'damage'       => 11,
                        'attack_speed' => 0.9,
                        'life'         => 75,
                        'total_life'   => 75,
                        'melee'        => false,
                        'class'        => 2,
                        'race'         => 8,
                        'command'      => 1,
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
                        'id'           => 'f83b1152-b186-4a17-a3dd-88ac75e3cd23',
                        'level'        => 1,
                        'avatar'       => 'url avatar 1',
                        'damage'       => 15,
                        'attack_speed' => 1.2,
                        'life'         => 80,
                        'total_life'   => 80,
                        'melee'        => true,
                        'class'        => 1,
                        'race'         => 1,
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
}
