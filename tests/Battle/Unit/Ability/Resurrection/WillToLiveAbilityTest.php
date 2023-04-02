<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Ability\Resurrection;

use Battle\Action\ActionCollection;
use Battle\Action\ActionInterface;
use Battle\Action\ResurrectionAction;
use Battle\Command\CommandFactory;
use Battle\Command\CommandInterface;
use Battle\Container\Container;
use Battle\Container\ContainerInterface;
use Battle\Response\Scenario\Scenario;
use Battle\Response\Statistic\Statistic;
use Battle\Unit\Ability\Ability;
use Battle\Unit\Ability\AbilityInterface;
use Battle\Unit\UnitInterface;
use Exception;
use Tests\AbstractUnitTest;
use Tests\Factory\UnitFactory;

class WillToLiveAbilityTest extends AbstractUnitTest
{
    private const MESSAGE_EN = '<span style="color: #1e72e3">dead_unit</span> died, but due to the innate ability <img src="/images/icons/ability/429.png" alt="" /> <span class="ability">Will to live</span> came back to life';
    private const MESSAGE_RU = '<span style="color: #1e72e3">dead_unit</span> умер, но за счет врожденной способности <img src="/images/icons/ability/429.png" alt="" /> <span class="ability">Воля к жизни</span> вернулся к жизни';

    // -----------------------------------------------------------------------------------------------------------------
    // ------------------------------------------   Тесты через Ability   ----------------------------------------------
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Тест на создание способности WillToLiveAbility через универсальный объект Ability
     *
     * @throws Exception
     */
    public function testWillToLiveAbilityCreate(): void
    {
        $name = 'Will to live';
        $icon = '/images/icons/ability/429.png';

        $container = new Container();
        $unit = UnitFactory::createByTemplate(10, $container);
        $enemyUnit = UnitFactory::createByTemplate(2, $container);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = $this->createAbility($unit);

        self::assertEquals($name, $ability->getName());
        self::assertEquals($icon, $ability->getIcon());
        self::assertEquals($unit, $ability->getUnit());
        self::assertFalse($ability->isReady());
        self::assertTrue($ability->canByUsed($enemyCommand, $command));
        self::assertTrue($ability->isDisposable());
        self::assertFalse($ability->isUsage());

        // Активируем - созданный юнит изначально мертв
        $ability->update($unit, true);

        self::assertTrue($ability->isReady());

        self::assertEquals(
            $this->getWillToLiveActions($container, $unit, $enemyCommand, $command),
            $ability->getActions($enemyCommand, $command)
        );

        $ability->usage();
        self::assertTrue($ability->isUsage());
        self::assertFalse($ability->isReady());
    }

    /**
     * Тест на применение способности WillToLiveAbility через универсальный объект Ability
     *
     * @throws Exception
     */
    public function testWillToLiveAbilityApply(): void
    {
        $unit = UnitFactory::createByTemplate(10);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = $this->createAbility($unit);

        // Изначально юнит мертв
        self::assertEquals(0, $unit->getLife());

        // Активируем способность
        // Вначале используем метод в обычном режиме - для 100% покрытия кода тестами
        $ability->update($unit);
        // Но чтобы активировать её точно - используем в тестовом режиме
        $ability->update($unit, true);

        // Применяем способность
        foreach ($ability->getActions($enemyCommand, $command) as $action) {
            self::assertTrue($action->canByUsed());
            $action->handle();
            self::assertEquals(self::MESSAGE_EN, $this->getChat()->addMessage($action));
            self::assertEquals(self::MESSAGE_RU, $this->getChatRu()->addMessage($action));
        }

        $ability->usage();

        // Проверяем, что здоровье восстановилось
        self::assertEquals($unit->getTotalLife() / 2, $unit->getLife());

        self::assertFalse($ability->isReady());
    }

    /**
     * Тест на проверку того, что способность не может использоваться повторно через универсальный объект Ability
     *
     * @throws Exception
     */
    public function testWillToLiveAbilityNoRepeatUsage(): void
    {
        $unit = UnitFactory::createByTemplate(10);
        $enemyUnit = UnitFactory::createByTemplate(12);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = $this->createAbility($unit);

        // Изначально юнит мертв
        self::assertEquals(0, $unit->getLife());

        // Активируем способность
        $ability->update($unit, true);

        // Проверяем, что способность может быть использована
        self::assertTrue($ability->isReady());

        // Применяем способность
        foreach ($ability->getActions($enemyCommand, $command) as $action) {
            self::assertTrue($action->canByUsed());
            $action->handle();
            self::assertEquals(self::MESSAGE_EN, $this->getChat()->addMessage($action));
            self::assertEquals(self::MESSAGE_RU, $this->getChatRu()->addMessage($action));
        }

        $ability->usage();

        // Проверяем, что здоровье восстановилось
        self::assertEquals($unit->getTotalLife() / 2, $unit->getLife());

        // Проверяем, что способность больше не активна
        self::assertFalse($ability->isReady());

        // Убиваем юнита повторно
        $actions = $enemyUnit->getActions($command, $enemyCommand);
        foreach ($actions as $action) {
            self::assertTrue($action->canByUsed());
            $action->handle();
        }

        // Проверяем, что юнит мертв
        self::assertEquals(0, $unit->getLife());

        // Пытаемся активировать способность повторно
        $ability->update($unit, true);

        // Но она больше не активируется
        self::assertFalse($ability->isReady());
        self::assertFalse($ability->canByUsed($enemyCommand, $command));
    }

