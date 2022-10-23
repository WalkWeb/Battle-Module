<?php

declare(strict_types=1);

namespace Tests\Battle\Action;

use Battle\Action\ActionCollection;
use Battle\Action\ActionException;
use Battle\Action\ActionInterface;
use Battle\Action\DamageAction;
use Battle\Action\EffectAction;
use Battle\Command\CommandFactory;
use Battle\Command\CommandInterface;
use Battle\Container\Container;
use Battle\Container\ContainerInterface;
use Battle\Unit\Effect\Effect;
use Battle\Unit\Effect\EffectCollection;
use Battle\Unit\Effect\EffectFactory;
use Battle\Unit\Effect\EffectInterface;
use Battle\Unit\UnitInterface;
use Battle\Weapon\Type\WeaponTypeInterface;
use Exception;
use Tests\AbstractUnitTest;
use Tests\Battle\Factory\UnitFactory;

class EffectActionTest extends AbstractUnitTest
{
    /**
     * @throws Exception
     */
    public function testEffectActionCreate(): void
    {
        $container = new Container();
        $unit = UnitFactory::createByTemplate(1, $container);
        $enemyUnit = UnitFactory::createByTemplate(2, $container);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $name = 'use Reserve Forces';
        $action = $this->getReserveForcesAction($container, $unit, $enemyCommand, $command, EffectAction::TARGET_SELF);
        $effect = $this->getReserveForcesEffect($container, $unit, $enemyCommand, $command);

        self::assertEquals('applyEffectAction', $action->getHandleMethod());
        self::assertEquals('effect', $action->getAnimationMethod());
        self::assertEquals('applyEffect', $action->getMessageMethod());
        self::assertEquals($effect, $action->getEffect());
        self::assertEquals($name, $action->getNameAction());
    }

    /**
     * @throws Exception
     */
    public function testEffectActionApply(): void
    {
        $container = new Container();
        $unit = UnitFactory::createByTemplate(1, $container);
        $enemyUnit = UnitFactory::createByTemplate(2, $container);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $unitBaseLife = $unit->getTotalLife();

        $effectAction = $this->getReserveForcesAction($container, $unit, $enemyCommand, $command, EffectAction::TARGET_SELF);

        // Пока эффекта на юните нет - событие может примениться
        self::assertTrue($effectAction->canByUsed());

        $effectAction->handle();

        // А вот когда эффект наложен - уже нет
        self::assertFalse($effectAction->canByUsed());

        $effects = new EffectCollection($unit);
        $effects->add($effectAction->getEffect());

        // Проверяем увеличившееся здоровье
        self::assertEquals((int)($unitBaseLife * 1.3), $unit->getTotalLife());
        self::assertEquals((int)($unitBaseLife * 1.3), $unit->getLife());

        // Применяем эффект еще раз, и проверяем, что здоровье еще раз не увеличилось
        $effectAction->handle();

        self::assertEquals((int)($unitBaseLife * 1.3), $unit->getTotalLife());
        self::assertEquals((int)($unitBaseLife * 1.3), $unit->getLife());

        // Обновляем длительность эффектов. Длительность эффектов обновляется в getAfterActions()
        for ($i = 0; $i < 10; $i++) {
            foreach ($unit->getAfterActions() as $afterAction) {
                if ($afterAction->canByUsed()) {
                    $afterAction->handle();
                }
            }
        }

        self::assertEquals($unitBaseLife, $unit->getTotalLife());
        self::assertEquals($unitBaseLife, $unit->getLife());
        self::assertCount(0, $unit->getEffects());

        // Проверяем, что эффект опять готов примениться
        self::assertTrue($effectAction->canByUsed());
    }

    /**
     * @throws Exception
     */
    public function testEffectActionNoTargetForEffect(): void
    {
        $container = new Container();
        $unit = UnitFactory::createByTemplate(1, $container);
        $enemyUnit = UnitFactory::createByTemplate(10, $container);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = $this->getReserveForcesAction($container, $unit, $enemyCommand, $command, EffectAction::TARGET_RANDOM_ENEMY);

        // При вызове canByUsed() происходит поиск цели
        self::assertFalse($action->canByUsed());

        $this->expectException(ActionException::class);
        $this->expectExceptionMessage(ActionException::NO_TARGET_FOR_EFFECT);
        $action->handle();
    }

