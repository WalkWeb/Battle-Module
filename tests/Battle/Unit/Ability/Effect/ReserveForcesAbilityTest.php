<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Ability\Effect;

use Battle\Action\ActionCollection;
use Battle\Action\ActionFactory;
use Battle\Action\ActionInterface;
use Battle\Command\CommandFactory;
use Battle\Command\CommandInterface;
use Battle\Container\Container;
use Battle\Result\Scenario\Scenario;
use Battle\Result\Statistic\Statistic;
use Battle\Unit\Ability\Ability;
use Battle\Unit\Ability\AbilityCollection;
use Battle\Unit\Ability\AbilityFactory;
use Battle\Unit\Ability\AbilityInterface;
use Battle\Unit\Ability\DataProvider\AbilityDataProviderInterface;
use Battle\Unit\Ability\DataProvider\ExampleAbilityDataProvider;
use Battle\Unit\UnitInterface;
use Exception;
use Tests\AbstractUnitTest;
use Tests\Battle\Factory\UnitFactory;

class ReserveForcesAbilityTest extends AbstractUnitTest
{
    private const MESSAGE_EN = '<span style="color: #ae882d">Titan</span> use <img src="/images/icons/ability/156.png" alt="" /> <span class="ability">Reserve Forces</span>';
    private const MESSAGE_RU = '<span style="color: #ae882d">Titan</span> использовал <img src="/images/icons/ability/156.png" alt="" /> <span class="ability">Резервные Силы</span>';

    // -----------------------------------------------------------------------------------------------------------------
    // ------------------------------------------   Тесты через Ability   ----------------------------------------------
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Тест на создание способности ReserveForcesAbility через универсальный объект Ability
     *
     * @throws Exception
     */
    public function testReserveForcesAbilityUse(): void
    {
        $name = 'Reserve Forces';
        $icon = '/images/icons/ability/156.png';

        $unit = UnitFactory::createByTemplate(21);
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

        self::assertEquals(
            $this->getReserveForcesActions($unit, $enemyCommand, $command),
            $ability->getAction($enemyCommand, $command)
        );

        $ability->usage();
        self::assertTrue($ability->isUsage());
        self::assertFalse($ability->isReady());
    }

    /**
     * Тест на применение способности ReserveForcesAbility через универсальный объект Ability
     *
     * @throws Exception
     */
    public function testReserveForcesAbilityApply(): void
    {
        $container = new Container();
        $unit = UnitFactory::createByTemplate(21, $container);
        $enemyUnit = UnitFactory::createByTemplate(2, $container);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $unitBaseLife = $unit->getTotalLife();

        // Up concentration
        for ($i = 0; $i < 10; $i++) {
            $unit->newRound();
        }

        $ability = $this->createAbility($unit);

        $collection = new AbilityCollection();
        $collection->add($ability);

        $collection->update($unit);

        foreach ($ability->getAction($enemyCommand, $command) as $action) {
            self::assertTrue($action->canByUsed());
            $action->handle();
            self::assertEquals(self::MESSAGE_EN, $this->getChat()->addMessage($action));
            self::assertEquals(self::MESSAGE_RU, $this->getChatRu()->addMessage($action));
        }

        // Проверяем, что здоровье юнита выросло
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

        // Проверяем, что здоровье вернулось к исходному
        self::assertEquals($unitBaseLife, $unit->getTotalLife());
        self::assertEquals($unitBaseLife, $unit->getLife());
    }

