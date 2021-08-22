<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Ability\Effect;

use Battle\Command\CommandFactory;
use Battle\Unit\Ability\AbilityCollection;
use Battle\Unit\Ability\Effect\HealingPotionAbility;
use Exception;
use PHPUnit\Framework\TestCase;
use Tests\Battle\Factory\UnitFactory;

class HealingPotionAbilityTest extends TestCase
{
    /**
     * Тест на создание способности HealingPotionAbility
     *
     * @throws Exception
     */
    public function testHealingPotionAbilityCreate(): void
    {
        $name = 'Healing Potion';
        $icon = '/images/icons/ability/234.png';
        $unit = UnitFactory::createByTemplate(1);
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
            $action->handle();
        }

        self::assertCount(1, $unit->getEffects());

        self::assertEquals(1, $unit->getLife());

        $unit->newRound();

        self::assertEquals(1 + 15, $unit->getLife());

        $unit->newRound();

        self::assertEquals(1 + 30, $unit->getLife());

        $unit->newRound();

        self::assertEquals(1 + 45, $unit->getLife());

        $unit->newRound();

        self::assertEquals(1 + 60, $unit->getLife());

        $unit->newRound();

        self::assertEquals(1 + 60, $unit->getLife());

        self::assertCount(0, $unit->getEffects());
    }

    /**
     * @throws Exception
     */
    public function testHealingPotionAbilityCanBeUsed(): void
    {
        $unit = UnitFactory::createByTemplate(11);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = new HealingPotionAbility($unit);

        // Перед применением способности эффекта на юните еще нет - способность может быть применена
        self::assertTrue($ability->canByUsed($enemyCommand, $command));

        foreach ($ability->getAction($enemyCommand, $command) as $action) {
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
            $action->handle();
        }

        // Проверяем еще раз, что при повторном применении эффекта длительность = 4
        foreach ($unit->getEffects() as $effect) {
            self::assertEquals(4, $effect->getDuration());
        }
    }
}
