<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Ability\Effect;

use Battle\Action\ActionCollection;
use Battle\Action\ActionInterface;
use Battle\Action\DamageAction;
use Battle\Action\ParalysisAction;
use Battle\Command\CommandFactory;
use Battle\Command\CommandInterface;
use Battle\Container\Container;
use Battle\Container\ContainerInterface;
use Battle\Unit\Ability\Ability;
use Battle\Unit\Ability\AbilityCollection;
use Battle\Unit\Ability\AbilityInterface;
use Battle\Unit\UnitInterface;
use Exception;
use Tests\AbstractUnitTest;
use Tests\Factory\UnitFactory;

/**
 * StunAbilityTest почти аналогичен тесту ParalysisAbilityTest, т.к. у обоих способностей аналогичное действие -
 * блокируют действия юнита в его ходе
 *
 * @package Tests\Battle\Unit\Ability\Effect
 */
class StunAbilityTest extends AbstractUnitTest
{
    private const MESSAGE_APPLY_EN = '<span style="color: #1e72e3">unit_1</span> use <img src="/images/icons/ability/186.png" alt="" /> <span class="ability">Stun</span> on <span style="color: #1e72e3">unit_2</span>';
    private const MESSAGE_APPLY_RU = '<span style="color: #1e72e3">unit_1</span> использовал <img src="/images/icons/ability/186.png" alt="" /> <span class="ability">Оглушение</span> на <span style="color: #1e72e3">unit_2</span>';

    private const MESSAGE_EFFECT_EN = '<span style="color: #1e72e3">unit_2</span> stunned and unable to move';
    private const MESSAGE_EFFECT_RU = '<span style="color: #1e72e3">unit_2</span> оглушен и не может двигаться';

    // -----------------------------------------------------------------------------------------------------------------
    // ------------------------------------------   Тесты через Ability   ----------------------------------------------
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Тест на создание способности Stun через универсальный объект Ability
     *
     * @throws Exception
     */
    public function testStunAbilityCreate(): void
    {
        $container = new Container();
        $name = 'Stun';
        $icon = '/images/icons/ability/186.png';

        $unit = UnitFactory::createByTemplate(1, $container);
        $enemyUnit = UnitFactory::createByTemplate(2, $container);
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

        self::assertEquals(
            $this->getStunActions($container, $unit, $enemyCommand, $command),
            $ability->getActions($enemyCommand, $command)
        );

        $ability->usage();
        self::assertTrue($ability->isUsage());
        self::assertFalse($ability->isReady());
    }

    /**
     * Тест на применение способности Stun
     *
     * @throws Exception
     */
    public function testStunAbilityUse(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = $this->createAbility($unit);

        // Изначально эффектов на юните нет
        self::assertCount(0, $enemyUnit->getEffects());

        // Перед применением способности эффекта на юните еще нет - способность может быть применена
        self::assertTrue($ability->canByUsed($enemyCommand, $command));

        foreach ($ability->getActions($enemyCommand, $command) as $action) {
            self::assertTrue($action->canByUsed());
            $action->handle();
            self::assertEquals(self::MESSAGE_APPLY_EN, $this->getChat()->addMessage($action));
            self::assertEquals(self::MESSAGE_APPLY_RU, $this->getChatRu()->addMessage($action));
        }

        // Эффект появляется
        self::assertCount(1, $enemyUnit->getEffects());

        // После появления эффекта на юните - способность уже не может быть применена
        self::assertFalse($ability->canByUsed($enemyCommand, $command));

        // Пропускаем ходы - заполняем ярость. А также сбрасываем эффект у противника
        for ($i = 0; $i < 20; $i++) {
            $unit->newRound();

            foreach ($enemyUnit->getBeforeActions() as $beforeAction) {
                if ($beforeAction->canByUsed()) {
                    $beforeAction->handle();
                    self::assertEquals(self::MESSAGE_EFFECT_EN, $this->getChat()->addMessage($beforeAction));
                    self::assertEquals(self::MESSAGE_EFFECT_RU, $this->getChatRu()->addMessage($beforeAction));
                }
            }

            // Длительность эффектов обновляется в getAfterActions()
            foreach ($enemyUnit->getAfterActions() as $afterAction) {
                if ($afterAction->canByUsed()) {
                    $afterAction->handle();
                }
            }
        }

        // Эффект исчез
        self::assertCount(0, $enemyUnit->getEffects());

        // Способность опять может быть применена
        self::assertTrue($ability->canByUsed($enemyCommand, $command));
    }

    // -----------------------------------------------------------------------------------------------------------------
    // -------------------------------   Аналогичные тесты через AbilityDataProvider   ---------------------------------
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Тест на создание способности Stun через AbilityDataProvider
     *
     * @throws Exception
     */
    public function testStunAbilityDataProviderCreate(): void
    {
        $container = new Container();
        $name = 'Stun';
        $icon = '/images/icons/ability/186.png';

        $unit = UnitFactory::createByTemplate(1, $container);
        $enemyUnit = UnitFactory::createByTemplate(2, $container);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = $this->createAbilityByDataProvider($unit, 'Stun');

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

        self::assertEquals(
            $this->getStunActions($container, $unit, $enemyCommand, $command),
            $ability->getActions($enemyCommand, $command)
        );

        $ability->usage();
        self::assertTrue($ability->isUsage());
        self::assertFalse($ability->isReady());
    }