    /**
     * Тест на создание способности WillToLiveAbility через AbilityDataProvider
     *
     * @throws Exception
     */
    public function testWillToLiveAbilityDataProviderCreate(): void
    {
        $name = 'Will to live';
        $icon = '/images/icons/ability/429.png';

        $container = new Container();
        $unit = UnitFactory::createByTemplate(10, $container);
        $enemyUnit = UnitFactory::createByTemplate(2, $container);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = $this->createAbilityByDataProvider($unit, $name);

        self::assertEquals($name, $ability->getName());
        self::assertEquals($icon, $ability->getIcon());
        self::assertEquals($unit, $ability->getUnit());
        self::assertFalse($ability->isReady());
        self::assertTrue($ability->canByUsed($enemyCommand, $command));
        self::assertTrue($ability->isDisposable());
        self::assertFalse($ability->isUsage());

        // Активируем - созданный юнит изначально мертв
        $ability->update($unit, true);

        self::assertTrue($ability->isReady());

        self::assertEquals(
            $this->getWillToLiveActions($container, $unit, $enemyCommand, $command),
            $ability->getActions($enemyCommand, $command)
        );

        $ability->usage();
        self::assertTrue($ability->isUsage());
        self::assertFalse($ability->isReady());
    }

    /**
     * Тест на применение способности WillToLiveAbility через AbilityDataProvider
     *
     * @throws Exception
     */
    public function testWillToLiveAbilityDataProviderApply(): void
    {
        $unit = UnitFactory::createByTemplate(10);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = $this->createAbilityByDataProvider($unit, 'Will to live');

        // Изначально юнит мертв
        self::assertEquals(0, $unit->getLife());

        // Активируем способность
        // Вначале используем метод в обычном режиме - для 100% покрытия кода тестами
        $ability->update($unit);
        // Но чтобы активировать её точно - используем в тестовом режиме
        $ability->update($unit, true);

        // Применяем способность
        foreach ($ability->getActions($enemyCommand, $command) as $action) {
            self::assertTrue($action->canByUsed());
            $action->handle();
            self::assertEquals(self::MESSAGE_EN, $this->getChat()->addMessage($action));
            self::assertEquals(self::MESSAGE_RU, $this->getChatRu()->addMessage($action));
        }

        $ability->usage();

        // Проверяем, что здоровье восстановилось
        self::assertEquals($unit->getTotalLife() / 2, $unit->getLife());

        self::assertFalse($ability->isReady());
    }

    /**
     * Тест на проверку того, что способность не может использоваться повторно через AbilityDataProvider
     *
     * @throws Exception
     */
    public function testWillToLiveAbilityDataProviderNoRepeatUsage(): void
    {
        $unit = UnitFactory::createByTemplate(10);
        $enemyUnit = UnitFactory::createByTemplate(12);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = $this->createAbilityByDataProvider($unit, 'Will to live');

        // Изначально юнит мертв
        self::assertEquals(0, $unit->getLife());

        // Активируем способность
        $ability->update($unit, true);

        // Проверяем, что способность может быть использована
        self::assertTrue($ability->isReady());

        // Применяем способность
        foreach ($ability->getActions($enemyCommand, $command) as $action) {
            self::assertTrue($action->canByUsed());
            $action->handle();
            self::assertEquals(self::MESSAGE_EN, $this->getChat()->addMessage($action));
            self::assertEquals(self::MESSAGE_RU, $this->getChatRu()->addMessage($action));

            // Дополнительное проверяем, что по событию успешно создается анимация
            (new Scenario())->addAnimation($action, new Statistic());
        }

        $ability->usage();

        // Проверяем, что здоровье восстановилось
        self::assertEquals($unit->getTotalLife() / 2, $unit->getLife());

        // Проверяем, что способность больше не активна
        self::assertFalse($ability->isReady());

        // Убиваем юнита повторно
        $actions = $enemyUnit->getActions($command, $enemyCommand);
        foreach ($actions as $action) {
            self::assertTrue($action->canByUsed());
            $action->handle();
        }

        // Проверяем, что юнит мертв
        self::assertEquals(0, $unit->getLife());

        // Пытаемся активировать способность повторно
        $ability->update($unit, true);

        // Но она больше не активируется
        self::assertFalse($ability->isReady());
        self::assertFalse($ability->canByUsed($enemyCommand, $command));
    }

    // -----------------------------------------------------------------------------------------------------------------
    // -------------------------------   Аналогичные тесты через AbilityDataProvider   ---------------------------------
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * @param ContainerInterface $container
     * @param UnitInterface $unit
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $command
     * @return ActionCollection
     * @throws Exception
     */
    private function getWillToLiveActions(
        ContainerInterface $container,
        UnitInterface $unit,
        CommandInterface $enemyCommand,
        CommandInterface $command
    ): ActionCollection
    {
        $actionCollection = new ActionCollection();

        $actionCollection->add(new ResurrectionAction(
            $container,
            $unit,
            $enemyCommand,
            $command,
            ResurrectionAction::TARGET_SELF,
            50,
            'Will to live',
            'selfRaceResurrected',
            '/images/icons/ability/429.png'
        ));

        return $actionCollection;
    }

    /**
     * @param UnitInterface $unit
     * @return AbilityInterface
     * @throws Exception
     */
    private function createAbility(UnitInterface $unit): AbilityInterface
    {
        $name = 'Will to live';
        $icon = '/images/icons/ability/429.png';
        $messageMethod = 'selfRaceResurrected';

        return new Ability(
            $unit,
            true,
            $name,
            $icon,
            [
                [
                    'type'           => ActionInterface::RESURRECTION,
                    'type_target'    => ActionInterface::TARGET_SELF,
                    'power'          => 50,
                    'name'           => $name,
                    'icon'           => $icon,
                    'message_method' => $messageMethod,
                ],
            ],
            AbilityInterface::ACTIVATE_DEAD,
            [],
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
