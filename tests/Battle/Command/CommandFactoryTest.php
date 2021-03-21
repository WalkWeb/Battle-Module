<?php

declare(strict_types=1);

namespace Tests\Battle\Command;

use Battle\Chat\Chat;
use Battle\Classes\UnitClassFactory;
use Battle\Classes\ClassFactoryException;
use Battle\Command\CommandException;
use Battle\Command\CommandFactory;
use Battle\Unit\UnitException;
use PHPUnit\Framework\TestCase;
use Tests\Battle\Factory\UnitFactory;
use Tests\Battle\Factory\UnitFactoryException;

class CommandFactoryTest extends TestCase
{
    /**
     * @dataProvider successDataProvider
     * @param array $data
     * @throws ClassFactoryException
     * @throws CommandException
     */
    public function testCommandFactoryCreateFromDataSuccess(array $data): void
    {
        $command = CommandFactory::create($data);

        self::assertSameSize($data, $command->getUnits());

        $i = 0;
        foreach ($command->getUnits() as $unit) {
            $class = UnitClassFactory::create($data[$i]['class']);

            self::assertEquals($data[$i]['name'], $unit->getName());
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
     * @throws CommandException
     */
    public function testCommandFactoryCreateFromDataFail(array $data): void
    {
        $this->expectException(CommandException::class);
        $this->expectErrorMessage(UnitException::INCORRECT_NAME . ' (1 element)');
        CommandFactory::create($data);
    }

    /**
     * @throws ClassFactoryException
     * @throws CommandException
     * @throws UnitFactoryException
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
     */
    public function testCommandFactoryCreateFromUsersFail(): void
    {
        $unit = new Chat();
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
                        'name'         => 'Skeleton',
                        'avatar'       => 'url avatar 1',
                        'damage'       => 15,
                        'attack_speed' => 1.2,
                        'life'         => 80,
                        'melee'        => true,
                        'class'        => 1,
                    ],
                    [
                        'name'         => 'Ghost',
                        'avatar'       => 'url avatar 2',
                        'damage'       => 11,
                        'attack_speed' => 0.9,
                        'life'         => 75,
                        'melee'        => false,
                        'class'        => 2,
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
                    [
                        // отсутствует name
                        'avatar'       => 'url avatar 1',
                        'damage'       => 15,
                        'attack_speed' => 1.2,
                        'life'         => 80,
                        'melee'        => true,
                        'class'        => 1,
                    ],
                ],
            ],
        ];
    }
}
