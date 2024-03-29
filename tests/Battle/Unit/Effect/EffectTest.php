<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Effect;

use Battle\Action\ActionCollection;
use Battle\Action\ActionException;
use Battle\Action\ActionInterface;
use Battle\Action\DamageAction;
use Battle\Command\CommandFactory;
use Battle\Command\CommandInterface;
use Battle\Unit\Effect\Effect;
use Battle\Unit\Effect\EffectInterface;
use Battle\Unit\UnitInterface;
use Exception;
use Tests\AbstractUnitTest;
use Tests\Factory\BaseFactory;
use Tests\Factory\UnitFactory;

class EffectTest extends AbstractUnitTest
{
    /**
     * @throws ActionException
     */
    public function testEffectCreate(): void
    {
        $name = 'Effect Name';
        $icon = 'path_to_icon.png';
        $duration = 10;
        $onApplyActions = new ActionCollection();
        $onNextRoundActions = new ActionCollection();
        $onDisableActions = new ActionCollection();

        $effect = new Effect($name, $icon, $duration, $onApplyActions, $onNextRoundActions, $onDisableActions);

        self::assertEquals($name, $effect->getName());
        self::assertEquals($icon, $effect->getIcon());
        self::assertEquals($duration, $effect->getDuration());
        self::assertEquals($duration, $effect->getBaseDuration());
        self::assertEquals($onApplyActions, $effect->getOnApplyActions());
        self::assertEquals($onNextRoundActions, $effect->getOnNextRoundActions());
        self::assertEquals($onDisableActions, $effect->getOnDisableActions());

        $effect->nextRound();
        $effect->nextRound();
        $effect->nextRound();

        self::assertEquals($duration - 3, $effect->getDuration());

        $effect->resetDuration();

        self::assertEquals($duration, $effect->getDuration());
    }

    /**
     * @throws Exception
     */
    public function testEffectChangeActionUnit(): void
    {
        [$unit, $command, $enemyCommand, $enemyUnit] = BaseFactory::create(1, 2);

        $effect = $this->createEffect($unit, $command, $enemyCommand);

        foreach ($effect->getOnApplyActions() as $action) {
            self::assertEquals($unit->getId(), $action->getActionUnit()->getId());
        }

        foreach ($effect->getOnNextRoundActions() as $action) {
            self::assertEquals($unit->getId(), $action->getActionUnit()->getId());
        }

        foreach ($effect->getOnDisableActions() as $action) {
            self::assertEquals($unit->getId(), $action->getActionUnit()->getId());
        }

        $effect = $effect->changeActionUnit($enemyUnit);

        foreach ($effect->getOnApplyActions() as $action) {
            self::assertEquals($enemyUnit->getId(), $action->getActionUnit()->getId());
        }

        foreach ($effect->getOnNextRoundActions() as $action) {
            self::assertEquals($enemyUnit->getId(), $action->getActionUnit()->getId());
        }

        foreach ($effect->getOnDisableActions() as $action) {
            self::assertEquals($enemyUnit->getId(), $action->getActionUnit()->getId());
        }
    }

