<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Ability\Effect;

use Battle\Action\ActionCollection;
use Battle\Action\ActionInterface;
use Battle\Action\DamageAction;
use Battle\Command\CommandFactory;
use Battle\Command\CommandInterface;
use Battle\Container\ContainerInterface;
use Battle\Response\Scenario\Scenario;
use Battle\Response\Statistic\Statistic;
use Battle\Unit\Ability\Ability;
use Battle\Unit\Ability\AbilityCollection;
use Battle\Unit\Ability\AbilityInterface;
use Battle\Unit\UnitInterface;
use Battle\Weapon\Type\WeaponTypeInterface;
use Exception;
use Tests\AbstractUnitTest;
use Tests\Factory\UnitFactory;

class PoisonAbilityTest extends AbstractUnitTest
{
    // Сообщения применения на другого юнита
    private const MESSAGE_APPLY_TO_EN = '<span style="color: #1e72e3">unit_4</span> use <img src="/images/icons/ability/202.png" alt="" /> <span class="ability">Poison</span> on <span style="color: #1e72e3">unit_2</span>';
    private const MESSAGE_APPLY_TO_RU = '<span style="color: #1e72e3">unit_4</span> использовал <img src="/images/icons/ability/202.png" alt="" /> <span class="ability">Отравление</span> на <span style="color: #1e72e3">unit_2</span>';

    // Сообщения применения эффекта на себя
    private const MESSAGE_APPLY_SELF_EN = '<span style="color: #1e72e3">unit_4</span> use <img src="/images/icons/ability/202.png" alt="" /> <span class="ability">Poison</span>';
    private const MESSAGE_APPLY_SELF_RU = '<span style="color: #1e72e3">unit_4</span> использовал <img src="/images/icons/ability/202.png" alt="" /> <span class="ability">Отравление</span>';

    // Сообщения об уроне от эффекта
    private const MESSAGE_DAMAGE_EN = '<span style="color: #1e72e3">unit_2</span> received 8 damage from effect <img src="/images/icons/ability/202.png" alt="" /> <span class="ability">Poison</span>';
    private const MESSAGE_DAMAGE_RU = '<span style="color: #1e72e3">unit_2</span> получил 8 урона от эффекта <img src="/images/icons/ability/202.png" alt="" /> <span class="ability">Отравление</span>';

    // -----------------------------------------------------------------------------------------------------------------
    // ------------------------------------------   Тесты через Ability   ----------------------------------------------
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Тест на создание способности PoisonAbility через универсальный объект Ability
     *
     * @throws Exception
     */
    public function testPoisonAbilityCreate(): void
    {
        $name = 'Poison';
        $icon = '/images/icons/ability/202.png';

        $unit = UnitFactory::createByTemplate(4);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = $this->createAbility($unit);

        self::assertEquals($name, $ability->getName());
        self::assertEquals($icon, $ability->getIcon());
        self::assertEquals($unit, $ability->getUnit());
        self::assertFalse($ability->isReady());
        self::assertTrue($ability->canByUsed($enemyCommand, $command));
        self::assertFalse($ability->isDisposable());
        self::assertFalse($ability->isUsage());

        // Up concentration
        for ($i = 0; $i < 10; $i++) {
            $unit->newRound();
        }

        $collection = new AbilityCollection();
        $collection->add($ability);

        foreach ($collection as $item) {
            self::assertEquals($ability, $item);
        }

        $collection->update($unit);

        self::assertTrue($ability->isReady());

        $ability->usage();
        self::assertTrue($ability->isUsage());
        self::assertFalse($ability->isReady());
    }

    /**
     * Тест на получение Actions из PoisonAbility через универсальный объект Ability
     *
     * @throws Exception
     */
    public function testPoisonAbilityGetActions(): void
    {
        $unit = UnitFactory::createByTemplate(4, $this->container);
        $enemyUnit = UnitFactory::createByTemplate(2, $this->container);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = $this->createAbility($unit);

        self::assertEquals(
            $this->createActions($this->container, $unit, $command, $enemyCommand, ActionInterface::TARGET_EFFECT_ENEMY),
            $ability->getActions($enemyCommand, $command)
        );
    }

