<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Ability\Effect;

use Battle\Action\ActionCollection;
use Battle\Action\ActionInterface;
use Battle\Action\BuffAction;
use Battle\Command\CommandFactory;
use Battle\Command\CommandInterface;
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

class BattleFuryAbilityTest extends AbstractUnitTest
{
    private const MESSAGE_EN = '<span style="color: #ae882d">Titan</span> use <img src="/images/icons/ability/102.png" alt="" /> <span class="ability">Battle Fury</span>';
    private const MESSAGE_RU = '<span style="color: #ae882d">Titan</span> использовал <img src="/images/icons/ability/102.png" alt="" /> <span class="ability">Ярость битвы</span>';

    // -----------------------------------------------------------------------------------------------------------------
    // ------------------------------------------   Тесты через Ability   ----------------------------------------------
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Тест на создание способности BattleFuryAbility через универсальный объект Ability
     *
     * @throws Exception
     */
    public function testBattleFuryAbilityCreate(): void
    {
        $name = 'Battle Fury';
        $icon = '/images/icons/ability/102.png';
        $disposable = false;

        $unit = UnitFactory::createByTemplate(21, $this->container);
        $enemyUnit = UnitFactory::createByTemplate(2, $this->container);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = $this->createAbility($unit);

        self::assertEquals($name, $ability->getName());
        self::assertEquals($icon, $ability->getIcon());
        self::assertEquals($unit, $ability->getUnit());
        self::assertFalse($ability->isReady());
        self::assertTrue($ability->canByUsed($enemyCommand, $command));
        self::assertEquals($disposable, $ability->isDisposable());
        self::assertFalse($ability->isUsage());

        // Up concentration
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
            $this->getBattleFuryActions($this->container, $unit, $enemyCommand, $command),
            $ability->getActions($enemyCommand, $command)
        );

