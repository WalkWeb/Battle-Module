<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Ability\Effect;

use Battle\Action\ActionInterface;
use Battle\Action\HealAction;
use Battle\Command\CommandFactory;
use Battle\Container\Container;
use Battle\Result\Scenario\Scenario;
use Battle\Result\Statistic\Statistic;
use Battle\Unit\Ability\Ability;
use Battle\Unit\Ability\AbilityCollection;
use Battle\Unit\Ability\AbilityInterface;
use Battle\Unit\UnitInterface;
use Exception;
use Tests\AbstractUnitTest;
use Tests\Factory\UnitFactory;

class HealingPotionAbilityTest extends AbstractUnitTest
{
    // Сообщения применение эффекта на себя
    private const MESSAGE_APPLY_SELF_EN = '<span style="color: #1e72e3">wounded_unit</span> use <img src="/images/icons/ability/234.png" alt="" /> <span class="ability">Healing Potion</span>';
    private const MESSAGE_APPLY_SELF_RU = '<span style="color: #1e72e3">wounded_unit</span> использовал <img src="/images/icons/ability/234.png" alt="" /> <span class="ability">Лечебное зелье</span>';

    // Сообщения применения эффекта на другого юнита
    private const MESSAGE_APPLY_TO_EN   = '<span style="color: #1e72e3">unit_1</span> use <img src="/images/icons/ability/234.png" alt="" /> <span class="ability">Healing Potion</span> on <span style="color: #1e72e3">wounded_unit</span>';
    private const MESSAGE_APPLY_TO_RU   = '<span style="color: #1e72e3">unit_1</span> использовал <img src="/images/icons/ability/234.png" alt="" /> <span class="ability">Лечебное зелье</span> на <span style="color: #1e72e3">wounded_unit</span>';

    // Сообщения о лечении от эффекта
    private const MESSAGE_HEAL_EN       = '<span style="color: #1e72e3">wounded_unit</span> restored 15 life from effect <img src="/images/icons/ability/234.png" alt="" /> <span class="ability">Healing Potion</span>';
    private const MESSAGE_HEAL_RU       = '<span style="color: #1e72e3">wounded_unit</span> восстановил 15 здоровья от эффекта <img src="/images/icons/ability/234.png" alt="" /> <span class="ability">Лечебное зелье</span>';

    // -----------------------------------------------------------------------------------------------------------------
    // ------------------------------------------   Тесты через Ability   ----------------------------------------------
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Тест на создание способности HealingPotionAbility через универсальный объект Ability
     *
     * @throws Exception
     */
    public function testHealingPotionAbilitySelfCreate(): void
    {
        $name = 'Healing Potion';
        $icon = '/images/icons/ability/234.png';

        $unit = UnitFactory::createByTemplate(11);
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

        $actions = $ability->getActions($enemyCommand, $command);

        foreach ($actions as $action) {
            self::assertTrue($action->canByUsed());
            $action->handle();
            self::assertEquals(self::MESSAGE_APPLY_SELF_EN, $this->getChat()->addMessage($action));
            self::assertEquals(self::MESSAGE_APPLY_SELF_RU, $this->getChatRu()->addMessage($action));
        }

        $effects = $unit->getEffects();

        self::assertCount(1, $effects);

        foreach ($effects as $effect) {
            $onNextRoundActions = $effect->getOnNextRoundActions();

            foreach ($onNextRoundActions as $effectAction) {
                self::assertTrue($effectAction->canByUsed());
                $effectAction->handle();
                self::assertEquals(self::MESSAGE_HEAL_EN, $this->getChat()->addMessage($effectAction));
                self::assertEquals(self::MESSAGE_HEAL_RU, $this->getChatRu()->addMessage($effectAction));
            }
        }

        $ability->usage();
        self::assertTrue($ability->isUsage());
        self::assertFalse($ability->isReady());
    }

    /**
     * @throws Exception
     */
    public function testHealingPotionAbilityApply(): void
    {
        $unit = UnitFactory::createByTemplate(11);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $power = 15;
        $ability = $this->createAbility($unit);

        foreach ($ability->getActions($enemyCommand, $command) as $action) {
            self::assertTrue($action->canByUsed());
            $action->handle();
        }

        self::assertCount(1, $unit->getEffects());

        self::assertEquals(1, $unit->getLife());

        foreach ($unit->getEffects() as $effect) {
            foreach ($effect->getOnNextRoundActions() as $effectAction) {
                self::assertEquals(0, $effectAction->getFactualPower());
            }
        }

        for ($i = 1; $i < 5; $i++) {

            // Применяем события от эффектов на юните
            foreach ($unit->getBeforeActions() as $beforeAction) {
                if ($beforeAction->canByUsed()) {
                    $beforeAction->handle();
                }
            }

            // Проверяем FactualPower после применения - он не должен меняться
            self::assertEquals(1 + $power * $i, $unit->getLife());

            foreach ($unit->getEffects() as $effect) {
                foreach ($effect->getOnNextRoundActions() as $effectAction) {
                    self::assertEquals($power, $effectAction->getFactualPower());
                }
            }

            // Обновляем длительность эффектов. Длительность эффектов обновляется в getAfterActions()
            foreach ($unit->getAfterActions() as $afterAction) {
                if ($afterAction->canByUsed()) {
                    $afterAction->handle();
                }
            }
        }

        self::assertCount(0, $unit->getEffects());
    }

