<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Classes\Orcs;

use Battle\Action\ActionCollection;
use Battle\Action\ActionFactory;
use Battle\Action\ActionInterface;
use Battle\Command\CommandInterface;
use Battle\Unit\UnitInterface;
use Exception;
use PHPUnit\Framework\TestCase;
use Battle\Command\CommandFactory;
use Tests\Battle\Factory\UnitFactory;
use Battle\Unit\Classes\UnitClassInterface;
use Battle\Unit\Ability\Effect\ReserveForcesAbility;

class TitanTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testCreateTitanClass(): void
    {
        $unit = UnitFactory::createByTemplate(21);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $class = $unit->getClass();

        self::assertEquals(UnitClassInterface::TITAN_ID, $class->getId());
        self::assertEquals(UnitClassInterface::TITAN_NAME, $class->getName());
        self::assertEquals(UnitClassInterface::TITAN_SMALL_ICON, $class->getSmallIcon());

        $abilities = $class->getAbilities($unit);

        foreach ($abilities as $ability) {
            self::assertContainsOnlyInstancesOf(ReserveForcesAbility::class, [$ability]);

            self::assertEquals(
                $this->getReserveForcesActions($unit, $enemyCommand, $command),
                $ability->getAction($enemyCommand, $command)
            );
        }
    }

    /**
     * @param UnitInterface $unit
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $alliesCommand
     * @return ActionCollection
     * @throws Exception
     */
    private function getReserveForcesActions(
        UnitInterface $unit,
        CommandInterface $enemyCommand,
        CommandInterface $alliesCommand
    ): ActionCollection
    {
        $collection = new ActionCollection();
        $factory = new ActionFactory();

        $data = [
            'type'           => ActionInterface::EFFECT,
            'action_unit'    => $unit,
            'enemy_command'  => $enemyCommand,
            'allies_command' => $alliesCommand,
            'type_target'    => ActionInterface::TARGET_SELF,
            'name'           => 'use Reserve Forces',
            'effects'        => [
                [
                    'name'                  => 'Reserve Forces',
                    'icon'                  => '/images/icons/ability/156.png',
                    'duration'              => 6,
                    'on_apply_actions'      => [
                        [
                            'type'           => ActionInterface::BUFF,
                            'action_unit'    => $unit,
                            'enemy_command'  => $enemyCommand,
                            'allies_command' => $alliesCommand,
                            'type_target'    => ActionInterface::TARGET_SELF,
                            'name'           => 'use Reserve Forces',
                            'modify_method'  => 'multiplierMaxLife',
                            'power'          => 130,
                        ],
                    ],
                    'on_next_round_actions' => [],
                    'on_disable_actions'    => [],
                ],
            ],
        ];

        $collection->add($factory->create($data));

        return $collection;
    }
}
