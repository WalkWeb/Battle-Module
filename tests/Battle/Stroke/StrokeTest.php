<?php

declare(strict_types=1);

namespace Tests\Battle\Stroke;

use Battle\Action\ActionInterface;
use Battle\Action\DamageAction;
use Battle\Action\EffectAction;
use Battle\Action\HealAction;
use Battle\Command\CommandInterface;
use Battle\Stroke\StrokeException;
use Battle\Unit\Ability\AbilityFactory;
use Battle\Unit\Defense\Defense;
use Battle\Unit\Offense\Offense;
use Battle\Unit\UnitInterface;
use Exception;
use Battle\Container\Container;
use Battle\Command\CommandFactory;
use Battle\Stroke\Stroke;
use Tests\AbstractUnitTest;
use Tests\Battle\Factory\Mock\BrokenPriestUnit;
use Tests\Battle\Factory\UnitFactory;

class StrokeTest extends AbstractUnitTest
{
    private const MESSAGE = '<span style="color: #1e72e3">unit_1</span> hit for 20 damage against <span style="color: #1e72e3">unit_2</span>';

    /**
     * Тест на базовую обработку одного хода
     *
     * @throws Exception
     */
    public function testStrokeHandle(): void
    {
        $container = new Container(true);
        $unit = UnitFactory::createByTemplate(1, $container);
        $enemyUnit = UnitFactory::createByTemplate(2, $container);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $stroke = new Stroke(1, $unit, $command, $enemyCommand, $container);
        $stroke->handle();

        self::assertEquals($enemyUnit->getTotalLife() - $unit->getOffense()->getDamage($enemyUnit->getDefense()), $enemyUnit->getLife());

        self::assertTrue($unit->isAction());
        self::assertFalse($enemyUnit->isAction());

        $chatResultMessages = [
            self::MESSAGE,
        ];

        self::assertEquals($chatResultMessages, $container->getChat()->getMessages());
    }

    /**
     * Тест на остановку внутри Stroke, например, когда юнит хочет сделать два удара, но противник умирает после первого
     *
     * @throws Exception
     */
    public function testStrokeBreakAction(): void
    {
        $unit = UnitFactory::createByTemplate(13);
        $enemyUnit = UnitFactory::createByTemplate(18);

        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $stroke = new Stroke(1, $unit, $command, $enemyCommand, new Container());

        // Для теста достаточно того, что выполнение хода завершилось без ошибок
        $stroke->handle();

        // Но, на всякий случай проверяем, что противник умер
        self::assertEquals(0, $enemyUnit->getLife());
    }

    /**
     * Сложный тест на эмуляцию ситуации, когда Stroke не может выполнить полученный Action
     *
     * Сложный тем, что юнит проверяет событие на возможность использование. Значит, делаем мок юнита, который вернет
     * именно лечение, хотя и лечить некого
     *
     * @throws Exception
     */
    public function testStrokeCantByUsedActionException(): void
    {
        $enemyUnit = UnitFactory::createByTemplate(3);
        $container = $enemyUnit->getContainer();

        $brokenPriest = new BrokenPriestUnit(
            'id',
            'Broken Priest',
            1,
            'avatar',
            20,
            20,
            10,
            10,
            true,
            1,
            new Offense(
                1,
                10,
                5,
                1,
                100,
                50,
                5,
                200,
            0
            ),
            new Defense(
                0,
                10,
                10,
                0,
                0,
                0
            ),
            $container->getRaceFactory()->create($container->getRaceDataProvider()->get(1)),
            $container
        );

        $alliesCommand = CommandFactory::create([$brokenPriest]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $stroke = new Stroke(1, $brokenPriest, $alliesCommand, $enemyCommand, new Container());

        $this->expectException(StrokeException::class);
        $this->expectExceptionMessage(StrokeException::CANT_BE_USED_ACTION);
        $stroke->handle();
    }

    /**
     * Тест на применение эффект при обработке хода
     *
     * @throws Exception
     */
    public function testStrokeApplyEffect(): void
    {
        $unit = UnitFactory::createByTemplate(11);
        $command = CommandFactory::create([$unit]);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $enemyCommand = CommandFactory::create([$enemyUnit]);
        $container = new Container();

        $effectAction = $this->getHealEffectAction($unit, $command, $enemyCommand);

        self::assertTrue($effectAction->canByUsed());

        $effectAction->handle();

        self::assertCount(1, $unit->getEffects());
        self::assertEquals(1, $unit->getLife());

        $stroke = new Stroke(1, $unit, $command, $enemyCommand, $container);
        $stroke->handle();

        self::assertEquals(16, $unit->getLife());
    }

    /**
     * Тест на ситуацию, когда юнит, который должен ходить, умирает от эффекта, наложенного на него и по сути не ходит
     *
     * @throws Exception
     */
    public function testStrokeEffectDead(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);
        $container = new Container();

        $action = $this->getDamageEffectAction($unit, $command, $enemyCommand);

        if ($action->canByUsed()) {
            $unit->applyAction($action);
        }

        $stroke = new Stroke(1, $unit, $command, $enemyCommand, $container);
        $stroke->handle();

        $scenario = $container->getScenario()->getArray();

        // В сценарии должна быть только одна запись - о нанесении урона
        self::assertCount(1, $scenario);

        // Проверяем, что эта запись - именно об эффекте
        $expectedData = [
            'step'    => $container->getStatistic()->getRoundNumber(),
            'attack'  => $container->getStatistic()->getStrokeNumber(),
            'effects' => [
                [
                    'user_id'      => $unit->getId(),
                    'unit_effects' => [],
                    'targets'      => [
                        [
                            'type'              => 'change',
                            'user_id'           => $unit->getId(),
                            'ava'               => 'unit_ava_effect_damage',
                            'recdam'            => '-100',
                            'hp'                => 0,
                            'thp'               => 100,
                            'unit_hp_bar_width' => 0,
                            'avas'              => 'unit_ava_dead',
                            'hp_bar_class'      => 'unit_hp_bar',
                            'hp_bar_class2'     => 'unit_hp_bar2',
                            'unit_cons_bar2'    => 20,
                            'unit_rage_bar2'    => 14,
                            'unit_effects'      => [],
                        ],
                    ],
                ],
            ],
        ];

        self::assertEquals($expectedData, $container->getScenario()->getArray()[0]);
    }