    /**
     * @throws Exception
     */
    public function testEffectChangeMultipleActionUnit(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $secondaryEnemyUnit = UnitFactory::createByTemplate(3);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit, $secondaryEnemyUnit]);

        $action = $this->createIncinerationAction($unit, $enemyCommand, $command);

        // До применения эффекта на противника Action которые будут применяться от эффекта имеют ActionUnit создателя
        foreach ($action->getEffect()->getOnNextRoundActions() as $nextRoundAction) {
            self::assertEquals($unit, $nextRoundAction->getActionUnit());
        }

        self::assertTrue($action->canByUsed());
        $action->handle();

        // После применения эффекта все Action от эффектов привязаны к своим родительским юнитам
        foreach ($enemyUnit->getEffects() as $effect) {
            foreach ($effect->getOnNextRoundActions() as $nextRoundAction) {
                self::assertEquals($enemyUnit->getId(), $nextRoundAction->getActionUnit()->getId());
            }
        }

        foreach ($secondaryEnemyUnit->getEffects() as $effect) {
            foreach ($effect->getOnNextRoundActions() as $nextRoundAction) {
                self::assertEquals($secondaryEnemyUnit->getId(), $nextRoundAction->getActionUnit()->getId());
            }
        }
    }

    /**
     * @param UnitInterface $unit
     * @param CommandInterface $command
     * @param CommandInterface $enemyCommand
     * @return EffectInterface
     * @throws Exception
     */
    private function createEffect(
        UnitInterface $unit,
        CommandInterface $command,
        CommandInterface $enemyCommand
    ): EffectInterface
    {
        $data = [
            'name'                  => 'Effect test #1',
            'icon'                  => 'effect_icon_#1',
            'duration'              => 10,
            'on_apply_actions'      => [
                [
                    'type'             => ActionInterface::DAMAGE,
                    'action_unit'      => $unit,
                    'enemy_command'    => $enemyCommand,
                    'allies_command'   => $command,
                    'type_target'      => ActionInterface::TARGET_SELF,
                    'offense'          => [
                        'damage_type'         => 2,
                        'weapon_type'         => 0,
                        'physical_damage'     => 6,
                        'fire_damage'         => 0,
                        'water_damage'        => 0,
                        'air_damage'          => 0,
                        'earth_damage'        => 0,
                        'life_damage'         => 0,
                        'death_damage'        => 0,
                        'attack_speed'        => 1,
                        'cast_speed'          => 0,
                        'accuracy'            => 500,
                        'magic_accuracy'      => 500,
                        'block_ignoring'      => 0,
                        'critical_chance'     => 0,
                        'critical_multiplier' => 0,
                        'damage_multiplier'   => 100,
                        'vampirism'           => 0,
                        'magic_vampirism'     => 0,
                    ],
                    'can_be_avoided'   => true,
                    'name'             => '',
                    'animation_method' => DamageAction::UNIT_ANIMATION_METHOD,
                    'message_method'   => DamageAction::DEFAULT_MESSAGE_METHOD,
                ],
            ],
            'on_next_round_actions' => [
                [
                    'type'             => ActionInterface::DAMAGE,
                    'action_unit'      => $unit,
                    'enemy_command'    => $enemyCommand,
                    'allies_command'   => $command,
                    'type_target'      => ActionInterface::TARGET_SELF,
                    'offense'          => [
                        'damage_type'         => 2,
                        'weapon_type'         => 0,
                        'physical_damage'     => 6,
                        'fire_damage'         => 0,
                        'water_damage'        => 0,
                        'air_damage'          => 0,
                        'earth_damage'        => 0,
                        'life_damage'         => 0,
                        'death_damage'        => 0,
                        'attack_speed'        => 1,
                        'cast_speed'          => 0,
                        'accuracy'            => 500,
                        'magic_accuracy'      => 500,
                        'block_ignoring'      => 0,
                        'critical_chance'     => 0,
                        'critical_multiplier' => 0,
                        'damage_multiplier'   => 100,
                        'vampirism'           => 0,
                        'magic_vampirism'     => 0,
                    ],
                    'can_be_avoided'   => true,
                    'name'             => '',
                    'animation_method' => DamageAction::UNIT_ANIMATION_METHOD,
                    'message_method'   => DamageAction::DEFAULT_MESSAGE_METHOD,
                ],
            ],
            'on_disable_actions'    => [
                [
                    'type'             => ActionInterface::DAMAGE,
                    'action_unit'      => $unit,
                    'enemy_command'    => $enemyCommand,
                    'allies_command'   => $command,
                    'type_target'      => ActionInterface::TARGET_SELF,
                    'offense'          => [
                        'damage_type'         => 2,
                        'weapon_type'         => 0,
                        'physical_damage'     => 6,
                        'fire_damage'         => 0,
                        'water_damage'        => 0,
                        'air_damage'          => 0,
                        'earth_damage'        => 0,
                        'life_damage'         => 0,
                        'death_damage'        => 0,
                        'attack_speed'        => 1,
                        'cast_speed'          => 0,
                        'accuracy'            => 500,
                        'magic_accuracy'      => 500,
                        'block_ignoring'      => 0,
                        'critical_chance'     => 0,
                        'critical_multiplier' => 0,
                        'damage_multiplier'   => 100,
                        'vampirism'           => 0,
                        'magic_vampirism'     => 0,
                    ],
                    'can_be_avoided'   => true,
                    'name'             => '',
                    'animation_method' => DamageAction::UNIT_ANIMATION_METHOD,
                    'message_method'   => DamageAction::DEFAULT_MESSAGE_METHOD,
                ],
            ],
        ];

        return $this->container->getEffectFactory()->create($data);
    }

    /**
     * @param UnitInterface $actionUnit
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $command
     * @return ActionInterface
     * @throws Exception
     */
    private function createIncinerationAction(
        UnitInterface $actionUnit,
        CommandInterface $enemyCommand,
        CommandInterface $command
    ): ActionInterface
    {
        $data = [
            'type'           => ActionInterface::EFFECT,
            'action_unit'    => $actionUnit,
            'enemy_command'  => $enemyCommand,
            'allies_command' => $command,
            'type_target'    => ActionInterface::TARGET_ALL_ENEMY,
            'name'           => 'Incineration',
            'icon'           => '/images/icons/ability/232.png',
            'message_method' => 'applyEffect',
            'effect'         => [
                'name'                  => 'Incineration',
                'icon'                  => '/images/icons/ability/232.png',
                'duration'              => 8,
                'on_apply_actions'      => [],
                'on_next_round_actions' => [
                    [
                        'type'             => ActionInterface::DAMAGE,
                        'action_unit'      => $actionUnit,
                        'enemy_command'    => $enemyCommand,
                        'allies_command'   => $command,
                        'type_target'      => ActionInterface::TARGET_SELF,
                        'name'             => 'Incineration',
                        'offense'          => [
                            'damage_type'         => 2,
                            'weapon_type'         => 0,
                            'physical_damage'     => 6,
                            'fire_damage'         => 0,
                            'water_damage'        => 0,
                            'air_damage'          => 0,
                            'earth_damage'        => 0,
                            'life_damage'         => 0,
                            'death_damage'        => 0,
                            'attack_speed'        => 1,
                            'cast_speed'          => 0,
                            'accuracy'            => 500,
                            'magic_accuracy'      => 500,
                            'block_ignoring'      => 0,
                            'critical_chance'     => 0,
                            'critical_multiplier' => 0,
                            'damage_multiplier'   => 100,
                            'vampirism'           => 0,
                            'magic_vampirism'     => 0,
                        ],
                        'can_be_avoided'   => true,
                        'animation_method' => DamageAction::EFFECT_ANIMATION_METHOD,
                        'message_method'   => DamageAction::EFFECT_MESSAGE_METHOD,
                        'icon'             => '/images/icons/ability/232.png',
                    ],
                ],
                'on_disable_actions'    => [],
            ],
        ];

        return $this->container->getActionFactory()->create($data);
    }
}
