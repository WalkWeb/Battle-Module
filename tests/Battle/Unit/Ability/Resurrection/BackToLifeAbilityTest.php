<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Ability\Resurrection;

use Battle\Action\ActionInterface;
use Battle\Action\ResurrectionAction;
use Battle\Command\CommandFactory;
use Battle\Result\Scenario\Scenario;
use Battle\Result\Statistic\Statistic;
use Battle\Unit\Ability\Ability;
use Battle\Unit\Ability\AbilityFactory;
use Battle\Unit\Ability\AbilityInterface;
use Battle\Unit\Ability\DataProvider\AbilityDataProviderInterface;
use Battle\Unit\Ability\DataProvider\ExampleAbilityDataProvider;
use Battle\Unit\UnitInterface;
use Exception;
use Tests\AbstractUnitTest;
use Tests\Battle\Factory\UnitFactory;

class BackToLifeAbilityTest extends AbstractUnitTest
{
    private const MESSAGE_EN = '<span style="color: #1e72e3">unit_1</span> use <img src="/images/icons/ability/053.png" alt="" /> <span class="ability">Back to Life</span> and resurrected <span style="color: #1e72e3">dead_unit</span>';
    private const MESSAGE_RU = '<span style="color: #1e72e3">unit_1</span> использовал <img src="/images/icons/ability/053.png" alt="" /> <span class="ability">Возвращение к жизни</span> и воскресил <span style="color: #1e72e3">dead_unit</span>';

