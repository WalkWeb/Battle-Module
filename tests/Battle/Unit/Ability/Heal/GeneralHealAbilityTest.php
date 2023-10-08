<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Ability\Heal;

use Battle\Action\ActionInterface;
use Battle\Action\HealAction;
use Battle\Command\CommandFactory;
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

class GeneralHealAbilityTest extends AbstractUnitTest
{
    /**
     * Сила заклинания = 24
     *
     * Один из раненых юнитов имеет 90/100 здоровья, другой 1/100, соответственно лечение будет на 10 + 24 = 34
     */
    private const MESSAGE_EN = '<span style="color: #1e72e3">unit_4</span> use <img src="/images/icons/ability/452.png" alt="" /> <span class="ability">General Heal</span> and heal <span style="color: #1e72e3">small_wounded_unit</span> and <span style="color: #1e72e3">wounded_unit</span> on 34 life';
    private const MESSAGE_RU = '<span style="color: #1e72e3">unit_4</span> использовал <img src="/images/icons/ability/452.png" alt="" /> <span class="ability">Общее исцеление</span> и вылечил <span style="color: #1e72e3">small_wounded_unit</span> и <span style="color: #1e72e3">wounded_unit</span> на 34 здоровья';

    // -----------------------------------------------------------------------------------------------------------------
    // ------------------------------------------   Тесты через Ability   ----------------------------------------------
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Тест на создание и применение способности GeneralHealAbility через универсальный объект Ability
     *
     * @throws Exception
     */
    public function testGeneralHealAbilityCreateAndApply(): void
    {
        $name = 'General Heal';
        $icon = '/images/icons/ability/452.png';

        $unit = UnitFactory::createByTemplate(4);
        $slightlyWoundedUnit = UnitFactory::createByTemplate(9);
        $badlyWoundedUnit = UnitFactory::createByTemplate(11);
        $deadUnit =  UnitFactory::createByTemplate(10);
        $enemyUnit = UnitFactory::createByTemplate(2);

        $command = CommandFactory::create([$unit, $slightlyWoundedUnit, $badlyWoundedUnit, $deadUnit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = $this->createAbility($unit);

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
        $actions = $ability->getActions($enemyCommand, $command);

        foreach ($actions as $action) {
            self::assertInstanceOf(HealAction::class, $action);
            self::assertEquals(HealAction::TARGET_ALL_WOUNDED_ALLIES, $action->getTypeTarget());
            self::assertEquals(24, $action->getPower());
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
     * Тест на ситуацию, когда лечить некого через универсальный объект Ability
     *
     * @throws Exception
     */
    public function testGeneralHealAbilityCantByUsed(): void
    {
        $unit = UnitFactory::createByTemplate(4);
        $enemyUnit = UnitFactory::createByTemplate(2);

        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = $this->createAbility($unit);

        self::assertFalse($ability->canByUsed($enemyCommand, $command));
    }

    // -----------------------------------------------------------------------------------------------------------------
    // -------------------------------   Аналогичные тесты через AbilityDataProvider   ---------------------------------
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Тест на создание и применение способности GeneralHealAbility через AbilityDataProvider
     *
     * @throws Exception
     */
    public function testGeneralHealAbilityDataProviderCreateAndApply(): void
    {
        $name = 'General Heal';
        $icon = '/images/icons/ability/452.png';

        $unit = UnitFactory::createByTemplate(4);
        $slightlyWoundedUnit = UnitFactory::createByTemplate(9);
        $badlyWoundedUnit = UnitFactory::createByTemplate(11);
        $deadUnit =  UnitFactory::createByTemplate(10);
        $enemyUnit = UnitFactory::createByTemplate(2);

        $command = CommandFactory::create([$unit, $slightlyWoundedUnit, $badlyWoundedUnit, $deadUnit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = $this->getAbility($unit, $name);

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
        $actions = $ability->getActions($enemyCommand, $command);

        foreach ($actions as $action) {
            self::assertInstanceOf(HealAction::class, $action);
            self::assertEquals(HealAction::TARGET_ALL_WOUNDED_ALLIES, $action->getTypeTarget());
            self::assertEquals(24, $action->getPower());
            self::assertTrue($action->canByUsed());
            $action->handle();
            self::assertEquals(self::MESSAGE_EN, $this->getChat()->addMessage($action));
            self::assertEquals(self::MESSAGE_RU, $this->getChatRu()->addMessage($action));

            // Дополнительное проверяем, что по событию успешно создается анимация
            (new Scenario())->addAnimation($action, new Statistic());
        }

        $ability->usage();
        self::assertTrue($ability->isUsage());
        self::assertFalse($ability->isReady());
    }

    /**
     * Тест на ситуацию, когда лечить некого через AbilityDataProvider
     *
     * @throws Exception
     */
    public function testGeneralHealAbilityDataProviderCantByUsed(): void
    {
        $unit = UnitFactory::createByTemplate(4);
        $enemyUnit = UnitFactory::createByTemplate(2);

        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = $this->getAbility($unit, 'General Heal');

        self::assertFalse($ability->canByUsed($enemyCommand, $command));
    }

    /**
     * @param UnitInterface $unit
     * @return AbilityInterface
     * @throws Exception
     */
    private function createAbility(UnitInterface $unit): AbilityInterface
    {
        $name = 'General Heal';
        $icon = '/images/icons/ability/452.png';

        return new Ability(
            $unit,
            false,
            $name,
            $icon,
            [
                [
                    'type'             => ActionInterface::HEAL,
                    'type_target'      => ActionInterface::TARGET_ALL_WOUNDED_ALLIES,
                    'power'            => 24,
                    'can_be_avoided'   => true,
                    'name'             => $name,
                    'animation_method' => 'heal',
                    'message_method'   => 'healAbility',
                    'icon'             => $icon,
                ],
            ],
            AbilityInterface::ACTIVATE_RAGE,
            [
                WeaponTypeInterface::STAFF,
                WeaponTypeInterface::WAND,
            ],
            0,
        );
    }
}
