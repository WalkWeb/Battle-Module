<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Ability\Effect;

use Battle\Action\ActionInterface;
use Battle\Command\CommandFactory;
use Battle\Container\Container;
use Battle\Response\Scenario\Scenario;
use Battle\Response\Statistic\Statistic;
use Battle\Unit\Ability\Ability;
use Battle\Unit\Ability\AbilityCollection;
use Battle\Unit\Ability\AbilityInterface;
use Battle\Unit\UnitInterface;
use Exception;
use Tests\AbstractUnitTest;
use Tests\Factory\UnitFactory;

class RageAbilityTest extends AbstractUnitTest
{
    private const MESSAGE_EN = '<span style="color: #ae882d">wounded_orc</span> use <img src="/images/icons/ability/285.png" alt="" /> <span class="ability">Rage</span>';
    private const MESSAGE_RU = '<span style="color: #ae882d">wounded_orc</span> использовал <img src="/images/icons/ability/285.png" alt="" /> <span class="ability">Ярость</span>';

    // -----------------------------------------------------------------------------------------------------------------
    // ------------------------------------------   Тесты через Ability   ----------------------------------------------
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Тест на применение способности RageAbility через универсальный объект Ability
     *
     * @throws Exception
     */
    public function testRageAbilityApply(): void
    {
        $unit = UnitFactory::createByTemplate(31);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = $this->createAbility($unit);

        // Активируем - созданный юнит изначально сильно ранен и имеет здоровье < 30%
        $collection = new AbilityCollection();
        $collection->add($ability);

        foreach ($collection as $item) {
            self::assertEquals($ability, $item);
        }

        $collection->update($unit);

        self::assertTrue($ability->isReady());

        // Изначальный урон = 35
        self::assertEquals(35, $unit->getOffense()->getDamage($enemyUnit->getDefense()));

        // Применяем способность
        foreach ($ability->getActions($enemyCommand, $command) as $action) {
            self::assertTrue($action->canByUsed());
            $action->handle();
            self::assertEquals(self::MESSAGE_EN, $this->getChat()->addMessage($action));
            self::assertEquals(self::MESSAGE_RU, $this->getChatRu()->addMessage($action));
        }

        // Проверяем, что урон вырос в 2 раза
        self::assertEquals(70, $unit->getOffense()->getDamage($enemyUnit->getDefense()));

        // Обновляем длительность эффектов. Длительность эффектов обновляется в getAfterActions()
        for ($i = 0; $i < 10; $i++) {
            foreach ($unit->getAfterActions() as $afterAction) {
                if ($afterAction->canByUsed()) {
                    $afterAction->handle();
                }
            }
        }

        // И проверяем, что урон вернулся к прежнему
        self::assertEquals(35, $unit->getOffense()->getDamage($enemyUnit->getDefense()));
        self::assertCount(0, $unit->getEffects());
    }

    /**
     * Тест на проверку перехода события из способного к применению, в невозможное к применение и обратно
     *
     * @throws Exception
     */
    public function testRageAbilityCanByUsed(): void
    {
        $unit = UnitFactory::createByTemplate(31);
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

        $ability->usage();

        // После появления эффекта на юните - способность уже не может быть применена
        self::assertFalse($ability->canByUsed($enemyCommand, $command));

        // Пропускаем ходы
        for ($i = 0; $i < 10; $i++) {
            $unit->newRound();
        }

        // Эффект исчез - но способность не может быть опять применена, потому что она одноразовая
        self::assertFalse($ability->canByUsed($enemyCommand, $command));
    }

    // -----------------------------------------------------------------------------------------------------------------
    // -------------------------------   Аналогичные тесты через AbilityDataProvider   ---------------------------------
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Тест на применение способности RageAbility через универсальный объект Ability
     *
     * @throws Exception
     */
    public function testRageAbilityDataProviderApply(): void
    {
        $unit = UnitFactory::createByTemplate(31);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = $this->createAbilityByDataProvider($unit, 'Rage');

        // Активируем - созданный юнит изначально сильно ранен и имеет здоровье < 30%
        $collection = new AbilityCollection();
        $collection->add($ability);

        foreach ($collection as $item) {
            self::assertEquals($ability, $item);
        }

        $collection->update($unit);

        self::assertTrue($ability->isReady());

        // Изначальный урон = 35
        self::assertEquals(35, $unit->getOffense()->getDamage($enemyUnit->getDefense()));

        // Применяем способность
        foreach ($ability->getActions($enemyCommand, $command) as $action) {
            self::assertTrue($action->canByUsed());
            $action->handle();
            self::assertEquals(self::MESSAGE_EN, $this->getChat()->addMessage($action));
            self::assertEquals(self::MESSAGE_RU, $this->getChatRu()->addMessage($action));

            // Дополнительное проверяем, что по событию успешно создается анимация
            (new Scenario())->addAnimation($action, new Statistic());
        }

        // Проверяем, что урон вырос в 2 раза
        self::assertEquals(70, $unit->getOffense()->getDamage($enemyUnit->getDefense()));

        // Обновляем длительность эффектов. Длительность эффектов обновляется в getAfterActions()
        for ($i = 0; $i < 10; $i++) {
            foreach ($unit->getAfterActions() as $afterAction) {
                if ($afterAction->canByUsed()) {
                    $afterAction->handle();
                }
            }
        }

        // И проверяем, что урон вернулся к прежнему
        self::assertEquals(35, $unit->getOffense()->getDamage($enemyUnit->getDefense()));
        self::assertCount(0, $unit->getEffects());
    }

    /**
     * Тест на проверку перехода события из способного к применению, в невозможное к применение и обратно
     *
     * @throws Exception
     */
    public function testRageAbilityDataProviderCanByUsed(): void
    {
        $unit = UnitFactory::createByTemplate(31);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = $this->createAbilityByDataProvider($unit, 'Rage');

        // Перед применением способности эффекта на юните еще нет - способность может быть применена
        self::assertTrue($ability->canByUsed($enemyCommand, $command));

        foreach ($ability->getActions($enemyCommand, $command) as $action) {
            self::assertTrue($action->canByUsed());
            $action->handle();
        }

        $ability->usage();

        // После появления эффекта на юните - способность уже не может быть применена
        self::assertFalse($ability->canByUsed($enemyCommand, $command));

        // Пропускаем ходы
        for ($i = 0; $i < 10; $i++) {
            $unit->newRound();
        }

        // Эффект исчез - но способность не может быть опять применена, потому что она одноразовая
        self::assertFalse($ability->canByUsed($enemyCommand, $command));
    }

    /**
     * @param UnitInterface $unit
     * @return AbilityInterface
     * @throws Exception
     */
    private function createAbility(UnitInterface $unit): AbilityInterface
    {
        $name = 'Rage';
        $icon = '/images/icons/ability/285.png';

        return new Ability(
            $unit,
            true,
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
                        'duration'              => 8,
                        'on_apply_actions'      => [
                            [
                                'type'           => ActionInterface::BUFF,
                                'type_target'    => ActionInterface::TARGET_SELF,
                                'name'           => $name,
                                'modify_method'  => 'multiplierPhysicalDamage',
                                'power'          => 200,
                                'icon'           => $icon,
                                'message_method' => ActionInterface::SKIP_MESSAGE_METHOD,
                            ],
                        ],
                        'on_next_round_actions' => [],
                        'on_disable_actions'    => [],
                    ],
                ],
            ],
            AbilityInterface::ACTIVATE_LOW_LIFE,
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
