<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Classes\Demon;

use Battle\Action\ActionInterface;
use Battle\Action\DamageAction;
use Battle\Command\CommandFactory;
use Battle\Command\CommandInterface;
use Battle\Container\Container;
use Battle\Container\ContainerInterface;
use Battle\Unit\Ability\Ability;
use Battle\Unit\Effect\EffectFactory;
use Battle\Unit\Effect\EffectInterface;
use Battle\Unit\UnitInterface;
use Exception;
use Tests\AbstractUnitTest;
use Tests\Battle\Factory\UnitFactory;

class SuccubusTest extends AbstractUnitTest
{
    /**
     * Тест на создание класса Succubus
     *
     * @throws Exception
     */
    public function testSuccubusCreate(): void
    {
        $container = new Container();
        $unit = UnitFactory::createByTemplate(23, $container);
        $enemyUnit = UnitFactory::createByTemplate(1, $container);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $succubus = $unit->getClass();

        self::assertEquals(7, $succubus->getId());
        self::assertEquals('Succubus', $succubus->getName());
        self::assertEquals('/images/icons/small/dark-mage.png', $succubus->getSmallIcon());

        $abilities = $succubus->getAbilities($unit);

        foreach ($abilities as $i => $ability) {

            if ($i === 0) {
                self::assertContainsOnlyInstancesOf(Ability::class, [$ability]);

                $actions = $ability->getActions($enemyCommand, $command);

                foreach ($actions as $action) {
                    self::assertEquals(
                        $this->createEffectPoison($container, $unit, $enemyCommand, $command),
                        $action->getEffect()
                    );
                }
            }

            if ($i === 1) {
                self::assertContainsOnlyInstancesOf(Ability::class, [$ability]);

                $actions = $ability->getActions($enemyCommand, $command);

                foreach ($actions as $action) {
                    self::assertEquals(
                        $this->createEffectParalysis($container, $unit, $enemyCommand, $command),
                        $action->getEffect()
                    );
                }
            }
        }
    }

    /**
     * @throws Exception
     */
    public function testSuccubusReadyAbility(): void
    {
        $unit = UnitFactory::createByTemplate(23);
        $woundedUnit = UnitFactory::createByTemplate(11);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit, $woundedUnit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        for ($i = 0; $i < 30; $i++) {
            $unit->newRound();
        }

        foreach ($unit->getAbilities() as $ability) {
            self::assertTrue($ability->isReady());
            self::assertTrue($ability->canByUsed($enemyCommand, $command));
        }
    }

    /**
     * @param ContainerInterface $container
     * @param UnitInterface $unit
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $command
     * @return EffectInterface
     * @throws Exception
     */
    private function createEffectPoison(
        ContainerInterface $container,
        UnitInterface $unit,
        CommandInterface $enemyCommand,
        CommandInterface $command
    ): EffectInterface
    {
        $effectFactory = new EffectFactory($container->getActionFactory());

        $data = [
            'name'                  => 'Poison',
            'icon'                  => '/images/icons/ability/202.png',
            'duration'              => 5,
            'on_apply_actions'      => [],
            'on_next_round_actions' => [
                [
                    'type'             => ActionInterface::DAMAGE,
                    'action_unit'      => $unit,
                    'enemy_command'    => $enemyCommand,
                    'allies_command'   => $command,
                    'type_target'      => ActionInterface::TARGET_SELF,
                    'name'             => 'Poison',
                    'offense'          => [
                        'type_damage'         => 2,
                        'weapon_type'         => 0,
                        'physical_damage'     => 8,
                        'attack_speed'        => 1,
                        'accuracy'            => 500,
                        'magic_accuracy'      => 500,
                        'block_ignore'        => 0,
                        'critical_chance'     => 0,
                        'critical_multiplier' => 0,
                        'vampire'             => 0,
                    ],
                    'can_be_avoided'   => false,
                    'animation_method' => DamageAction::EFFECT_ANIMATION_METHOD,
                    'message_method'   => DamageAction::EFFECT_MESSAGE_METHOD,
                    'icon'             => '/images/icons/ability/202.png',
                ],
            ],
            'on_disable_actions'    => [],
        ];

        return $effectFactory->create($data);
    }

    /**
     * @param ContainerInterface $container
     * @param UnitInterface $unit
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $command
     * @return EffectInterface
     * @throws Exception
     */
    private function createEffectParalysis(
        ContainerInterface $container,
        UnitInterface $unit,
        CommandInterface $enemyCommand,
        CommandInterface $command
    ): EffectInterface
    {
        $effectFactory = new EffectFactory($container->getActionFactory());

        $data = [
            'name'                  => 'Paralysis',
            'icon'                  => '/images/icons/ability/086.png',
            'duration'              => 2,
            'on_apply_actions'      => [],
            'on_next_round_actions' => [
                [
                    'type'             => ActionInterface::PARALYSIS,
                    'action_unit'      => $unit,
                    'enemy_command'    => $enemyCommand,
                    'allies_command'   => $command,
                    'type_target'      => ActionInterface::TARGET_SELF,
                    'name'             => 'Paralysis',
                    'can_be_avoided'   => false,
                    'animation_method' => DamageAction::EFFECT_ANIMATION_METHOD,
                    'message_method'   => DamageAction::EFFECT_MESSAGE_METHOD,
                    'icon'             => '/images/icons/ability/086.png',
                ],
            ],
            'on_disable_actions'    => [],
        ];

        return $effectFactory->create($data);
    }
}
