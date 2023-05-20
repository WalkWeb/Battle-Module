<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Classes\Demon;

use Battle\Action\ActionInterface;
use Battle\Action\DamageAction;
use Battle\Action\ParalysisAction;
use Battle\Command\CommandFactory;
use Battle\Command\CommandInterface;
use Battle\Container\Container;
use Battle\Container\ContainerInterface;
use Battle\Unit\Ability\Ability;
use Battle\Unit\Effect\EffectInterface;
use Battle\Unit\UnitInterface;
use Exception;
use Tests\AbstractUnitTest;
use Tests\Factory\UnitFactory;

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
                        'damage_type'         => 2,
                        'weapon_type'         => 0,
                        'physical_damage'     => 0,
                        'fire_damage'         => 0,
                        'water_damage'        => 0,
                        'air_damage'          => 0,
                        'earth_damage'        => 0,
                        'life_damage'         => 0,
                        'death_damage'        => 8,
                        'attack_speed'        => 0,
                        'cast_speed'          => 1,
                        'accuracy'            => 500,
                        'magic_accuracy'      => 500,
                        'block_ignoring'      => 0,
                        'critical_chance'     => 0,
                        'critical_multiplier' => 0,
                        'damage_multiplier'   => 100,
                        'vampirism'           => 0,
                        'magic_vampirism'     => 0,
                    ],
                    'can_be_avoided'   => false,
                    'animation_method' => DamageAction::EFFECT_ANIMATION_METHOD,
                    'message_method'   => DamageAction::EFFECT_MESSAGE_METHOD,
                    'icon'             => '/images/icons/ability/202.png',
                    'target_tracking'  => false,
                ],
            ],
            'on_disable_actions'    => [],
        ];

        return $container->getEffectFactory()->create($data);
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
                    'message_method'   => ParalysisAction::PARALYSIS_MESSAGE_METHOD,
                    'icon'             => '/images/icons/ability/086.png',
                    'target_tracking'  => false,
                ],
            ],
            'on_disable_actions'    => [],
        ];

        return $container->getEffectFactory()->create($data);
    }
}
