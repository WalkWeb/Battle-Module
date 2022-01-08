<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Ability\Effect;

use Battle\Command\CommandFactory;
use Battle\Unit\Ability\AbilityCollection;
use Battle\Unit\Ability\Effect\HealingPotionAbility;
use Battle\Unit\UnitInterface;
use Exception;
use Tests\AbstractUnitTest;
use Tests\Battle\Factory\UnitFactory;

class HealingPotionAbilityTest extends AbstractUnitTest
{
    // Сообщения применение эффекта на себя
    private const MESSAGE_APPLY_SELF_EN = '<span style="color: #1e72e3">wounded_unit</span> use <img src="/images/icons/ability/234.png" alt="" /> Healing Potion';
    private const MESSAGE_APPLY_SELF_RU = '<span style="color: #1e72e3">wounded_unit</span> использовал <img src="/images/icons/ability/234.png" alt="" /> Лечебное зелье';

    // Сообщения применения эффекта на другого юнита
    private const MESSAGE_APPLY_TO_EN   = '<span style="color: #1e72e3">unit_1</span> use <img src="/images/icons/ability/234.png" alt="" /> Healing Potion on <span style="color: #1e72e3">wounded_unit</span>';
    private const MESSAGE_APPLY_TO_RU   = '<span style="color: #1e72e3">unit_1</span> использовал <img src="/images/icons/ability/234.png" alt="" /> Лечебное зелье на <span style="color: #1e72e3">wounded_unit</span>';

    // Сообщения о лечении от эффекта
    private const MESSAGE_HEAL_EN       = '<span style="color: #1e72e3">wounded_unit</span> restored 15 life from effect <img src="/images/icons/ability/234.png" alt="" /> Healing Potion';
    private const MESSAGE_HEAL_RU       = '<span style="color: #1e72e3">wounded_unit</span> восстановил 15 здоровья от эффекта <img src="/images/icons/ability/234.png" alt="" /> Лечебное зелье';

    /**
     * Тест на создание способности HealingPotionAbility
     *
     * @throws Exception
     */
    public function testHealingPotionAbilitySelfCreateEn(): void
    {
        $name = 'Healing Potion';
        $icon = '/images/icons/ability/234.png';
        $unit = UnitFactory::createByTemplate(11);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = new HealingPotionAbility($unit);

        self::assertEquals($name, $ability->getName());
        self::assertEquals($icon, $ability->getIcon());
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

        $actions = $ability->getAction($enemyCommand, $command);

        foreach ($actions as $action) {
            self::assertTrue($action->canByUsed());
            self::assertEquals(self::MESSAGE_APPLY_SELF_EN, $action->handle());
        }

        $effects = $unit->getEffects();

        self::assertCount(1, $effects);

        foreach ($effects as $effect) {
            $onNextRoundActions = $effect->getOnNextRoundActions();

            foreach ($onNextRoundActions as $effectAction) {
                self::assertTrue($effectAction->canByUsed());
                self::assertEquals(self::MESSAGE_HEAL_EN, $effectAction->handle());
            }
        }

        $ability->usage();

        self::assertFalse($ability->isReady());
    }