    // -----------------------------------------------------------------------------------------------------------------
    // ------------------------------------------   Тесты через Ability   ----------------------------------------------
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Создание и применение способности BackToLifeAbility через универсальный объект Ability
     *
     * @throws Exception
     */
    public function testBackToLifeAbilityCreateAndApply(): void
    {
        $name = 'Back to Life';
        $icon = '/images/icons/ability/053.png';
        $power = 30;

        $unit = UnitFactory::createByTemplate(1);
        $deadUnit = UnitFactory::createByTemplate(10);
        $enemyUnit = UnitFactory::createByTemplate(2);

        $command = CommandFactory::create([$unit, $deadUnit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = $this->createAbility($unit);

        // Сверяем базовые параметры
        self::assertEquals($name, $ability->getName());
        self::assertEquals($icon, $ability->getIcon());
        self::assertFalse($ability->isReady());
        self::assertFalse($ability->isDisposable());
        self::assertFalse($ability->isUsage());

        // Увеличиваем ярость у юнита до максимальной
        for ($i = 0; $i < 20; $i++) {
            $unit->newRound();
        }

        $ability->update($unit);

        // Способность перешла в статус готовой для использования
        self::assertTrue($ability->isReady());
        self::assertTrue($ability->canByUsed($enemyCommand, $command));

        $actions = $ability->getActions($enemyCommand, $command);

        // До применения способности юнит мертв
        self::assertEquals(0, $deadUnit->getLife());

        foreach ($actions as $action) {
            self::assertInstanceOf(ResurrectionAction::class, $action);
            self::assertEquals($name, $action->getNameAction());
            self::assertEquals($power, $action->getPower());
            self::assertTrue($action->canByUsed());
            $action->handle();
            self::assertEquals(self::MESSAGE_EN, $this->getChat()->addMessage($action));
            self::assertEquals(self::MESSAGE_RU, $this->getChatRu()->addMessage($action));
        }

        // После применения способности юнит восстановил 30% здоровья (30 здоровья от 100 максимальных)
        self::assertEquals(30, $deadUnit->getLife());

        $ability->usage();

        // Проверяем, что ярость у юнита = 0, а способность вновь не готова к использованию
        self::assertEquals(0, $unit->getRage());
        self::assertFalse($ability->isReady());
        self::assertTrue($ability->isUsage());
    }

    /**
     * Тест ситуации, когда мертвых юнитов нет, и способность BackToLifeAbility не может быть применена, через
     * универсальный объект Ability
     *
     * @throws Exception
     */
    public function testBackToLifeAbilityCantBeUsed(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $woundedUnit = UnitFactory::createByTemplate(11);
        $enemyUnit = UnitFactory::createByTemplate(2);

        $command = CommandFactory::create([$unit, $woundedUnit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = $this->createAbility($unit);

        self::assertFalse($ability->canByUsed($enemyCommand, $command));
    }

    // -----------------------------------------------------------------------------------------------------------------
    // -------------------------------   Аналогичные тесты через AbilityDataProvider   ---------------------------------
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Создание и применение способности BackToLifeAbility через AbilityDataProvider
     *
     * @throws Exception
     */
    public function testBackToLifeAbilityDataProviderCreateAndApply(): void
    {
        $name = 'Back to Life';
        $icon = '/images/icons/ability/053.png';
        $power = 30;

        $unit = UnitFactory::createByTemplate(1);
        $deadUnit = UnitFactory::createByTemplate(10);
        $enemyUnit = UnitFactory::createByTemplate(2);

        $command = CommandFactory::create([$unit, $deadUnit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = $this->createAbilityByDataProvider($unit, $name);

        // Сверяем базовые параметры
        self::assertEquals($name, $ability->getName());
        self::assertEquals($icon, $ability->getIcon());
        self::assertFalse($ability->isReady());
        self::assertFalse($ability->isDisposable());
        self::assertFalse($ability->isUsage());

        // Увеличиваем ярость у юнита до максимальной
        for ($i = 0; $i < 20; $i++) {
            $unit->newRound();
        }

        $ability->update($unit);

        // Способность перешла в статус готовой для использования
        self::assertTrue($ability->isReady());
        self::assertTrue($ability->canByUsed($enemyCommand, $command));

        $actions = $ability->getActions($enemyCommand, $command);

        // До применения способности юнит мертв
        self::assertEquals(0, $deadUnit->getLife());

        foreach ($actions as $action) {
            self::assertInstanceOf(ResurrectionAction::class, $action);
            self::assertEquals($name, $action->getNameAction());
            self::assertEquals($power, $action->getPower());
            self::assertTrue($action->canByUsed());
            $action->handle();
            self::assertEquals(self::MESSAGE_EN, $this->getChat()->addMessage($action));
            self::assertEquals(self::MESSAGE_RU, $this->getChatRu()->addMessage($action));

            // Дополнительное проверяем, что по событию успешно создается анимация
            (new Scenario())->addAnimation($action, new Statistic());
        }

        // После применения способности юнит восстановил 30% здоровья (30 здоровья от 100 максимальных)
        self::assertEquals(30, $deadUnit->getLife());

        $ability->usage();

        // Проверяем, что ярость у юнита = 0, а способность вновь не готова к использованию
        self::assertEquals(0, $unit->getRage());
        self::assertFalse($ability->isReady());
        self::assertTrue($ability->isUsage());
    }

    /**
     * Тест ситуации, когда мертвых юнитов нет, и способность BackToLifeAbility не может быть применена, через
     * AbilityDataProvider
     *
     * @throws Exception
     */
    public function testBackToLifeAbilityDataProviderCantBeUsed(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $woundedUnit = UnitFactory::createByTemplate(11);
        $enemyUnit = UnitFactory::createByTemplate(2);

        $command = CommandFactory::create([$unit, $woundedUnit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = $this->createAbilityByDataProvider($unit, 'Back to Life');

        self::assertFalse($ability->canByUsed($enemyCommand, $command));
    }

    /**
     * @param UnitInterface $unit
     * @return AbilityInterface
     * @throws Exception
     */
    private function createAbility(UnitInterface $unit): AbilityInterface
    {
        $name = 'Back to Life';
        $icon = '/images/icons/ability/053.png';

        return new Ability(
            $unit,
            false,
            $name,
            $icon,
            [
                [
                    'type'           => ActionInterface::RESURRECTION,
                    'type_target'    => ActionInterface::TARGET_DEAD_ALLIES,
                    'power'          => 30,
                    'name'           => $name,
                    'icon'           => $icon,
                ],
            ],
            AbilityInterface::ACTIVATE_RAGE,
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
