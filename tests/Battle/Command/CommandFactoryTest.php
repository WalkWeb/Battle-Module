<?php

declare(strict_types=1);

namespace Tests\Battle\Command;

use Battle\Classes\ClassFactory;
use Battle\Classes\ClassFactoryException;
use Battle\Command\CommandException;
use Battle\Command\CommandFactory;
use PHPUnit\Framework\TestCase;

class CommandFactoryTest extends TestCase
{
    /**
     * @dataProvider successDataProvider
     * @param array $data
     * @throws ClassFactoryException
     * @throws CommandException
     */
    public function testCommandFactorySuccess(array $data): void
    {
        $command = CommandFactory::create($data);

        self::assertSameSize($data, $command->getUnits());

        $i = 0;
        foreach ($command->getUnits() as $unit) {
            $class = ClassFactory::create($data[$i]['class']);

            self::assertEquals($data[$i]['name'], $unit->getName());
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
     * @return array
     */
    public function successDataProvider(): array
    {
        return [
            [
                [
                    [
                        'name'         => 'Skeleton',
                        'damage'       => 15,
                        'attack_speed' => 1.2,
                        'life'         => 80,
                        'melee'        => true,
                        'class'        => 1,
                    ],
                    [
                        'name'         => 'Ghost',
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
}
