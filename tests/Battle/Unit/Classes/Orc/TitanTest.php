<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Classes\Orc;

use Battle\Action\ActionInterface;
use Battle\Command\CommandInterface;
use Battle\Container\Container;
use Battle\Container\ContainerInterface;
use Battle\Unit\Ability\Ability;
use Battle\Unit\Effect\EffectFactory;
use Battle\Unit\Effect\EffectInterface;
use Battle\Unit\UnitInterface;
use Exception;
use Battle\Command\CommandFactory;
use Tests\AbstractUnitTest;
use Tests\Factory\UnitFactory;

class TitanTest extends AbstractUnitTest
{
    /**
     * @throws Exception
     */
    public function testCreateTitanClass(): void
    {
        $container = new Container();
        $unit = UnitFactory::createByTemplate(21, $container);
        $enemyUnit = UnitFactory::createByTemplate(2, $container);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $class = $unit->getClass();

        self::assertEquals(5, $class->getId());
        self::assertEquals('Titan', $class->getName());
        self::assertEquals('/images/icons/small/titan.png', $class->getSmallIcon());

        $abilities = $class->getAbilities($unit);

        foreach ($abilities as $i => $ability) {

            if ($i === 0) {
                self::assertContainsOnlyInstancesOf(Ability::class, [$ability]);

                $actions = $ability->getActions($enemyCommand, $command);

                foreach ($actions as $action) {
                    self::assertEquals(
                        $this->createReserveForcesEffect($container, $unit, $enemyCommand, $command),
                        $action->getEffect()
                    );
                }
            }

            if ($i === 1) {
                self::assertContainsOnlyInstancesOf(Ability::class, [$ability]);

                $actions = $ability->getActions($enemyCommand, $command);

                foreach ($actions as $action) {
                    self::assertEquals(
                        $this->createBattleFuryEffect($container, $unit, $enemyCommand, $command),
                        $action->getEffect()
                    );
                }
            }
        }
    }

    /**
     * @param ContainerInterface $container
     * @param UnitInterface $unit
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $alliesCommand
     * @return EffectInterface
     * @throws Exception
     */
    private function createReserveForcesEffect(
        ContainerInterface $container,
        UnitInterface $unit,
        CommandInterface $enemyCommand,
        CommandInterface $alliesCommand
    ): EffectInterface
    {
        $factory = new EffectFactory($container->getActionFactory());

        $data = [
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
                    'name'           => 'Reserve Forces',
                    'modify_method'  => 'multiplierMaxLife',
                    'power'          => 130,
                    'message_method' => ActionInterface::SKIP_MESSAGE_METHOD,
                ],
            ],
            'on_next_round_actions' => [],
            'on_disable_actions'    => [],
        ];

        return $factory->create($data);
    }

    /**
     * @param ContainerInterface $container
     * @param UnitInterface $unit
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $alliesCommand
     * @return EffectInterface
     * @throws Exception
     */
    private function createBattleFuryEffect(
        ContainerInterface $container,
        UnitInterface $unit,
        CommandInterface $enemyCommand,
        CommandInterface $alliesCommand
    ): EffectInterface
    {
        $factory = new EffectFactory($container->getActionFactory());

        $data = [
            'name'                  => 'Battle Fury',
            'icon'                  => '/images/icons/ability/102.png',
            'duration'              => 15,
            'on_apply_actions'      => [
                [
                    'type'           => ActionInterface::BUFF,
                    'action_unit'    => $unit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $alliesCommand,
                    'type_target'    => ActionInterface::TARGET_SELF,
                    'name'           => 'Battle Fury',
                    'modify_method'  => 'multiplierAttackSpeed',
                    'power'          => 140,
                    'message_method' => ActionInterface::SKIP_MESSAGE_METHOD,
                ],
            ],
            'on_next_round_actions' => [],
            'on_disable_actions'    => [],
        ];

        return $factory->create($data);
    }
}
