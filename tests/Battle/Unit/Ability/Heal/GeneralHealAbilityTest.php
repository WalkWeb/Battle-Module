<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Ability\Heal;

use Battle\Action\DamageAction;
use Battle\Action\HealAction;
use Battle\Command\CommandFactory;
use Battle\Unit\Ability\AbilityCollection;
use Battle\Unit\Ability\Heal\GeneralHealAbility;
use Exception;
use Tests\AbstractUnitTest;
use Tests\Battle\Factory\UnitFactory;

class GeneralHealAbilityTest extends AbstractUnitTest
{
    /**
     * Юнит совершающий действие имеет Damage = 20, сила способности x1.2, соответственно лечение будет силой в 24
     *
     * Один из раненых юнитов имеет 90/100 здоровья, другой 1/100, соответственно лечение будет на 10 + 24 = 34
     */
    private const MESSAGE_EN = '<span style="color: #1e72e3">unit_1</span> use <img src="/images/icons/ability/452.png" alt="" /> <span class="ability">General Heal</span> and heal <span style="color: #1e72e3">small_wounded_unit</span> and <span style="color: #1e72e3">wounded_unit</span> on 34 life';
    private const MESSAGE_RU = '<span style="color: #1e72e3">unit_1</span> использовал <img src="/images/icons/ability/452.png" alt="" /> <span class="ability">Общее исцеление</span> и вылечил <span style="color: #1e72e3">small_wounded_unit</span> и <span style="color: #1e72e3">wounded_unit</span> на 34 здоровья';

    /**
     * Тест на создание и применение способности GeneralHealAbility
     *
     * @throws Exception
     */
    public function testGeneralHealAbilityCreateAndApply(): void
    {
        $name = 'General Heal';
        $icon = '/images/icons/ability/452.png';

        $unit = UnitFactory::createByTemplate(1);
        $slightlyWoundedUnit = UnitFactory::createByTemplate(9);
        $badlyWoundedUnit = UnitFactory::createByTemplate(11);
        $deadUnit =  UnitFactory::createByTemplate(10);
        $enemyUnit = UnitFactory::createByTemplate(2);

        $command = CommandFactory::create([$unit, $slightlyWoundedUnit, $badlyWoundedUnit, $deadUnit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = new GeneralHealAbility($unit);

        self::assertEquals($name, $ability->getName());
        self::assertEquals($icon, $ability->getIcon());
        self::assertEquals($unit, $ability->getUnit());
        self::assertFalse($ability->isDisposable());
        self::assertFalse($ability->isUsage());

        // Способность не готова к использованию - т.к. ярость у юнита не полная
        self::assertFalse($ability->isReady());

        // Но она может примениться, так как в команде есть раненые юниты
        self::assertTrue($ability->canByUsed($enemyCommand, $command));

        // Активируем способность
        $collection = new AbilityCollection();
        $collection->add($ability);

        // Up concentration
        for ($i = 0; $i < 20; $i++) {
            $unit->newRound();
        }

        $collection->update($unit);

        // Ярость у юнита полная, и теперь способность готова к использованию
        self::assertTrue($ability->isReady());

        // Применяем способность
        $actions = $ability->getAction($enemyCommand, $command);

        foreach ($actions as $action) {
            self::assertInstanceOf(HealAction::class, $action);
            self::assertEquals(HealAction::TARGET_ALL_WOUNDED_ALLIES, $action->getTypeTarget());
            self::assertEquals((int)($unit->getOffense()->getDamage() * 1.2), $action->getPower());
            self::assertTrue($action->canByUsed());
            $action->handle();
            self::assertEquals(self::MESSAGE_EN, $this->getChat()->addMessage($action));
            self::assertEquals(self::MESSAGE_RU, $this->getChatRu()->addMessage($action));
        }

        $ability->usage();
        self::assertTrue($ability->isUsage());
        self::assertFalse($ability->isReady());
    }

    /**
     * Тест на ситуацию, когда лечить некого
     *
     * @throws Exception
     */
    public function testGeneralHealAbilityCantByUsed(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);

        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);


        // Создаем напрямую, и проверяем, что способность не может быть применена
        $ability = new GeneralHealAbility($unit);
        self::assertFalse($ability->canByUsed($enemyCommand, $command));

        // Up concentration
        for ($i = 0; $i < 10; $i++) {
            $unit->newRound();
        }

        // По этому, получая способности через getAction - получаем DamageAction, а не GreatHealAbility
        $abilities = $unit->getActions($enemyCommand, $command);

        foreach ($abilities as $ability) {
            self::assertInstanceOf(DamageAction::class, $ability);
        }
    }
}