    /**
     * Тест на проверку перехода события из способного к применению, в невозможное к применение и обратно, через
     * универсальный объект Ability
     *
     * @throws Exception
     */
    public function testReserveForcesAbilityCanByUsed(): void
    {
        $unit = UnitFactory::createByTemplate(21);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = $this->createAbility($unit);

        // Перед применением способности эффекта на юните еще нет - способность может быть применена
        self::assertTrue($ability->canByUsed($enemyCommand, $command));

        foreach ($ability->getAction($enemyCommand, $command) as $action) {
            self::assertTrue($action->canByUsed());
            $action->handle();
        }

        // После появления эффекта на юните - способность уже не может быть применена
        self::assertFalse($ability->canByUsed($enemyCommand, $command));

        // Обновляем длительность эффектов. Длительность эффектов обновляется в getAfterActions()
        for ($i = 0; $i < 10; $i++) {
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
     * Тест на выявление ошибки, при котором повторное применение эффекта к персонажу добавляло эффект с длительностью 0
     * через универсальный объект Ability
     *
     * @throws Exception
     */
    public function testReserveForcesAbilityUpdatedDuration(): void
    {
        $unit = UnitFactory::createByTemplate(21);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        // Up concentration
        for ($i = 0; $i < 10; $i++) {
            $unit->newRound();
        }

        $ability = $this->createAbility($unit);

        $collection = new AbilityCollection();
        $collection->add($ability);

        $collection->update($unit);

        foreach ($ability->getAction($enemyCommand, $command) as $action) {
            self::assertTrue($action->canByUsed());
            $action->handle();
        }

        // Проверяем, что длительность = 6
        foreach ($unit->getEffects() as $effect) {
            self::assertEquals(6, $effect->getDuration());
        }

        // Обновляем длительность эффектов. Длительность эффектов обновляется в getAfterActions()
        for ($i = 0; $i < 10; $i++) {
            foreach ($unit->getAfterActions() as $afterAction) {
                if ($afterAction->canByUsed()) {
                    $afterAction->handle();
                }
            }
        }

        // Up concentration
        for ($i = 0; $i < 10; $i++) {
            $unit->newRound();
        }

        $collection->update($unit);

        // Применяем способность еще раз
        foreach ($ability->getAction($enemyCommand, $command) as $action) {
            self::assertTrue($action->canByUsed());
            $action->handle();
        }

        // Проверяем еще раз, что при повторном применении эффекта длительность = 6
        foreach ($unit->getEffects() as $effect) {
            self::assertEquals(6, $effect->getDuration());
        }
    }

    // -----------------------------------------------------------------------------------------------------------------
    // -------------------------------   Аналогичные тесты через AbilityDataProvider   ---------------------------------
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Тест на создание способности ReserveForcesAbility через универсальный объект Ability
     *
     * @throws Exception
     */
    public function testReserveForcesAbilityDataProviderUse(): void
    {
        $name = 'Reserve Forces';
        $icon = '/images/icons/ability/156.png';

        $unit = UnitFactory::createByTemplate(21);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = $this->createAbilityByDataProvider($unit, 'Reserve Forces');

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
            $this->getReserveForcesActions($unit, $enemyCommand, $command),
            $ability->getAction($enemyCommand, $command)
        );

        $ability->usage();
        self::assertTrue($ability->isUsage());
        self::assertFalse($ability->isReady());
    }

    /**
     * Тест на применение способности ReserveForcesAbility через универсальный объект Ability
     *
     * @throws Exception
     */
    public function testReserveForcesAbilityDataProviderApply(): void
    {
        $container = new Container();
        $unit = UnitFactory::createByTemplate(21, $container);
        $enemyUnit = UnitFactory::createByTemplate(2, $container);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $unitBaseLife = $unit->getTotalLife();

        // Up concentration
        for ($i = 0; $i < 10; $i++) {
            $unit->newRound();
        }

        $ability = $this->createAbilityByDataProvider($unit, 'Reserve Forces');

        $collection = new AbilityCollection();
        $collection->add($ability);

        $collection->update($unit);

        foreach ($ability->getAction($enemyCommand, $command) as $action) {
            self::assertTrue($action->canByUsed());
            $action->handle();
            self::assertEquals(self::MESSAGE_EN, $this->getChat()->addMessage($action));
            self::assertEquals(self::MESSAGE_RU, $this->getChatRu()->addMessage($action));

            // Дополнительное проверяем, что по событию успешно создается анимация
            (new Scenario())->addAnimation($action, new Statistic());
        }

        // Проверяем, что здоровье юнита выросло
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

        // Проверяем, что здоровье вернулось к исходному
        self::assertEquals($unitBaseLife, $unit->getTotalLife());
        self::assertEquals($unitBaseLife, $unit->getLife());
    }

    /**
     * Тест на проверку перехода события из способного к применению, в невозможное к применение и обратно, через
     * универсальный объект Ability
     *
     * @throws Exception
     */
    public function testReserveForcesAbilityDataProviderCanByUsed(): void
    {
        $unit = UnitFactory::createByTemplate(21);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = $this->createAbilityByDataProvider($unit, 'Reserve Forces');

        // Перед применением способности эффекта на юните еще нет - способность может быть применена
        self::assertTrue($ability->canByUsed($enemyCommand, $command));

        foreach ($ability->getAction($enemyCommand, $command) as $action) {
            self::assertTrue($action->canByUsed());
            $action->handle();
        }

        // После появления эффекта на юните - способность уже не может быть применена
        self::assertFalse($ability->canByUsed($enemyCommand, $command));

        // Обновляем длительность эффектов. Длительность эффектов обновляется в getAfterActions()
        for ($i = 0; $i < 10; $i++) {
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
     * Тест на выявление ошибки, при котором повторное применение эффекта к персонажу добавляло эффект с длительностью 0
     * через универсальный объект Ability
     *
     * @throws Exception
     */
    public function testReserveForcesAbilityDataProviderUpdatedDuration(): void
    {
        $unit = UnitFactory::createByTemplate(21);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        // Up concentration
        for ($i = 0; $i < 10; $i++) {
            $unit->newRound();
        }

        $ability = $this->createAbilityByDataProvider($unit, 'Reserve Forces');

        $collection = new AbilityCollection();
        $collection->add($ability);

        $collection->update($unit);

        foreach ($ability->getAction($enemyCommand, $command) as $action) {
            self::assertTrue($action->canByUsed());
            $action->handle();
        }

        // Проверяем, что длительность = 6
        foreach ($unit->getEffects() as $effect) {
            self::assertEquals(6, $effect->getDuration());
        }

        // Обновляем длительность эффектов. Длительность эффектов обновляется в getAfterActions()
        for ($i = 0; $i < 10; $i++) {
            foreach ($unit->getAfterActions() as $afterAction) {
                if ($afterAction->canByUsed()) {
                    $afterAction->handle();
                }
            }
        }

        // Up concentration
        for ($i = 0; $i < 10; $i++) {
            $unit->newRound();
        }

        $collection->update($unit);

        // Применяем способность еще раз
        foreach ($ability->getAction($enemyCommand, $command) as $action) {
            self::assertTrue($action->canByUsed());
            $action->handle();
        }

        // Проверяем еще раз, что при повторном применении эффекта длительность = 6
        foreach ($unit->getEffects() as $effect) {
            self::assertEquals(6, $effect->getDuration());
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
        $actionFactory = new ActionFactory();
        $collection = new ActionCollection();

        $data = [
            'type'           => ActionInterface::EFFECT,
            'action_unit'    => $unit,
            'enemy_command'  => $enemyCommand,
            'allies_command' => $alliesCommand,
            'type_target'    => ActionInterface::TARGET_SELF,
            'name'           => 'Reserve Forces',
            'use_message'    => 'use',
            'message_method' => 'applyEffect',
            'icon'           => '/images/icons/ability/156.png',
            'effect'         => [
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
            ],
        ];

        $collection->add($actionFactory->create($data));

        return $collection;
    }

    /**
     * @param UnitInterface $unit
     * @return AbilityInterface
     * @throws Exception
     */
    private function createAbility(UnitInterface $unit): AbilityInterface
    {
        $name = 'Reserve Forces';
        $icon = '/images/icons/ability/156.png';

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
                        'duration'              => 6,
                        'on_apply_actions'      => [
                            [
                                'type'           => ActionInterface::BUFF,
                                'type_target'    => ActionInterface::TARGET_SELF,
                                'name'           => $name,
                                'modify_method'  => 'multiplierMaxLife',
                                'power'          => 130,
                                'message_method' => ActionInterface::SKIP_MESSAGE_METHOD,
                            ],
                        ],
                        'on_next_round_actions' => [],
                        'on_disable_actions'    => [],
                    ],
                ],
            ],
            AbilityInterface::ACTIVATE_CONCENTRATION,
            0
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
        return $this->getFactory()->create(
            $unit,
            $this->getAbilityDataProvider()->get($abilityName, $abilityLevel)
        );
    }

    /**
     * @return AbilityFactory
     */
    private function getFactory(): AbilityFactory
    {
        return new AbilityFactory();
    }

    /**
     * @return AbilityDataProviderInterface
     */
    private function getAbilityDataProvider(): AbilityDataProviderInterface
    {
        return new ExampleAbilityDataProvider();
    }
}
