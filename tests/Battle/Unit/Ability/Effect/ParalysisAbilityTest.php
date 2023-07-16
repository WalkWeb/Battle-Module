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
use Battle\Response\Scenario\Scenario;
use Battle\Response\Statistic\Statistic;
use Battle\Unit\Ability\Ability;
use Battle\Unit\Ability\AbilityCollection;
use Battle\Unit\Ability\AbilityInterface;
use Battle\Unit\UnitInterface;
use Exception;
use Tests\AbstractUnitTest;
use Tests\Factory\UnitFactory;

/**
 * ParalysisAbilityTest почти аналогичен тесту StunAbilityTest, т.к. у обоих способностей аналогичное действие -
 * блокируют действия юнита в его ходе
 *
 * @package Tests\Battle\Unit\Ability\Effect
 */
class ParalysisAbilityTest extends AbstractUnitTest
{
    private const MESSAGE_APPLY_EN = '<span style="color: #1e72e3">unit_1</span> use <img src="/images/icons/ability/086.png" alt="" /> <span class="ability">Paralysis</span> on <span style="color: #1e72e3">unit_2</span>';
    private const MESSAGE_APPLY_RU = '<span style="color: #1e72e3">unit_1</span> использовал <img src="/images/icons/ability/086.png" alt="" /> <span class="ability">Паралич</span> на <span style="color: #1e72e3">unit_2</span>';

    private const MESSAGE_EFFECT_EN = '<span style="color: #1e72e3">unit_2</span> paralyzed and unable to move';
    private const MESSAGE_EFFECT_RU = '<span style="color: #1e72e3">unit_2</span> парализован и не может двигаться';

    // -----------------------------------------------------------------------------------------------------------------
    // ------------------------------------------   Тесты через Ability   ----------------------------------------------
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Тест на создание способности Paralysis через универсальный объект Ability
     *
     * @throws Exception
     */
    public function testParalysisAbilityCreate(): void
    {
        $name = 'Paralysis';
        $icon = '/images/icons/ability/086.png';

        $container = new Container();
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

        // Up rage
        for ($i = 0; $i < 20; $i++) {
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
            $this->getParalysisActions($container, $unit, $enemyCommand, $command),
            $ability->getActions($enemyCommand, $command)
        );

        $ability->usage();
        self::assertTrue($ability->isUsage());
        self::assertFalse($ability->isReady());
    }

    /**
     * Тест на применение способности Paralysis
     *
     * @throws Exception
     */
    public function testParalysisAbilityUse(): void
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
     * Тест на создание способности ParalysisAction через AbilityDataProvider
     *
     * @throws Exception
     */
    public function testParalysisAbilityDataProviderCreate(): void
    {
        $name = 'Paralysis';
        $icon = '/images/icons/ability/086.png';

        $container = new Container();
        $unit = UnitFactory::createByTemplate(21, $container);
        $enemyUnit = UnitFactory::createByTemplate(2, $container);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = $this->createAbilityByDataProvider($unit, 'Paralysis');

        self::assertEquals($name, $ability->getName());
        self::assertEquals($icon, $ability->getIcon());
        self::assertEquals($unit, $ability->getUnit());
        self::assertFalse($ability->isReady());
        self::assertTrue($ability->canByUsed($enemyCommand, $command));
        self::assertFalse($ability->isDisposable());
        self::assertFalse($ability->isUsage());

        // Up rage
        for ($i = 0; $i < 20; $i++) {
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
            $this->getParalysisActions($container, $unit, $enemyCommand, $command),
            $ability->getActions($enemyCommand, $command)
        );

        $ability->usage();
        self::assertTrue($ability->isUsage());
        self::assertFalse($ability->isReady());
    }

    /**
     * @throws Exception
     */
    public function testParalysisAbilityDataProviderCanByUsed(): void
    {
        $unit = UnitFactory::createByTemplate(21);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = $this->createAbilityByDataProvider($unit, 'Paralysis');

        // Изначально эффектов на юните нет
        self::assertCount(0, $enemyUnit->getEffects());

        // Перед применением способности эффекта на юните еще нет - способность может быть применена
        self::assertTrue($ability->canByUsed($enemyCommand, $command));

        foreach ($ability->getActions($enemyCommand, $command) as $action) {
            self::assertTrue($action->canByUsed());
            $action->handle();

            // Дополнительное проверяем, что по событию успешно создается анимация
            (new Scenario())->addAnimation($action, new Statistic());
        }

        // Эффект появляется
        self::assertCount(1, $enemyUnit->getEffects());

        // После появления эффекта на юните - способность уже не может быть применена
        self::assertFalse($ability->canByUsed($enemyCommand, $command));

        // Пропускаем ходы - заполняем ярость. А также сбрасываем эффект у противника
        for ($i = 0; $i < 20; $i++) {
            $unit->newRound();

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
    private function getParalysisActions(
        ContainerInterface $container,
        UnitInterface $unit,
        CommandInterface $enemyCommand,
        CommandInterface $alliesCommand
    ): ActionCollection
    {
        $collection = new ActionCollection();

        $data = [
            'type'           => ActionInterface::EFFECT,
            'action_unit'    => $unit,
            'enemy_command'  => $enemyCommand,
            'allies_command' => $alliesCommand,
            'type_target'    => ActionInterface::TARGET_RANDOM_ENEMY,
            'name'           => 'Paralysis',
            'icon'           => '/images/icons/ability/086.png',
            'message_method' => 'applyEffect',
            'effect'         => [
                'name'                  => 'Paralysis',
                'icon'                  => '/images/icons/ability/086.png',
                'duration'              => 2,
                'on_apply_actions'      => [],
                'on_next_round_actions' => [
                    [
                        'type'             => ActionInterface::PARALYSIS,
                        'action_unit'      => $unit,
                        'enemy_command'    => $enemyCommand,
                        'allies_command'   => $alliesCommand,
                        'type_target'      => ActionInterface::TARGET_SELF,
                        'name'             => 'Paralysis',
                        'can_be_avoided'   => false,
                        'animation_method' => DamageAction::EFFECT_ANIMATION_METHOD,
                        'message_method'   => ParalysisAction::PARALYSIS_MESSAGE_METHOD,
                        'icon'             => '/images/icons/ability/086.png',
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
        $name = 'Paralysis';
        $icon = '/images/icons/ability/086.png';

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
                                'message_method'   => ParalysisAction::PARALYSIS_MESSAGE_METHOD,
                                'icon'             => $icon,
                            ],
                        ],
                        'on_disable_actions'    => [],
                    ],
                ],
            ],
            AbilityInterface::ACTIVATE_RAGE,
            [],
            0
        );
    }
}