        $ability->usage();
        self::assertTrue($ability->isUsage());
        self::assertFalse($ability->isReady());
    }

    /**
     * Тест на применение способности BattleFuryAbility через универсальный объект Ability
     *
     * @throws Exception
     */
    public function testBattleFuryAbilityApply(): void
    {
        $power = 1.4;
        $unit = UnitFactory::createByTemplate(21, $this->container);
        $enemyUnit = UnitFactory::createByTemplate(2, $this->container);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $oldAttackSpeed = $unit->getOffense()->getAttackSpeed();

        $ability = $this->createAbility($unit);

        // Up concentration
        for ($i = 0; $i < 20; $i++) {
            $unit->newRound();
        }

        $collection = new AbilityCollection();
        $collection->add($ability);

        foreach ($collection as $item) {
            self::assertEquals($ability, $item);
        }

        $collection->update($unit);

        $actions = $ability->getActions($enemyCommand, $command);

        foreach ($actions as $action) {
            self::assertTrue($action->canByUsed());
            $action->handle();
            self::assertEquals(self::MESSAGE_EN, $this->getChat()->addMessage($action));
            self::assertEquals(self::MESSAGE_RU, $this->getChatRu()->addMessage($action));
        }

        // Проверяем, что скорость атаки юнита выросла
        self::assertEquals($oldAttackSpeed * $power , $unit->getOffense()->getAttackSpeed());

        // Обновляем длительность эффектов. Длительность эффектов обновляется в getAfterActions()
        for ($i = 0; $i < 30; $i++) {
            foreach ($unit->getAfterActions() as $afterAction) {
                if ($afterAction->canByUsed()) {
                    $afterAction->handle();
                }
            }
        }

        // Проверяем, что скорость атаки вернулась к исходному
        self::assertEquals($oldAttackSpeed, $unit->getOffense()->getAttackSpeed());
    }

    /**
     * Тест на проверку перехода события из способного к применению, в невозможное к применение и обратно через
     * универсальный объект Ability
     *
     * @throws Exception
     */
    public function testBattleFuryAbilityCanByUsed(): void
    {
        $unit = UnitFactory::createByTemplate(21);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = $this->createAbility($unit);

        // Перед применением способности эффекта на юните еще нет - способность может быть применена
        self::assertTrue($ability->canByUsed($enemyCommand, $command));

        foreach ($ability->getActions($enemyCommand, $command) as $action) {
            self::assertTrue($action->canByUsed());
            $action->handle();
        }

        // После появления эффекта на юните - способность уже не может быть применена
        self::assertFalse($ability->canByUsed($enemyCommand, $command));

        // Обновляем длительность эффектов. Длительность эффектов обновляется в getAfterActions()
        for ($i = 0; $i < 30; $i++) {
            foreach ($unit->getAfterActions() as $afterAction) {
                if ($afterAction->canByUsed()) {
                    $afterAction->handle();
                }
            }
        }

        // Эффект исчез - способность опять может быть применена
        self::assertTrue($ability->canByUsed($enemyCommand, $command));
    }

    /**
     * Тест на проверку того, что при откате бафа BattleFury (должно работать со всеми бафами) сообщение в чат не
     * формируется
     *
     * @throws Exception
     */
    public function testBattleFuryRevertMessage(): void
    {
        $unit = UnitFactory::createByTemplate(21);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = $this->createAbility($unit);

        foreach ($ability->getActions($enemyCommand, $command) as $action) {

            $effect = $action->getEffect();

            $onDisableActions = $effect->getOnDisableActions();

            foreach ($onDisableActions as $onDisableAction) {
                self::assertEquals('', $this->getChat()->addMessage($onDisableAction));
                self::assertEquals('', $this->getChatRu()->addMessage($onDisableAction));
            }
        }
    }

    // -----------------------------------------------------------------------------------------------------------------
    // -------------------------------   Аналогичные тесты через AbilityDataProvider   ---------------------------------
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Тест на создание способности BattleFuryAbility через AbilityDataProvider
     *
     * @throws Exception
     */
    public function testBattleFuryAbilityDataProviderCreate(): void
    {
        $this->assertCreateEffectAbility(
            21,
            'Battle Fury',
            '/images/icons/ability/102.png',
            AbilityInterface::ACTIVATE_RAGE
        );
    }

    /**
     * Тест на применение способности BattleFuryAbility через AbilityDataProvider
     *
     * @throws Exception
     */
    public function testBattleFuryAbilityDataProviderApply(): void
    {
        $power = 1.4;
        $unit = UnitFactory::createByTemplate(21, $this->container);
        $enemyUnit = UnitFactory::createByTemplate(2, $this->container);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $oldAttackSpeed = $unit->getOffense()->getAttackSpeed();

        $ability = $this->getAbility($unit, 'Battle Fury');

        // Up concentration
        for ($i = 0; $i < 20; $i++) {
            $unit->newRound();
        }

        $collection = new AbilityCollection();
        $collection->add($ability);

        foreach ($collection as $item) {
            self::assertEquals($ability, $item);
        }

        $collection->update($unit);

        $actions = $ability->getActions($enemyCommand, $command);

        foreach ($actions as $action) {
            self::assertTrue($action->canByUsed());
            $action->handle();
            self::assertEquals(self::MESSAGE_EN, $this->getChat()->addMessage($action));
            self::assertEquals(self::MESSAGE_RU, $this->getChatRu()->addMessage($action));

            // Дополнительное проверяем, что по событию успешно создается анимация
            (new Scenario())->addAnimation($action, new Statistic());
        }

        // Проверяем, что скорость атаки юнита выросла
        self::assertEquals($oldAttackSpeed * $power , $unit->getOffense()->getAttackSpeed());

        // Обновляем длительность эффектов. Длительность эффектов обновляется в getAfterActions()
        for ($i = 0; $i < 30; $i++) {
            foreach ($unit->getAfterActions() as $afterAction) {
                if ($afterAction->canByUsed()) {
                    $afterAction->handle();
                }
            }
        }

        // Проверяем, что скорость атаки вернулась к исходному
        self::assertEquals($oldAttackSpeed, $unit->getOffense()->getAttackSpeed());
    }

    /**
     * Тест на проверку перехода события из способного к применению, в невозможное к применение и обратно через
     * универсальный объект Ability
     *
     * @throws Exception
     */
    public function testBattleFuryAbilityDataProviderCanByUsed(): void
    {
        $unit = UnitFactory::createByTemplate(21);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = $this->getAbility($unit, 'Battle Fury');

        // Перед применением способности эффекта на юните еще нет - способность может быть применена
        self::assertTrue($ability->canByUsed($enemyCommand, $command));

        foreach ($ability->getActions($enemyCommand, $command) as $action) {
            self::assertTrue($action->canByUsed());
            $action->handle();
        }

        // После появления эффекта на юните - способность уже не может быть применена
        self::assertFalse($ability->canByUsed($enemyCommand, $command));

        // Обновляем длительность эффектов. Длительность эффектов обновляется в getAfterActions()
        for ($i = 0; $i < 30; $i++) {
            foreach ($unit->getAfterActions() as $afterAction) {
                if ($afterAction->canByUsed()) {
                    $afterAction->handle();
                }
            }
        }

        // Эффект исчез - способность опять может быть применена
        self::assertTrue($ability->canByUsed($enemyCommand, $command));
    }

    /**
     * @param ContainerInterface $container
     * @param UnitInterface $unit
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $command
     * @return ActionCollection
     * @throws Exception
     */
    private function getBattleFuryActions(
        ContainerInterface $container,
        UnitInterface $unit,
        CommandInterface $enemyCommand,
        CommandInterface $command
    ): ActionCollection
    {
        $collection = new ActionCollection();

        $data = [
            'type'           => ActionInterface::EFFECT,
            'action_unit'    => $unit,
            'enemy_command'  => $enemyCommand,
            'allies_command' => $command,
            'type_target'    => ActionInterface::TARGET_SELF,
            'name'           => 'Battle Fury',
            'icon'           => '/images/icons/ability/102.png',
            'message_method' => 'applyEffect',
            'effect'         => [
                'name'                  => 'Battle Fury',
                'icon'                  => '/images/icons/ability/102.png',
                'duration'              => 15,
                'on_apply_actions'      => [
                    [
                        'type'           => ActionInterface::BUFF,
                        'action_unit'    => $unit,
                        'enemy_command'  => $enemyCommand,
                        'allies_command' => $command,
                        'type_target'    => ActionInterface::TARGET_SELF,
                        'name'           => 'Battle Fury',
                        'modify_method'  => BuffAction::ATTACK_SPEED,
                        'power'          => 40,
                        'message_method' => ActionInterface::SKIP_MESSAGE_METHOD,
                    ],
                ],
                'on_next_round_actions' => [],
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
        $name = 'Battle Fury';
        $icon = '/images/icons/ability/102.png';

        return new Ability(
            $unit,
            false,
            $name,
            $icon,
            [
                [
                    'type'           => ActionInterface::EFFECT,
                    'type_target'    => ActionInterface::TARGET_SELF,
                    'name'           => $name,
                    'icon'           => $icon,
                    'message_method' => 'applyEffect',
                    'effect'         => [
                        'name'                  => $name,
                        'icon'                  => $icon,
                        'duration'              => 15,
                        'on_apply_actions'      => [
                            [
                                'type'           => ActionInterface::BUFF,
                                'type_target'    => ActionInterface::TARGET_SELF,
                                'name'           => $name,
                                'modify_method'  => BuffAction::ATTACK_SPEED,
                                'power'          => 40,
                                'message_method' => ActionInterface::SKIP_MESSAGE_METHOD,
                            ],
                        ],
                        'on_next_round_actions' => [],
                        'on_disable_actions'    => [],
                    ],
                ],
            ],
            AbilityInterface::ACTIVATE_RAGE,
            [],
        );
    }
}