    /**
     * Тест на формировании сообщении о применении и использовании эффекта на себя, на русском
     *
     * @throws Exception
     */
    public function testHealingPotionAbilitySelfCreateRuMessage(): void
    {
        $container = $this->getContainerWithRuLanguage();

        $unit = UnitFactory::createByTemplate(11, $container);
        $enemyUnit = UnitFactory::createByTemplate(2, $container);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = new HealingPotionAbility($unit);

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

        $actions = $ability->getAction($enemyCommand, $command);

        foreach ($actions as $action) {
            self::assertTrue($action->canByUsed());
            self::assertEquals(self::MESSAGE_APPLY_SELF_RU, $action->handle());
        }


        $effects = $unit->getEffects();

        self::assertCount(1, $effects);

        foreach ($effects as $effect) {
            $onNextRoundActions = $effect->getOnNextRoundActions();

            foreach ($onNextRoundActions as $effectAction) {
                self::assertTrue($effectAction->canByUsed());
                self::assertEquals(self::MESSAGE_HEAL_RU, $effectAction->handle());
            }
        }

        $ability->usage();

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

        $ability = new HealingPotionAbility($unit);

        foreach ($ability->getAction($enemyCommand, $command) as $action) {
            self::assertTrue($action->canByUsed());
            $action->handle();
        }

        self::assertCount(1, $unit->getEffects());

        self::assertEquals(1, $unit->getLife());

        $this->nextRound($unit);

        self::assertEquals(1 + 15, $unit->getLife());

        $this->nextRound($unit);

        self::assertEquals(1 + 30, $unit->getLife());

        $this->nextRound($unit);

        self::assertEquals(1 + 45, $unit->getLife());

        $this->nextRound($unit);

        self::assertEquals(1 + 60, $unit->getLife());

        $this->nextRound($unit);

        self::assertEquals(1 + 60, $unit->getLife());

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

        $ability = new HealingPotionAbility($unit);

        // Перед применением способности эффекта на юните еще нет - способность может быть применена
        self::assertTrue($ability->canByUsed($enemyCommand, $command));

        foreach ($ability->getAction($enemyCommand, $command) as $action) {
            self::assertTrue($action->canByUsed());
            $action->handle();
        }

        // После появления эффекта на юните - способность уже не может быть применена
        self::assertFalse($ability->canByUsed($enemyCommand, $command));

        // Пропускаем ходы
        for ($i = 0; $i < 10; $i++) {
            $unit->newRound();
        }

        // Эффект исчез - способность опять может быть применена
        self::assertTrue($ability->canByUsed($enemyCommand, $command));
    }

    /**
     * Тест на выявление ошибки, при котором повторное применение эффекта к персонажу добавляло эффект с длительностью 0
     *
     * @throws Exception
     */
    public function testHealingPotionAbilityUpdateDuration(): void
    {
        $unit = UnitFactory::createByTemplate(22);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        // Up concentration
        for ($i = 0; $i < 10; $i++) {
            $unit->newRound();
        }

        $actions = $unit->getAction($enemyCommand, $command);

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

        $actions = $unit->getAction($enemyCommand, $command);

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
     * Тест на проверку сформированного сообщения при применении способности на другого юнита, на английском
     *
     * @throws Exception
     */
    public function testHealingPotionAbilityToEn(): void
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

        $ability = new HealingPotionAbility($unit);

        $collection = new AbilityCollection();
        $collection->add($ability);
        $collection->update($unit);

        $actions = $ability->getAction($enemyCommand, $command);

        foreach ($actions as $action) {
            self::assertTrue($action->canByUsed());
            self::assertEquals(self::MESSAGE_APPLY_TO_EN, $action->handle());
        }
    }

    /**
     * Тест на проверку сформированного сообщения при применении способности на другого юнита, на русском
     *
     * @throws Exception
     */
    public function testHealingPotionAbilityToRu(): void
    {
        $container = $this->getContainerWithRuLanguage();

        $unit = UnitFactory::createByTemplate(1, $container);
        $woundedUnit = UnitFactory::createByTemplate(11, $container);
        $enemyUnit = UnitFactory::createByTemplate(2, $container);
        $command = CommandFactory::create([$unit, $woundedUnit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        // Up concentration
        for ($i = 0; $i < 10; $i++) {
            $unit->newRound();
        }

        $ability = new HealingPotionAbility($unit);

        $collection = new AbilityCollection();
        $collection->add($ability);
        $collection->update($unit);

        $actions = $ability->getAction($enemyCommand, $command);

        foreach ($actions as $action) {
            self::assertTrue($action->canByUsed());
            self::assertEquals(self::MESSAGE_APPLY_TO_RU, $action->handle());
        }
    }

    /**
     * @param UnitInterface $unit
     */
    private function nextRound(UnitInterface $unit): void
    {
        foreach ($unit->getOnNewRoundActions() as $action) {
            if ($action->canByUsed()) {
                $action->handle();
            }
        }
        $unit->newRound();
    }
}
