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
                        'id'           => 'd8eee3b4-8c0e-438d-b3ea-71af9190b6c3',
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
                        'id'           => '974968e3-5599-4a72-bf85-3199fc8ff0ae',
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
                        'id'           => '3c018373-efb5-4f21-91b9-918d5852f288',
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
                        'id'           => '0470ea73-3cc0-434d-9f1d-441cdb9f9e26',
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
                '974968e3-5599-4a72-bf85-3199fc8ff0ae',
            ],
        ];
    }
}
