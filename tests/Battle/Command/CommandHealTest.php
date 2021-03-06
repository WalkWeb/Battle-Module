<?php

declare(strict_types=1);

namespace Tests\Battle\Command;

use Battle\Command\Command;
use Battle\Command\CommandException;
use Battle\Command\CommandFactory;
use Battle\Unit\UnitCollection;
use Battle\Unit\UnitException;
use Exception;
use PHPUnit\Framework\TestCase;
use Tests\Battle\Factory\UnitFactory;

class CommandHealTest extends TestCase
{
    /**
     * @throws CommandException
     * @throws UnitException
     * @throws Exception
     */
    public function testGetUnitForHealNull(): void
    {
        $unit = UnitFactory::createByTemplate(10);

        $unitCollection = new UnitCollection();
        $unitCollection->add($unit);
        $command = new Command($unitCollection);

        self::assertNull($command->getUnitForHeal());
    }

    /**
     * Тест на выбор самого раненого юнита из команды для лечения
     *
     * @dataProvider unitDataProvider
     * @param array $data
     * @param string $unitIdForHeal
     * @throws CommandException
     * @throws UnitException
     */
    public function testGetMostWoundedUnitForHeal(array $data, string $unitIdForHeal): void
    {
        $command = CommandFactory::create($data);
        self::assertEquals($unitIdForHeal, $command->getUnitForHeal()->getId());
    }

    /**
     * @return array
     */
    public function unitDataProvider(): array
    {
        return [
            [
                [
                    [
                        'id'           => '63ad76c6-6a11-44ef-997b-fea1778bebe5',
                        'name'         => 'Warrior',
                        'level'        => 1,
                        'avatar'       => 'url avatar 1',
                        'damage'       => 15,
                        'attack_speed' => 1.2,
                        'life'         => 70,
                        'total_life'   => 80,
                        'melee'        => true,
                        'class'        => 1,
                        'race'         => 1,
                        'command'      => 1,
                    ],
                    // Самый битый юнит - именно он должен выбираться для лечения
                    [
                        'id'           => 'fb8be211-0782-4c60-8865-68b177ffbe0c',
                        'name'         => 'Knight',
                        'level'        => 1,
                        'avatar'       => 'url avatar 2',
                        'damage'       => 11,
                        'attack_speed' => 0.9,
                        'life'         => 12,
                        'total_life'   => 115,
                        'melee'        => false,
                        'class'        => 1,
                        'race'         => 1,
                        'command'      => 1,
                    ],
                    [
                        'id'           => 'fb8be211-0782-4c60-8865-68b177ffbedc',
                        'name'         => 'Archer',
                        'level'        => 1,
                        'avatar'       => 'url avatar 3',
                        'damage'       => 11,
                        'attack_speed' => 0.9,
                        'life'         => 50,
                        'total_life'   => 75,
                        'melee'        => false,
                        'class'        => 2,
                        'race'         => 1,
                        'command'      => 1,
                    ],
                    // Мертвый юнит - он выбираться не должен
                    [
                        'id'           => 'fb8be211-0782-4c60-8865-68b177ffbe3c',
                        'name'         => 'Priest',
                        'level'        => 1,
                        'avatar'       => 'url avatar 3',
                        'damage'       => 11,
                        'attack_speed' => 0.9,
                        'life'         => 0,
                        'total_life'   => 60,
                        'melee'        => false,
                        'class'        => 2,
                        'race'         => 1,
                        'command'      => 1,
                    ],
                ],
                'fb8be211-0782-4c60-8865-68b177ffbe0c',
            ],
        ];
    }
}
