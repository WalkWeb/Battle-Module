<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Ability\Effect;

use Battle\Action\ActionCollection;
use Battle\Action\ActionFactory;
use Battle\Action\ActionInterface;
use Battle\Action\DamageAction;
use Battle\Command\CommandFactory;
use Battle\Command\CommandInterface;
use Battle\Unit\Ability\AbilityCollection;
use Battle\Unit\Ability\Effect\PoisonAbility;
use Battle\Unit\UnitInterface;
use Exception;
use Tests\AbstractUnitTest;
use Tests\Battle\Factory\UnitFactory;

class PoisonAbilityTest extends AbstractUnitTest
{
    // Сообщения применения на другого юнита
    private const MESSAGE_APPLY_TO_EN = '<span style="color: #1e72e3">unit_1</span> use <img src="/images/icons/ability/202.png" alt="" /> Poison on <span style="color: #1e72e3">unit_2</span>';
    private const MESSAGE_APPLY_TO_RU = '<span style="color: #1e72e3">unit_1</span> использовал <img src="/images/icons/ability/202.png" alt="" /> Отравление на <span style="color: #1e72e3">unit_2</span>';

    // Сообщения об уроне от эффекта
    private const MESSAGE_DAMAGE_EN = '<span style="color: #1e72e3">unit_2</span> received damage on 8 life from effect <img src="/images/icons/ability/202.png" alt="" /> Poison';
    private const MESSAGE_DAMAGE_RU = '<span style="color: #1e72e3">unit_2</span> получил урон на 8 здоровья от эффекта <img src="/images/icons/ability/202.png" alt="" /> Отравление';

    // TODO Сообщения применения эффекта на себя

    /**
     * Тест на создание способности PoisonAbility
     *
     * @throws Exception
     */
    public function testPoisonAbilityCreate(): void
    {
        $name = 'Poison';
        $icon = '/images/icons/ability/202.png';
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = new PoisonAbility($unit);

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
     * Тест на получение Actions из PoisonAbility
     *
     * @throws Exception
     */
    public function testPoisonAbilityGetActions(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = new PoisonAbility($unit);

        self::assertEquals(
            $this->createActions($unit, $command, $enemyCommand),
            $ability->getAction($enemyCommand, $command)
        );
    }

    /**
     * Тест на получение false в $ability->canByUsed(), когда все противники уже имеют такой эффект
     *
     * @throws Exception
     */
    public function testPoisonAbilityCantByUsed(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = new PoisonAbility($unit);

        // Up concentration
        for ($i = 0; $i < 10; $i++) {
            $unit->newRound();
        }

        self::assertTrue($ability->canByUsed($enemyCommand, $command));

        // Применяем эффект
        foreach ($ability->getAction($enemyCommand, $command) as $action) {
            self::assertTrue($action->canByUsed());
            self::assertEquals(self::MESSAGE_APPLY_TO_EN, $action->handle());
        }

        // Теперь эффект у противника есть, и больше способность примениться не может
        // Так как все противники (один противник) уже имеют такой эффект
        self::assertFalse($ability->canByUsed($enemyCommand, $command));

        $effects = $enemyUnit->getEffects();

        self::assertCount(1, $effects);

        foreach ($effects as $effect) {
            $actions = $effect->getOnNextRoundActions();
            self::assertCount(1, $actions);
            foreach ($actions as $action) {
                self::assertTrue($action->canByUsed());
                self::assertEquals(self::MESSAGE_DAMAGE_EN, $action->handle());
            }
        }
    }

    /**
     * Тест на формирование сообщения на русском
     *
     * @throws Exception
     */
    public function testPoisonAbilityRuMessage(): void
    {
        $container = $this->getContainerWithRuLanguage();

        $unit = UnitFactory::createByTemplate(1, $container);
        $enemyUnit = UnitFactory::createByTemplate(2, $container);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = new PoisonAbility($unit);

        // Up concentration
        for ($i = 0; $i < 10; $i++) {
            $unit->newRound();
        }

        // Применяем эффект
        foreach ($ability->getAction($enemyCommand, $command) as $action) {
            self::assertTrue($action->canByUsed());
            self::assertEquals(self::MESSAGE_APPLY_TO_RU, $action->handle());
        }

        $effects = $enemyUnit->getEffects();

        self::assertCount(1, $effects);

        foreach ($effects as $effect) {
            $actions = $effect->getOnNextRoundActions();
            self::assertCount(1, $actions);
            foreach ($actions as $action) {
                self::assertTrue($action->canByUsed());
                self::assertEquals(self::MESSAGE_DAMAGE_RU, $action->handle());
            }
        }
    }

    /**
     * @param UnitInterface $unit
     * @param CommandInterface $command
     * @param CommandInterface $enemyCommand
     * @return ActionCollection
     * @throws Exception
     */
    private function createActions(
        UnitInterface $unit,
        CommandInterface $command,
        CommandInterface $enemyCommand
    ): ActionCollection
    {
        $actionFactory = new ActionFactory();

        $data = [
            'type'           => ActionInterface::EFFECT,
            'action_unit'    => $unit,
            'enemy_command'  => $enemyCommand,
            'allies_command' => $command,
            'type_target'    => ActionInterface::TARGET_EFFECT_ENEMY,
            'name'           => 'Poison',
            'icon'           => '/images/icons/ability/202.png',
            'message_method'  => 'applyEffectImproved',
            'effect'         => [
                'name'                  => 'Poison',
                'icon'                  => '/images/icons/ability/202.png',
                'duration'              => 5,
                'on_apply_actions'      => [],
                'on_next_round_actions' => [
                    [
                        'type'             => ActionInterface::DAMAGE,
                        'action_unit'      => $unit,
                        'enemy_command'    => $enemyCommand,
                        'allies_command'   => $command,
                        'type_target'      => ActionInterface::TARGET_SELF,
                        'name'             => 'Poison',
                        'power'            => 8,
                        'animation_method' => DamageAction::EFFECT_ANIMATION_METHOD,
                        'message_method'   => DamageAction::EFFECT_MESSAGE_METHOD,
                        'icon'             => '/images/icons/ability/202.png',
                    ],
                ],
                'on_disable_actions'    => [],
            ],
        ];

        $actions = new ActionCollection();
        $actions->add($actionFactory->create($data));

        return $actions;
    }
}
