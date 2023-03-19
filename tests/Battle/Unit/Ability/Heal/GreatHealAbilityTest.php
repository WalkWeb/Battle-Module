<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Ability\Heal;

use Battle\Action\ActionInterface;
use Battle\Container\Container;
use Battle\Result\Scenario\Scenario;
use Battle\Result\Statistic\Statistic;
use Battle\Unit\Ability\Ability;
use Battle\Unit\Ability\AbilityInterface;
use Battle\Unit\UnitInterface;
use Battle\Weapon\Type\WeaponTypeInterface;
use Exception;
use Battle\Action\DamageAction;
use Battle\Action\HealAction;
use Battle\Command\CommandFactory;
use Tests\AbstractUnitTest;
use Tests\Factory\UnitFactory;
use Battle\Unit\Ability\AbilityCollection;

class GreatHealAbilityTest extends AbstractUnitTest
{
    private const MESSAGE_EN = '<span style="color: #1e72e3">unit_5</span> use <img src="/images/icons/ability/196.png" alt="" /> <span class="ability">Great Heal</span> and heal <span style="color: #1e72e3">unit_5</span> on 30 life';
    private const MESSAGE_RU = '<span style="color: #1e72e3">unit_5</span> использовал <img src="/images/icons/ability/196.png" alt="" /> <span class="ability">Сильное Лечение</span> и вылечил <span style="color: #1e72e3">unit_5</span> на 30 здоровья';

    // -----------------------------------------------------------------------------------------------------------------
    // ------------------------------------------   Тесты через Ability   ----------------------------------------------
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Тест на создание и применение способности GeneralHealAbility через универсальный объект Ability
     *
     * @throws Exception
     */
    public function testGreatHealAbilityCreateAndApply(): void
    {
        $name = 'Great Heal';
        $icon = '/images/icons/ability/196.png';

        $unit = UnitFactory::createByTemplate(5);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = $this->createAbility($unit);

        self::assertEquals($name, $ability->getName());
        self::assertEquals($icon, $ability->getIcon());
        self::assertEquals($unit, $ability->getUnit());
        self::assertFalse($ability->isReady());
        self::assertFalse($ability->canByUsed($enemyCommand, $command));
        self::assertFalse($ability->isDisposable());
        self::assertFalse($ability->isUsage());

        // Наносим урон юниту, чтобы способность перешла в "возможную для использования"
        $damage = new DamageAction(
            $this->getContainer(),
            $enemyUnit,
            $command,
            $enemyCommand,
            DamageAction::TARGET_RANDOM_ENEMY,
            true,
            DamageAction::DEFAULT_NAME,
            DamageAction::UNIT_ANIMATION_METHOD,
            DamageAction::DEFAULT_MESSAGE_METHOD,
            $enemyUnit->getOffense()
        );

        $damage->handle();

        // Проверяем, что юнит получил 30 урона
        self::assertEquals($unit->getTotalLife() - 30, $unit->getLife());

        // После чего, способность на лечение может быть использована
        self::assertTrue($ability->canByUsed($enemyCommand, $command));

        // Активируем способность
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
            self::assertInstanceOf(HealAction::class, $action);
            self::assertEquals(60, $action->getPower());
            self::assertTrue($action->canByUsed());
            $action->handle();
            self::assertEquals(30, $action->getFactualPowerByUnit($unit));
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
     * @throws Exception
     */
    public function testCreatHealAbilityCantByUsed(): void
    {
        $unit = UnitFactory::createByTemplate(5);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = $this->createAbility($unit);

        self::assertFalse($ability->canByUsed($enemyCommand, $command));
    }

    /**
     * Тест на создание и применение способности GeneralHealAbility через AbilityDataProvider
     *
     * @throws Exception
     */
    public function testGreatHealAbilityDataProviderCreateAndApply(): void
    {
        $name = 'Great Heal';
        $icon = '/images/icons/ability/196.png';

        $unit = UnitFactory::createByTemplate(5);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = $this->createAbilityByDataProvider($unit, $name);

        self::assertEquals($name, $ability->getName());
        self::assertEquals($icon, $ability->getIcon());
        self::assertEquals($unit, $ability->getUnit());
        self::assertFalse($ability->isReady());
        self::assertFalse($ability->canByUsed($enemyCommand, $command));
        self::assertFalse($ability->isDisposable());
        self::assertFalse($ability->isUsage());

        // Наносим урон юниту, чтобы способность перешла в "возможную для использования"
        $damage = new DamageAction(
            $this->getContainer(),
            $enemyUnit,
            $command,
            $enemyCommand,
            DamageAction::TARGET_RANDOM_ENEMY,
            true,
            DamageAction::DEFAULT_NAME,
            DamageAction::UNIT_ANIMATION_METHOD,
            DamageAction::DEFAULT_MESSAGE_METHOD,
            $enemyUnit->getOffense()
        );

        $damage->handle();

        // Проверяем, что юнит получил 30 урона
        self::assertEquals($unit->getTotalLife() - 30, $unit->getLife());

        // После чего, способность на лечение может быть использована
        self::assertTrue($ability->canByUsed($enemyCommand, $command));

        // Активируем способность
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
            self::assertInstanceOf(HealAction::class, $action);
            self::assertEquals(60, $action->getPower());
            self::assertTrue($action->canByUsed());
            $action->handle();
            self::assertEquals(30, $action->getFactualPowerByUnit($unit));
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
     * @throws Exception
     */
    public function testGreatHealAbilityDataProviderCantByUsed(): void
    {
        $unit = UnitFactory::createByTemplate(5);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = $this->createAbilityByDataProvider($unit, 'Great Heal');

        self::assertFalse($ability->canByUsed($enemyCommand, $command));
    }

    // -----------------------------------------------------------------------------------------------------------------
    // -------------------------------   Аналогичные тесты через AbilityDataProvider   ---------------------------------
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * @param UnitInterface $unit
     * @return AbilityInterface
     * @throws Exception
     */
    private function createAbility(UnitInterface $unit): AbilityInterface
    {
        $name = 'Great Heal';
        $icon = '/images/icons/ability/196.png';

        return new Ability(
            $unit,
            false,
            $name,
            $icon,
            [
                [
                    'type'             => ActionInterface::HEAL,
                    'type_target'      => ActionInterface::TARGET_ALL_WOUNDED_ALLIES,
                    'power'            => 60,
                    'can_be_avoided'   => true,
                    'name'             => $name,
                    'animation_method' => 'heal',
                    'message_method'   => 'healAbility',
                    'icon'             => $icon,
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