    /**
     * Тест ситуации, когда юнит имеет два эффект, и первый эффект его убивает - второй эффект применяться не должен
     *
     * @throws Exception
     */
    public function testStrokeTwoEffectAndDeadAfterFirst(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $otherUnit = UnitFactory::createByTemplate(11);
        $enemyUnit = UnitFactory::createByTemplate(3);
        $command = CommandFactory::create([$unit, $otherUnit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);
        $container = new Container();

        $effectDamage = $this->getDamageEffectAction($unit, $command, $enemyCommand);

        if ($effectDamage->canByUsed()) {
            $unit->applyAction($effectDamage);
        }

        $effectHeal = $this->getHealEffectAction($unit, $command, $enemyCommand);

        if ($effectHeal->canByUsed()) {
            $unit->applyAction($effectHeal);
        }

        self::assertCount(2, $unit->getEffects());

        $stroke = new Stroke(1, $unit, $command, $enemyCommand, $container);
        $stroke->handle();

        $scenario = $container->getScenario()->getArray();

        // На всякий случай проверяем, что эффект полечил не того, на кого наложен, а другого юнита
        self::assertEquals(1, $otherUnit->getLife());

        // Проверяем количество записей в сценарии - должна быть только одна запись о применении эффекта
        self::assertCount(1, $scenario);

        // Проверяем, что эта запись - именно об эффекте
        $expectedData = [
            'step'    => $container->getStatistic()->getRoundNumber(),
            'attack'  => $container->getStatistic()->getStrokeNumber(),
            'effects' => [
                [
                    'user_id'      => $unit->getId(),
                    'unit_effects' => [],
                    'targets'      => [
                        [
                            'type'              => 'change',
                            'user_id'           => $unit->getId(),
                            'ava'               => 'unit_ava_effect_damage',
                            'recdam'            => '-100',
                            'hp'                => 0,
                            'thp'               => 100,
                            'unit_hp_bar_width' => 0,
                            'avas'              => 'unit_ava_dead',
                            'hp_bar_class'      => 'unit_hp_bar',
                            'hp_bar_class2'     => 'unit_hp_bar2',
                            'unit_cons_bar2'    => 30,
                            'unit_rage_bar2'    => 21,
                            'unit_effects'      => [],
                        ],
                    ],
                ],
            ],
        ];

        self::assertEquals($expectedData, $container->getScenario()->getArray()[0]);
    }

    /**
     * Тест на применение способностей после смерти юнита, на примере WillToLiveAbility
     *
     * @throws Exception
     */
    public function testStrokeDeadAbilitiesUse(): void
    {
        $container = new Container(true);
        // Юнит с ударом 3000
        $unit = UnitFactory::createByTemplate(12, $container);
        // Юнит с хп 250
        $enemyUnit = UnitFactory::createByTemplate(2, $container);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $stroke = new Stroke(1, $unit, $command, $enemyCommand, $container);

        $stroke->handle();

        // После удара юнит умирает, и затем воскрешается, восстанавливая 50% здоровья
        self::assertEquals($enemyUnit->getTotalLife() / 2, $enemyUnit->getLife());

        // Проверяем, что сгенерировано две анимации - удара и оживления
        self::assertCount(2, $container->getScenario()->getArray());
    }

    /**
     * Тест на выполнение метода handleAfterActionUnit() в Stroke
     *
     * @throws Exception
     */
    public function testStrokeHandleAfterActionUnit(): void
    {
        $abilityFactory = new AbilityFactory();
        $container = new Container();
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $oldAttackSpeed = $unit->getOffense()->getAttackSpeed();

        // Накладываем на юнита баф
        $ability = $abilityFactory->create($unit, $container->getAbilityDataProvider()->get('Battle Fury', 1));

        foreach ($ability->getActions($enemyCommand, $command) as $action) {
            self::assertTrue($action->canByUsed());
            $action->handle();
        }

        // Прокручиваем длительность эффекта до значения 1, чтобы во время хода эффект закончился
        for ($i = 0; $i < 14; $i++) {
            $unit->getAfterActions();
        }

        // Проверяем, что эффект еще есть
        self::assertCount(1, $unit->getEffects());

        $stroke = new Stroke(1, $unit, $command, $enemyCommand, $container);

        $stroke->handle();

        // А после выполнения хода он исчез
        self::assertCount(0, $unit->getEffects());

        // А также проверяем то, что скорость вернулась к прежнему значению
        self::assertEquals($oldAttackSpeed, $unit->getOffense()->getAttackSpeed());
    }

    /**
     * @param UnitInterface $unit
     * @param CommandInterface $command
     * @param CommandInterface $enemyCommand
     * @return ActionInterface
     * @throws Exception
     */
    private function getHealEffectAction(
        UnitInterface $unit,
        CommandInterface $command,
        CommandInterface $enemyCommand
    ): ActionInterface
    {
        $data = [
            'type'           => ActionInterface::EFFECT,
            'action_unit'    => $unit,
            'enemy_command'  => $enemyCommand,
            'allies_command' => $command,
            'type_target'    => ActionInterface::TARGET_SELF,
            'name'           => 'Effect Heal',
            'effect'         => [
                'name'                  => 'Effect Heal',
                'icon'                  => 'icon.png',
                'duration'              => 8,
                'on_apply_actions'      => [],
                'on_next_round_actions' => [
                    [
                        'type'             => ActionInterface::HEAL,
                        'action_unit'      => $unit,
                        'enemy_command'    => $enemyCommand,
                        'allies_command'   => $command,
                        'type_target'      => ActionInterface::TARGET_SELF,
                        'name'             => 'Effect Heal',
                        'power'            => 15,
                        'animation_method' => HealAction::EFFECT_ANIMATION_METHOD,
                    ],
                ],
                'on_disable_actions'    => [],
            ],
        ];

        return $this->getContainer()->getActionFactory()->create($data);
    }

    /**
     * @param UnitInterface $unit
     * @param CommandInterface $command
     * @param CommandInterface $enemyCommand
     * @return EffectAction
     * @throws Exception
     */
    private function getDamageEffectAction(
        UnitInterface $unit,
        CommandInterface $command,
        CommandInterface $enemyCommand
    ): ActionInterface
    {
        $data = [
            'type'           => ActionInterface::EFFECT,
            'action_unit'    => $unit,
            'enemy_command'  => $enemyCommand,
            'allies_command' => $command,
            'type_target'    => ActionInterface::TARGET_SELF,
            'name'           => 'Effect Damage',
            'effect'         => [
                'name'                  => 'Effect Damage',
                'icon'                  => 'icon.png',
                'duration'              => 8,
                'on_apply_actions'      => [],
                'on_next_round_actions' => [
                    [
                        'type'             => ActionInterface::DAMAGE,
                        'action_unit'      => $unit,
                        'enemy_command'    => $enemyCommand,
                        'allies_command'   => $command,
                        'type_target'      => ActionInterface::TARGET_SELF,
                        'name'             => 'Effect Damage',
                        'offense'          => [
                            'type_damage'         => 1,
                            'physical_damage'     => 1000,
                            'attack_speed'        => 1,
                            'accuracy'            => 1000,
                            'magic_accuracy'      => 1000,
                            'block_ignore'        => 0,
                            'critical_chance'     => 5,
                            'critical_multiplier' => 200,
                            'vampire'             => 0,
                        ],
                        'can_be_avoided'   => false,
                        'animation_method' => DamageAction::EFFECT_ANIMATION_METHOD,
                        'message_method'   => DamageAction::DEFAULT_MESSAGE_METHOD,
                    ],
                ],
                'on_disable_actions'    => [],
            ],
        ];

        return $this->getContainer()->getActionFactory()->create($data);
    }
}