    /**
     * Тест на получение false в $ability->canByUsed(), когда все противники уже имеют такой эффект, через универсальный
     * объект Ability
     *
     * @throws Exception
     */
    public function testPoisonAbilityCantByUsed(): void
    {
        $unit = UnitFactory::createByTemplate(4);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = $this->createAbility($unit);

        // Up concentration
        for ($i = 0; $i < 10; $i++) {
            $unit->newRound();
        }

        self::assertTrue($ability->canByUsed($enemyCommand, $command));

        // Применяем эффект
        foreach ($ability->getActions($enemyCommand, $command) as $action) {
            self::assertTrue($action->canByUsed());
            $action->handle();
            self::assertEquals(self::MESSAGE_APPLY_TO_EN, $this->getChat()->addMessage($action));
            self::assertEquals(self::MESSAGE_APPLY_TO_RU, $this->getChatRu()->addMessage($action));
        }

        // Теперь эффект у противника есть, и больше способность примениться не может
        // Так как все противники (один противник) уже имеют такой эффект
        self::assertFalse($ability->canByUsed($enemyCommand, $command));

        $effects = $enemyUnit->getEffects();

        self::assertCount(1, $effects);

        foreach ($effects as $effect) {
            $actions = $effect->getOnNextRoundActions();
            self::assertCount(1, $actions);
            foreach ($actions as $action) {
                self::assertTrue($action->canByUsed());
                $action->handle();
                self::assertEquals(self::MESSAGE_DAMAGE_EN, $this->getChat()->addMessage($action));
                self::assertEquals(self::MESSAGE_DAMAGE_RU, $this->getChatRu()->addMessage($action));
            }
        }
    }

    /**
     * Тест на формирование сообщения о применении эффекта на себя через универсальный объект Ability
     *
     * @throws Exception
     */
    public function testPoisonAbilityApplySelfMessage(): void
    {
        $unit = UnitFactory::createByTemplate(4, $this->container);
        $enemyUnit = UnitFactory::createByTemplate(2, $this->container);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        // Применяем эффект
        foreach ($this->createActions($this->container, $unit, $command, $enemyCommand, ActionInterface::TARGET_EFFECT_ALLIES) as $action) {
            self::assertTrue($action->canByUsed());
            $action->handle();
            self::assertEquals(self::MESSAGE_APPLY_SELF_EN, $this->getChat()->addMessage($action));
            self::assertEquals(self::MESSAGE_APPLY_SELF_RU, $this->getChatRu()->addMessage($action));
        }
    }

    // -----------------------------------------------------------------------------------------------------------------
    // -------------------------------   Аналогичные тесты через AbilityDataProvider   ---------------------------------
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Тест на создание способности PoisonAbility через универсальный объект Ability
     *
     * @throws Exception
     */
    public function testPoisonAbilityDataProviderCreate(): void
    {
        $name = 'Poison';
        $icon = '/images/icons/ability/202.png';

        $unit = UnitFactory::createByTemplate(4);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = $this->getAbility($unit, 'Poison');

        self::assertEquals($name, $ability->getName());
        self::assertEquals($icon, $ability->getIcon());
        self::assertEquals($unit, $ability->getUnit());
        self::assertFalse($ability->isReady());
        self::assertTrue($ability->canByUsed($enemyCommand, $command));
        self::assertFalse($ability->isDisposable());
        self::assertFalse($ability->isUsage());

        // Up concentration
        for ($i = 0; $i < 10; $i++) {
            $unit->newRound();
        }

        $collection = new AbilityCollection();
        $collection->add($ability);

        foreach ($collection as $item) {
            self::assertEquals($ability, $item);
        }

        $collection->update($unit);

        self::assertTrue($ability->isReady());

        $ability->usage();
        self::assertTrue($ability->isUsage());
        self::assertFalse($ability->isReady());
    }

    /**
     * Тест на получение Actions из PoisonAbility через универсальный объект Ability
     *
     * @throws Exception
     */
    public function testPoisonAbilityDataProviderGetActions(): void
    {
        $unit = UnitFactory::createByTemplate(4, $this->container);
        $enemyUnit = UnitFactory::createByTemplate(2, $this->container);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = $this->getAbility($unit, 'Poison');

        self::assertEquals(
            $this->createActions($this->container, $unit, $command, $enemyCommand, ActionInterface::TARGET_EFFECT_ENEMY),
            $ability->getActions($enemyCommand, $command)
        );
    }