    /**
     * Тест на применение способности Stun через AbilityDataProvider
     *
     * @throws Exception
     */
    public function testStunAbilityDataProviderUse(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = $this->createAbilityByDataProvider($unit, 'Stun');

        // Изначально эффектов на юните нет
        self::assertCount(0, $enemyUnit->getEffects());

        // Перед применением способности эффекта на юните еще нет - способность может быть применена
        self::assertTrue($ability->canByUsed($enemyCommand, $command));

        foreach ($ability->getActions($enemyCommand, $command) as $action) {
            self::assertTrue($action->canByUsed());
            $action->handle();
            self::assertEquals(self::MESSAGE_APPLY_EN, $this->getChat()->addMessage($action));
            self::assertEquals(self::MESSAGE_APPLY_RU, $this->getChatRu()->addMessage($action));
        }

        // Эффект появляется
        self::assertCount(1, $enemyUnit->getEffects());

        // После появления эффекта на юните - способность уже не может быть применена
        self::assertFalse($ability->canByUsed($enemyCommand, $command));

        // Пропускаем ходы - заполняем ярость. А также сбрасываем эффект у противника
        for ($i = 0; $i < 20; $i++) {
            $unit->newRound();

            foreach ($enemyUnit->getBeforeActions() as $beforeAction) {
                if ($beforeAction->canByUsed()) {
                    $beforeAction->handle();
                    self::assertEquals(self::MESSAGE_EFFECT_EN, $this->getChat()->addMessage($beforeAction));
                    self::assertEquals(self::MESSAGE_EFFECT_RU, $this->getChatRu()->addMessage($beforeAction));
                }
            }

            // Длительность эффектов обновляется в getAfterActions()
            foreach ($enemyUnit->getAfterActions() as $afterAction) {
                if ($afterAction->canByUsed()) {
                    $afterAction->handle();
                }
            }
        }

        // Эффект исчез
        self::assertCount(0, $enemyUnit->getEffects());

        // Способность опять может быть применена
        self::assertTrue($ability->canByUsed($enemyCommand, $command));
    }

    /**
     * @param ContainerInterface $container
     * @param UnitInterface $unit
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $alliesCommand
     * @return ActionCollection
     * @throws Exception
     */
    private function getStunActions(
        ContainerInterface $container,
        UnitInterface $unit,
        CommandInterface $enemyCommand,
        CommandInterface $alliesCommand
    ): ActionCollection
    {
        $name = 'Stun';
        $icon = '/images/icons/ability/186.png';

        $collection = new ActionCollection();

        $data = [
            'type'           => ActionInterface::EFFECT,
            'action_unit'    => $unit,
            'enemy_command'  => $enemyCommand,
            'allies_command' => $alliesCommand,
            'type_target'    => ActionInterface::TARGET_RANDOM_ENEMY,
            'name'           => $name,
            'icon'           => $icon,
            'message_method' => 'applyEffect',
            'effect'         => [
                'name'                  => $name,
                'icon'                  => $icon,
                'duration'              => 2,
                'on_apply_actions'      => [],
                'on_next_round_actions' => [
                    [
                        'type'             => ActionInterface::PARALYSIS,
                        'action_unit'      => $unit,
                        'enemy_command'    => $enemyCommand,
                        'allies_command'   => $alliesCommand,
                        'type_target'      => ActionInterface::TARGET_SELF,
                        'name'             => $name,
                        'can_be_avoided'   => false,
                        'animation_method' => DamageAction::EFFECT_ANIMATION_METHOD,
                        'message_method'   => ParalysisAction::STUN_MESSAGE_METHOD,
                        'icon'             => $icon,
                    ],
                ],
                'on_disable_actions'    => [],
            ],
        ];

        $collection->add($container->getActionFactory()->create($data));

        return $collection;
    }

    /**
     * @param UnitInterface $unit
     * @return AbilityInterface
     * @throws Exception
     */
    private function createAbility(UnitInterface $unit): AbilityInterface
    {
        $name = 'Stun';
        $icon = '/images/icons/ability/186.png';

        return new Ability(
            $unit,
            false,
            $name,
            $icon,
            [
                [
                    'type'           => ActionInterface::EFFECT,
                    'type_target'    => ActionInterface::TARGET_RANDOM_ENEMY,
                    'name'           => $name,
                    'icon'           => $icon,
                    'message_method' => 'applyEffect',
                    'effect'         => [
                        'name'                  => $name,
                        'icon'                  => $icon,
                        'duration'              => 2,
                        'on_apply_actions'      => [],
                        'on_next_round_actions' => [
                            [
                                'type'             => ActionInterface::PARALYSIS,
                                'type_target'      => ActionInterface::TARGET_SELF,
                                'name'             => $name,
                                'can_be_avoided'   => false,
                                'animation_method' => DamageAction::EFFECT_ANIMATION_METHOD,
                                'message_method'   => ParalysisAction::STUN_MESSAGE_METHOD,
                                'icon'             => $icon,
                            ],
                        ],
                        'on_disable_actions'    => [],
                    ],
                ],
            ],
            AbilityInterface::ACTIVATE_CONCENTRATION,
        );
    }

    /**
     * @param UnitInterface $unit
     * @param string $abilityName
     * @param int $abilityLevel
     * @return AbilityInterface
     * @throws Exception
     */
    private function createAbilityByDataProvider(UnitInterface $unit, string $abilityName, int $abilityLevel = 1): AbilityInterface
    {
        $container = new Container();

        return $container->getAbilityFactory()->create(
            $unit,
            $container->getAbilityDataProvider()->get($abilityName, $abilityLevel)
        );
    }
}