    /**
     * @throws Exception
     */
    public function testHealingPotionAbilityCanByUsed(): void
    {
        $unit = UnitFactory::createByTemplate(11);
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
    public function testHealingPotionAbilityUpdateDuration(): void
    {
        $unit = UnitFactory::createByTemplate(11);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = $this->createAbility($unit);

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

        $actions = $ability->getActions($enemyCommand, $command);

        foreach ($actions as $action) {
            self::assertTrue($action->canByUsed());
            $action->handle();
        }

        // Проверяем, что длительность = 4
        foreach ($unit->getEffects() as $effect) {
            self::assertEquals(4, $effect->getDuration());
        }

        // Пропускаем ходы
        for ($i = 0; $i < 10; $i++) {
            $unit->newRound();
        }

        // Применяем способность еще раз
        for ($i = 0; $i < 10; $i++) {
            $unit->newRound();
        }

        $actions = $unit->getActions($enemyCommand, $command);

        foreach ($actions as $action) {
            self::assertTrue($action->canByUsed());
            $action->handle();
        }

        // Проверяем еще раз, что при повторном применении эффекта длительность = 4
        foreach ($unit->getEffects() as $effect) {
            self::assertEquals(4, $effect->getDuration());
        }
    }

    // -----------------------------------------------------------------------------------------------------------------
    // -------------------------------   Аналогичные тесты через AbilityDataProvider   ---------------------------------
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Тест на создание способности HealingPotionAbility через универсальный объект Ability
     *
     * @throws Exception
     */
    public function testHealingPotionAbilityDataProviderSelfCreateEn(): void
    {
        $name = 'Healing Potion';
        $icon = '/images/icons/ability/234.png';

        $unit = UnitFactory::createByTemplate(11);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = $this->createAbilityByDataProvider($unit, 'Healing Potion');

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

        $actions = $ability->getActions($enemyCommand, $command);

        foreach ($actions as $action) {
            self::assertTrue($action->canByUsed());
            $action->handle();
            self::assertEquals(self::MESSAGE_APPLY_SELF_EN, $this->getChat()->addMessage($action));

            // Дополнительное проверяем, что по событию успешно создается анимация
            (new Scenario())->addAnimation($action, new Statistic());
        }

        $effects = $unit->getEffects();

        self::assertCount(1, $effects);

        foreach ($effects as $effect) {
            $onNextRoundActions = $effect->getOnNextRoundActions();

            foreach ($onNextRoundActions as $effectAction) {
                self::assertTrue($effectAction->canByUsed());
                $effectAction->handle();
                self::assertEquals(self::MESSAGE_HEAL_EN, $this->getChat()->addMessage($effectAction));

                // Дополнительное проверяем, что по событию успешно создается анимация
                (new Scenario())->addAnimation($action, new Statistic());
            }
        }

        $ability->usage();
        self::assertTrue($ability->isUsage());
        self::assertFalse($ability->isReady());
    }

    /**
     * Тест на формировании сообщении о применении и использовании эффекта на себя, на русском через универсальный
     * объект Ability
     *
     * @throws Exception
     */
    public function testHealingPotionAbilityDataProviderSelfCreateRuMessage(): void
    {
        $container = $this->getContainerWithRuLanguage();

        $unit = UnitFactory::createByTemplate(11, $container);
        $enemyUnit = UnitFactory::createByTemplate(2, $container);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = $this->createAbilityByDataProvider($unit, 'Healing Potion');

        self::assertEquals($unit, $ability->getUnit());
        self::assertFalse($ability->isReady());
        self::assertTrue($ability->canByUsed($enemyCommand, $command));

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

        $actions = $ability->getActions($enemyCommand, $command);

        foreach ($actions as $action) {
            self::assertTrue($action->canByUsed());
            $action->handle();
            self::assertEquals(self::MESSAGE_APPLY_SELF_RU, $this->getChatRu()->addMessage($action));
        }

        $effects = $unit->getEffects();

        self::assertCount(1, $effects);

        foreach ($effects as $effect) {
            $onNextRoundActions = $effect->getOnNextRoundActions();

            foreach ($onNextRoundActions as $effectAction) {
                self::assertTrue($effectAction->canByUsed());
                $effectAction->handle();
                self::assertEquals(self::MESSAGE_HEAL_RU, $this->getChatRu()->addMessage($effectAction));
            }
        }

        $ability->usage();

        self::assertFalse($ability->isReady());
    }

    /**
     * @throws Exception
     */
    public function testHealingPotionDataProviderAbilityApply(): void
    {
        $unit = UnitFactory::createByTemplate(11);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $power = 15;
        $ability = $this->createAbilityByDataProvider($unit, 'Healing Potion');

        foreach ($ability->getActions($enemyCommand, $command) as $action) {
            self::assertTrue($action->canByUsed());
            $action->handle();
        }

        self::assertCount(1, $unit->getEffects());

        self::assertEquals(1, $unit->getLife());

        foreach ($unit->getEffects() as $effect) {
            foreach ($effect->getOnNextRoundActions() as $effectAction) {
                self::assertEquals(0, $effectAction->getFactualPower());
            }
        }

        for ($i = 1; $i < 5; $i++) {

            // Применяем события от эффектов на юните
            foreach ($unit->getBeforeActions() as $beforeAction) {
                if ($beforeAction->canByUsed()) {
                    $beforeAction->handle();
                }
            }

            // Проверяем FactualPower после применения - он не должен меняться
            self::assertEquals(1 + $power * $i, $unit->getLife());

            foreach ($unit->getEffects() as $effect) {
                foreach ($effect->getOnNextRoundActions() as $effectAction) {
                    self::assertEquals($power, $effectAction->getFactualPower());
                }
            }

            // Обновляем длительность эффектов. Длительность эффектов обновляется в getAfterActions()
            foreach ($unit->getAfterActions() as $afterAction) {
                if ($afterAction->canByUsed()) {
                    $afterAction->handle();
                }
            }
        }

        self::assertCount(0, $unit->getEffects());
    }

    /**
     * @throws Exception
     */
    public function testHealingPotionDataProviderAbilityCanByUsed(): void
    {
        $unit = UnitFactory::createByTemplate(11);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = $this->createAbilityByDataProvider($unit, 'Healing Potion');

        // Перед применением способности эффекта на юните еще нет - способность может быть применена
        self::assertTrue($ability->canByUsed($enemyCommand, $command));

        foreach ($ability->getActions($enemyCommand, $command) as $action) {
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
    public function testHealingPotionAbilityDataProviderUpdateDuration(): void
    {
        $unit = UnitFactory::createByTemplate(11);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = $this->createAbilityByDataProvider($unit, 'Healing Potion');

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

        $actions = $ability->getActions($enemyCommand, $command);

        foreach ($actions as $action) {
            self::assertTrue($action->canByUsed());
            $action->handle();
        }

        // Проверяем, что длительность = 4
        foreach ($unit->getEffects() as $effect) {
            self::assertEquals(4, $effect->getDuration());
        }

        // Пропускаем ходы
        for ($i = 0; $i < 10; $i++) {
            $unit->newRound();
        }

        // Применяем способность еще раз
        for ($i = 0; $i < 10; $i++) {
            $unit->newRound();
        }

        $actions = $unit->getActions($enemyCommand, $command);

        foreach ($actions as $action) {
            self::assertTrue($action->canByUsed());
            $action->handle();
        }

        // Проверяем еще раз, что при повторном применении эффекта длительность = 4
        foreach ($unit->getEffects() as $effect) {
            self::assertEquals(4, $effect->getDuration());
        }
    }

    /**
     * Тест на проверку сформированного сообщения при применении способности на другого юнита через универсальный объект
     * Ability
     *
     * @throws Exception
     */
    public function testHealingPotionAbilityToMessage(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $woundedUnit = UnitFactory::createByTemplate(11);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit, $woundedUnit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        // Up concentration
        for ($i = 0; $i < 10; $i++) {
            $unit->newRound();
        }

        $ability = $this->createAbility($unit);

        $collection = new AbilityCollection();
        $collection->add($ability);
        $collection->update($unit);

        $actions = $ability->getActions($enemyCommand, $command);

        foreach ($actions as $action) {
            self::assertTrue($action->canByUsed());
            $action->handle();
            self::assertEquals(self::MESSAGE_APPLY_TO_EN, $this->getChat()->addMessage($action));
            self::assertEquals(self::MESSAGE_APPLY_TO_RU, $this->getChatRu()->addMessage($action));
        }
    }

    /**
     * @param UnitInterface $unit
     * @return AbilityInterface
     * @throws Exception
     */
    private function createAbility(UnitInterface $unit): AbilityInterface
    {
        $name = 'Healing Potion';
        $icon = '/images/icons/ability/234.png';

        return new Ability(
            $unit,
            false,
            $name,
            $icon,
            [
                [
                    'type'           => ActionInterface::EFFECT,
                    'type_target'    => ActionInterface::TARGET_WOUNDED_ALLIES_EFFECT,
                    'name'           => $name,
                    'icon'           => $icon,
                    'message_method' => 'applyEffect',
                    'effect'         => [
                        'name'                  => $name,
                        'icon'                  => $icon,
                        'duration'              => 4,
                        'on_apply_actions'      => [],
                        'on_next_round_actions' => [
                            [
                                'type'             => ActionInterface::HEAL,
                                'type_target'      => ActionInterface::TARGET_SELF,
                                'name'             => $name,
                                'power'            => 15,
                                'animation_method' => HealAction::EFFECT_ANIMATION_METHOD,
                                'message_method'   => HealAction::EFFECT_MESSAGE_METHOD,
                                'icon'             => $icon,
                            ],
                        ],
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
        $container = new Container();

        return $container->getAbilityFactory()->create(
            $unit,
            $container->getAbilityDataProvider()->get($abilityName, $abilityLevel)
        );
    }
}
