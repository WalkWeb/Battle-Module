<?php

declare(strict_types=1);

namespace Tests\Battle\Stroke;

use Battle\Action\ActionInterface;
use Battle\Action\DamageAction;
use Battle\Action\EffectAction;
use Battle\Action\HealAction;
use Battle\Command\CommandInterface;
use Battle\Unit\UnitInterface;
use Battle\Weapon\Type\WeaponTypeInterface;
use Exception;
use Battle\Command\CommandFactory;
use Battle\Stroke\Stroke;
use Tests\AbstractUnitTest;
use Tests\Factory\UnitFactory;

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
        $unit = UnitFactory::createByTemplate(1, $this->container);
        $enemyUnit = UnitFactory::createByTemplate(2, $this->container);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $stroke = new Stroke(1, $unit, $command, $enemyCommand, $this->container);
        $stroke->handle();

        self::assertEquals($enemyUnit->getTotalLife() - $unit->getOffense()->getDamage($enemyUnit->getDefense()), $enemyUnit->getLife());

        self::assertTrue($unit->isAction());
        self::assertFalse($enemyUnit->isAction());

        $chatResultMessages = [
            self::MESSAGE,
        ];

        self::assertEquals($chatResultMessages, $this->container->getChat()->getMessages());
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

        $stroke = new Stroke(1, $unit, $command, $enemyCommand, $this->container);

        // Для теста достаточно того, что выполнение хода завершилось без ошибок
        $stroke->handle();

        // Но, на всякий случай проверяем, что противник умер
        self::assertEquals(0, $enemyUnit->getLife());
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

        $effectAction = $this->getHealEffectAction($unit, $command, $enemyCommand);

        self::assertTrue($effectAction->canByUsed());

        $effectAction->handle();

        self::assertCount(1, $unit->getEffects());
        self::assertEquals(1, $unit->getLife());

        $stroke = new Stroke(1, $unit, $command, $enemyCommand, $this->container);
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

        $action = $this->getDamageEffectAction($unit, $command, $enemyCommand);

        if ($action->canByUsed()) {
            $unit->applyAction($action);
        }

        $stroke = new Stroke(1, $unit, $command, $enemyCommand, $this->container);
        $stroke->handle();

        $scenario = $this->container->getScenario()->getArray();

        // В сценарии должна быть только одна запись - о нанесении урона
        self::assertCount(1, $scenario);

        // Проверяем, что эта запись - именно об эффекте
        $expectedData = [
            'step'    => $this->container->getStatistic()->getRoundNumber(),
            'attack'  => $this->container->getStatistic()->getStrokeNumber(),
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
                            'unit_cons_bar2'    => 10,
                            'unit_rage_bar2'    => 7,
                            'unit_effects'      => [],
                        ],
                    ],
                ],
            ],
        ];

        self::assertEquals($expectedData, $this->container->getScenario()->getArray()[0]);
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

        $effectDamage = $this->getDamageEffectAction($unit, $command, $enemyCommand);

        if ($effectDamage->canByUsed()) {
            $unit->applyAction($effectDamage);
        }

        $effectHeal = $this->getHealEffectAction($unit, $command, $enemyCommand);

        if ($effectHeal->canByUsed()) {
            $unit->applyAction($effectHeal);
        }

        self::assertCount(2, $unit->getEffects());

        $stroke = new Stroke(1, $unit, $command, $enemyCommand, $this->container);
        $stroke->handle();

        $scenario = $this->container->getScenario()->getArray();

        // На всякий случай проверяем, что эффект полечил не того, на кого наложен, а другого юнита
        self::assertEquals(1, $otherUnit->getLife());

        // Проверяем количество записей в сценарии - должна быть только одна запись о применении эффекта
        self::assertCount(1, $scenario);

        // Проверяем, что эта запись - именно об эффекте
        $expectedData = [
            'step'    => $this->container->getStatistic()->getRoundNumber(),
            'attack'  => $this->container->getStatistic()->getStrokeNumber(),
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
                            'unit_cons_bar2'    => 10,
                            'unit_rage_bar2'    => 7,
                            'unit_effects'      => [],
                        ],
                    ],
                ],
            ],
        ];

        self::assertEquals($expectedData, $this->container->getScenario()->getArray()[0]);
    }

    /**
     * Тест на применение способностей после смерти юнита, на примере WillToLiveAbility
     *
     * @throws Exception
     */
    public function testStrokeDeadAbilitiesUse(): void
    {
        // Юнит с ударом 3000
        $unit = UnitFactory::createByTemplate(12, $this->container);
        // Юнит с хп 250
        $enemyUnit = UnitFactory::createByTemplate(2, $this->container);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $stroke = new Stroke(1, $unit, $command, $enemyCommand, $this->container);

        $stroke->handle();

        // После удара юнит умирает, и затем воскрешается, восстанавливая 50% здоровья
        self::assertEquals($enemyUnit->getTotalLife() / 2, $enemyUnit->getLife());

        // Проверяем, что сгенерировано две анимации - удара и оживления
        self::assertCount(2, $this->container->getScenario()->getArray());
    }

    /**
     * Тест на выполнение метода handleAfterActionUnit() в Stroke
     *
     * @throws Exception
     */
    public function testStrokeHandleAfterActionUnit(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $oldAttackSpeed = $unit->getOffense()->getAttackSpeed();

        // Накладываем на юнита баф
        $ability = $this->getAbility($unit, 'Battle Fury', 1);

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

        $stroke = new Stroke(1, $unit, $command, $enemyCommand, $this->container);

        $stroke->handle();

        // А после выполнения хода он исчез
        self::assertCount(0, $unit->getEffects());

        // А также проверяем то, что скорость вернулась к прежнему значению
        self::assertEquals($oldAttackSpeed, $unit->getOffense()->getAttackSpeed());
    }

    /**
     * Тест на реалистичную ситуацию, когда критическая атака с булавы оглушает юнита
     *
     * @throws Exception
     */
    public function testStrokeStunEffectFromWeapon(): void
    {
        $unit = UnitFactory::createByTemplate(47);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $stroke = new Stroke(1, $unit, $command, $enemyCommand, $this->container);

        $stroke->handle();

        // Проверяем, что появился эффект на юните
        self::assertCount(1, $enemyUnit->getEffects());

        // Проверяем, что это оглушение от оружия
        foreach ($unit->getEffects() as $effect) {
            self::assertEquals('Stun Weapon Action', $effect->getName());
        }
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
                        'message_method'   => HealAction::DEFAULT_MESSAGE_METHOD,
                    ],
                ],
                'on_disable_actions'    => [],
            ],
        ];

        return $this->container->getActionFactory()->create($data);
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
                            'damage_type'         => 1,
                            'weapon_type'         => WeaponTypeInterface::SWORD,
                            'physical_damage'     => 1000,
                            'fire_damage'         => 0,
                            'water_damage'        => 0,
                            'air_damage'          => 0,
                            'earth_damage'        => 0,
                            'life_damage'         => 0,
                            'death_damage'        => 0,
                            'attack_speed'        => 1,
                            'cast_speed'          => 0,
                            'accuracy'            => 1000,
                            'magic_accuracy'      => 1000,
                            'block_ignoring'      => 0,
                            'critical_chance'     => 5,
                            'critical_multiplier' => 200,
                            'damage_multiplier'   => 100,
                            'vampirism'           => 0,
                            'magic_vampirism'     => 0,
                        ],
                        'can_be_avoided'   => false,
                        'animation_method' => DamageAction::EFFECT_ANIMATION_METHOD,
                        'message_method'   => DamageAction::DEFAULT_MESSAGE_METHOD,
                    ],
                ],
                'on_disable_actions'    => [],
            ],
        ];

        return $this->container->getActionFactory()->create($data);
    }
}