    /**
     * Тест на получение false в $ability->canByUsed(), когда все противники уже имеют такой эффект, через универсальный
     * объект Ability
     *
     * @throws Exception
     */
    public function testPoisonAbilityDataProviderCantByUsed(): void
    {
        $unit = UnitFactory::createByTemplate(4);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = $this->getAbility($unit, 'Poison');

        // Up concentration
        for ($i = 0; $i < 10; $i++) {
            $unit->newRound();
        }

        self::assertTrue($ability->canByUsed($enemyCommand, $command));

        // Применяем эффект
        foreach ($ability->getActions($enemyCommand, $command) as $action) {
            self::assertTrue($action->canByUsed());
            $action->handle();
            self::assertEquals(self::MESSAGE_APPLY_TO_EN, $this->getChat()->addMessage($action));
            self::assertEquals(self::MESSAGE_APPLY_TO_RU, $this->getChatRu()->addMessage($action));

            // Дополнительное проверяем, что по событию успешно создается анимация
            (new Scenario())->addAnimation($action, new Statistic());
        }

        // Теперь эффект у противника есть, и больше способность примениться не может
        // Так как все противники (один противник) уже имеют такой эффект
        self::assertFalse($ability->canByUsed($enemyCommand, $command));

        $effects = $enemyUnit->getEffects();

        self::assertCount(1, $effects);

        foreach ($effects as $effect) {
            $actions = $effect->getOnNextRoundActions();
            self::assertCount(1, $actions);
            foreach ($actions as $action) {
                self::assertTrue($action->canByUsed());
                $action->handle();
                self::assertEquals(self::MESSAGE_DAMAGE_EN, $this->getChat()->addMessage($action));
                self::assertEquals(self::MESSAGE_DAMAGE_RU, $this->getChatRu()->addMessage($action));

                // Дополнительное проверяем, что по событию успешно создается анимация
                (new Scenario())->addAnimation($action, new Statistic());
            }
        }
    }

    /**
     * @param ContainerInterface $container
     * @param UnitInterface $unit
     * @param CommandInterface $command
     * @param CommandInterface $enemyCommand
     * @param int $typeTarget
     * @return ActionCollection
     * @throws Exception
     */
    private function createActions(
        ContainerInterface $container,
        UnitInterface $unit,
        CommandInterface $command,
        CommandInterface $enemyCommand,
        int $typeTarget
    ): ActionCollection
    {
        $data = [
            'type'           => ActionInterface::EFFECT,
            'action_unit'    => $unit,
            'enemy_command'  => $enemyCommand,
            'allies_command' => $command,
            'type_target'    => $typeTarget,
            'name'           => 'Poison',
            'icon'           => '/images/icons/ability/202.png',
            'message_method' => 'applyEffect',
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
            ],
        ];

        $actions = new ActionCollection();
        $actions->add($container->getActionFactory()->create($data));

        return $actions;
    }

    /**
     * @param UnitInterface $unit
     * @return AbilityInterface
     * @throws Exception
     */
    private function createAbility(UnitInterface $unit): AbilityInterface
    {
        $name = 'Poison';
        $icon = '/images/icons/ability/202.png';

        return new Ability(
            $unit,
            false,
            $name,
            $icon,
            [
                [
                    'type'           => ActionInterface::EFFECT,
                    'type_target'    => ActionInterface::TARGET_EFFECT_ENEMY,
                    'name'           => $name,
                    'icon'           => $icon,
                    'message_method' => 'applyEffect',
                    'effect'         => [
                        'name'                  => $name,
                        'icon'                  => $icon,
                        'duration'              => 5,
                        'on_apply_actions'      => [],
                        'on_next_round_actions' => [
                            [
                                'type'             => ActionInterface::DAMAGE,
                                'type_target'      => ActionInterface::TARGET_SELF,
                                'name'             => $name,
                                'offense'          => [
                                    'damage_type'         => 2,
                                    'weapon_type'         => WeaponTypeInterface::NONE,
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
                                'icon'             => $icon,
                                'target_tracking'  => false,
                            ],
                        ],
                        'on_disable_actions'    => [],
                    ],
                ],
            ],
            AbilityInterface::ACTIVATE_CONCENTRATION,
            [
                WeaponTypeInterface::STAFF,
                WeaponTypeInterface::WAND,
            ],
            0
        );
    }
}