    /**
     * @throws Exception
     */
    public function testEffectActionMessageTo(): void
    {
        $container = new Container();
        $unit = UnitFactory::createByTemplate(1, $container);
        $enemyUnit = UnitFactory::createByTemplate(2, $container);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = $this->getReserveForcesAction($container, $unit, $enemyCommand, $command, EffectAction::TARGET_RANDOM_ENEMY);

        // При вызове canByUsed() происходит поиск цели
        self::assertTrue($action->canByUsed());
        $action->handle();
    }

    /**
     * Тест на применение эффекта на случайного противника не имеющего данного эффекта
     *
     * @throws Exception
     */
    public function testEffectActionTargetEffectEnemy(): void
    {
        $container = new Container();
        $unit = UnitFactory::createByTemplate(1, $container);
        $enemyUnit = UnitFactory::createByTemplate(2, $container);
        $otherEnemyUnit = UnitFactory::createByTemplate(10);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit, $otherEnemyUnit]);

        $action = $this->getPoisonActionToEnemyTarget($container, $unit, $enemyCommand, $command, ActionInterface::TARGET_EFFECT_ENEMY);

        self::assertTrue($action->canByUsed());

        $action->handle();

        // Мертвый вражеский юнит не должен получить эффекта
        self::assertEquals(new EffectCollection($otherEnemyUnit), $otherEnemyUnit->getEffects());

        $effects = new EffectCollection($enemyUnit);
        $effects->add($action->getEffect());

        // А вот живой вражеский юнит должен теперь иметь эффект
        self::assertEquals($effects, $enemyUnit->getEffects());
    }

    /**
     * Тест аналогичен testEffectActionTargetEffectEnemy(), только на этот раз выбирается случайный союзный юнит
     *
     * @throws Exception
     */
    public function testEffectActionTargetEffectAllies(): void
    {
        $container = new Container();
        $unit = UnitFactory::createByTemplate(1, $container);
        $otherUnit = UnitFactory::createByTemplate(10, $container);
        $enemyUnit = UnitFactory::createByTemplate(2, $container);
        $command = CommandFactory::create([$unit, $otherUnit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = $this->getPoisonActionToEnemyTarget($container, $unit, $enemyCommand, $command, ActionInterface::TARGET_EFFECT_ALLIES);

        self::assertTrue($action->canByUsed());

        $action->handle();

        // Мертвый союзный юнит не должен получить эффекта
        self::assertEquals(new EffectCollection($otherUnit), $otherUnit->getEffects());

        $effects = new EffectCollection($unit);
        $effects->add($action->getEffect());

        // А вот живой союзный юнит должен теперь иметь эффект
        self::assertEquals($effects, $unit->getEffects());
    }

    /**
     * Проверка дефолтных параметров $animationMethod и $messageMethod
     *
     * @throws Exception
     */
    public function testEffectActionDefaultValue(): void
    {
        $container = new Container();
        $unit = UnitFactory::createByTemplate(1, $container);
        $enemyUnit = UnitFactory::createByTemplate(2, $container);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $name = 'use Reserve Forces';
        $icon = 'icon.png';
        $duration = 10;

        $action = $this->getReserveForcesAction($container, $unit, $enemyCommand, $command, EffectAction::TARGET_SELF);

        $actionCollection = new ActionCollection();
        $actionCollection->add($action);

        $effect = new Effect($name, $icon, $duration, new ActionCollection(), $actionCollection, new ActionCollection());

        $effectAction = new EffectAction(
            $this->getContainer(),
            $unit,
            $enemyCommand,
            $command,
            EffectAction::TARGET_SELF,
            $name,
            $icon,
            $effect
        );

        self::assertEquals(EffectAction::DEFAULT_ANIMATION_METHOD, $effectAction->getAnimationMethod());
        self::assertEquals(EffectAction::DEFAULT_MESSAGE_METHOD, $effectAction->getMessageMethod());
    }

    /**
     * Проверка пользовательских параметров $animationMethod и $messageMethod
     *
     * @throws Exception
     */
    public function testEffectActionCustomValue(): void
    {
        $container = new Container();
        $unit = UnitFactory::createByTemplate(1, $container);
        $enemyUnit = UnitFactory::createByTemplate(2, $container);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $name = 'use Reserve Forces';
        $icon = 'icon.png';
        $duration = 10;

        $animationMethod = 'custom_animate_method';
        $messageMethod = 'custom_message_method';

        $action = $this->getReserveForcesAction($container, $unit, $enemyCommand, $command, EffectAction::TARGET_SELF);

        $actionCollection = new ActionCollection();
        $actionCollection->add($action);

        $effect = new Effect($name, $icon, $duration, new ActionCollection(), $actionCollection, new ActionCollection());

        $effectAction = new EffectAction(
            $this->getContainer(),
            $unit,
            $enemyCommand,
            $command,
            EffectAction::TARGET_SELF,
            $name,
            $icon,
            $effect,
            $animationMethod,
            $messageMethod
        );

        self::assertEquals($animationMethod, $effectAction->getAnimationMethod());
        self::assertEquals($messageMethod, $effectAction->getMessageMethod());
    }

    /**
     * Создает и возвращает EffectAction
     *
     * @param ContainerInterface $container
     * @param UnitInterface $unit
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $command
     * @param int $typeTarget
     * @return ActionInterface
     * @throws Exception
     */
    private function getReserveForcesAction(
        ContainerInterface $container,
        UnitInterface $unit,
        CommandInterface $enemyCommand,
        CommandInterface $command,
        int $typeTarget
    ): ActionInterface
    {
        $data = [
            'type'           => ActionInterface::EFFECT,
            'action_unit'    => $unit,
            'enemy_command'  => $enemyCommand,
            'allies_command' => $command,
            'type_target'    => $typeTarget,
            'name'           => 'use Reserve Forces',
            'effect'         => [
                'name'                  => 'Effect#123',
                'icon'                  => 'icon.png',
                'duration'              => 8,
                'on_apply_actions'      => [
                    [
                        'type'           => ActionInterface::BUFF,
                        'action_unit'    => $unit,
                        'enemy_command'  => $enemyCommand,
                        'allies_command' => $command,
                        'type_target'    => ActionInterface::TARGET_SELF,
                        'name'           => 'use Reserve Forces',
                        'modify_method'  => 'multiplierMaxLife',
                        'power'          => 130,
                    ],
                ],
                'on_next_round_actions' => [],
                'on_disable_actions'    => [],
            ],
        ];

        return $container->getActionFactory()->create($data);
    }

    /**
     * @param ContainerInterface $container
     * @param UnitInterface $unit
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $command
     * @return EffectInterface
     * @throws Exception
     */
    public function getReserveForcesEffect(
        ContainerInterface $container,
        UnitInterface $unit,
        CommandInterface $enemyCommand,
        CommandInterface $command
    ): EffectInterface
    {
        $effectFactory = new EffectFactory($container->getActionFactory());

        $data = [
            'name'                  => 'Effect#123',
            'icon'                  => 'icon.png',
            'duration'              => 8,
            'on_apply_actions'      => [
                [
                    'type'           => ActionInterface::BUFF,
                    'action_unit'    => $unit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'type_target'    => ActionInterface::TARGET_SELF,
                    'name'           => 'use Reserve Forces',
                    'modify_method'  => 'multiplierMaxLife',
                    'power'          => 130,
                ],
            ],
            'on_next_round_actions' => [],
            'on_disable_actions'    => [],
        ];

        return $effectFactory->create($data);
    }

    /**
     * @param ContainerInterface $container
     * @param UnitInterface $unit
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $command
     * @param int $typeTarget
     * @return ActionInterface
     * @throws Exception
     */
    public function getPoisonActionToEnemyTarget(
        ContainerInterface $container,
        UnitInterface $unit,
        CommandInterface $enemyCommand,
        CommandInterface $command,
        int $typeTarget
    ): ActionInterface
    {
        $data = [
            'type'           => ActionInterface::EFFECT,
            'action_unit'    => $unit,
            'enemy_command'  => $enemyCommand,
            'allies_command' => $command,
            'type_target'    => $typeTarget,
            'name'           => 'use Poison',
            'effect'         => [
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
                            'weapon_type'         => WeaponTypeInterface::NONE,
                            'physical_damage'     => 8,
                            'fire_damage'         => 0,
                            'water_damage'        => 0,
                            'air_damage'          => 0,
                            'earth_damage'        => 0,
                            'life_damage'         => 0,
                            'death_damage'        => 0,
                            'attack_speed'        => 0,
                            'cast_speed'          => 1,
                            'accuracy'            => 500,
                            'magic_accuracy'      => 500,
                            'block_ignoring'      => 0,
                            'critical_chance'     => 0,
                            'critical_multiplier' => 0,
                            'vampirism'           => 0,
                        ],
                        'can_be_avoided'   => false,
                        'animation_method' => DamageAction::EFFECT_ANIMATION_METHOD,
                        'message_method'   => DamageAction::EFFECT_MESSAGE_METHOD,
                    ],
                ],
                'on_disable_actions'    => [],
            ],
        ];

        return $container->getActionFactory()->create($data);
    }
}
