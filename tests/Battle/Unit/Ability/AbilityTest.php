<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Ability;

use Battle\Action\ActionInterface;
use Battle\Action\BuffAction;
use Battle\Action\DamageAction;
use Battle\Command\CommandFactory;
use Battle\Unit\Ability\Ability;
use Battle\Unit\Ability\AbilityCollection;
use Battle\Unit\Ability\AbilityException;
use Battle\Unit\Ability\AbilityInterface;
use Battle\Unit\Offense\MultipleOffense\MultipleOffense;
use Battle\Weapon\Type\WeaponTypeInterface;
use Exception;
use Tests\AbstractUnitTest;
use Tests\Factory\UnitFactory;

/**
 * Способностей много, и создавать тесты на все варианты в одном классе неразумно. По этому тесты под способности
 * пишутся в тестах с аналогичным типом/названием.
 *
 * В данном классе лишь небольшое количество тестов на самые базовые проверки
 *
 * @package Tests\Battle\Unit\Ability
 */
class AbilityTest extends AbstractUnitTest
{
    /**
     * Тест на проверку базовых параметров способностей: name, icon, unit, disposable
     *
     * @throws Exception
     */
    public function testAbilityCreate(): void
    {
        $name = 'Heavy Strike';
        $icon = '/images/icons/ability/335.png';
        $disposable = false;
        $chanceActivate = 50;
        $allowedWeaponType = [
            WeaponTypeInterface::SWORD,
            WeaponTypeInterface::AXE,
            WeaponTypeInterface::MACE,
        ];

        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = new Ability(
            $unit,
            $disposable,
            $name,
            $icon,
            [
                [
                    'type'             => ActionInterface::DAMAGE,
                    'action_unit'      => $unit,
                    'enemy_command'    => $enemyCommand,
                    'allies_command'   => $command,
                    'type_target'      => ActionInterface::TARGET_RANDOM_ENEMY,
                    'offense'          => [
                        'damage_type'     => 1,
                        'physical_damage' => 36,
                        'attack_speed'    => 1,
                        'accuracy'        => 252,
                        'magic_accuracy'  => 413,
                        'block_ignoring'  => 0,
                    ],
                    'can_be_avoided'   => true,
                    'name'             => $name,
                    'animation_method' => 'damage',
                    'message_method'   => 'damageAbility',
                    'icon'             => $icon,
                ],
            ],
            $typeActivate = AbilityInterface::ACTIVATE_RAGE,
            $allowedWeaponType,
            $chanceActivate
        );

        self::assertEquals($unit, $ability->getUnit());
        self::assertEquals($name, $ability->getName());
        self::assertEquals($icon, $ability->getIcon());
        self::assertEquals($disposable, $ability->isDisposable());
        self::assertEquals($typeActivate, $ability->getTypeActivate());
        self::assertEquals($allowedWeaponType, $ability->getAllowedWeaponTypes());

        // Проверка значений по-умолчанию:
        self::assertFalse($ability->isReady());
        self::assertFalse($ability->isUsage());
        self::assertEquals($chanceActivate, $ability->getChanceActivate());
    }

    /**
     * Тест на ситуацию, когда передан некорректный массив данных по способностей
     *
     * @throws Exception
     */
    public function testAbilityInvalidActionsData(): void
    {
        $unit = UnitFactory::createByTemplate(1);

        $this->expectException(AbilityException::class);
        $this->expectExceptionMessage(AbilityException::INVALID_ACTION_DATA);

        new Ability(
            $unit,
            false,
            'Heavy Strike',
            '/images/icons/ability/335.png',
            [
                'invalid_data',
            ],
            AbilityInterface::ACTIVATE_CONCENTRATION,
            [],
            0
        );
    }

    /**
     * Тест на неизвестный тип активации способности
     *
     * @throws Exception
     */
    public function testAbilityUnknownTypeActivate(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $typeActivate = 10;

        $this->expectException(AbilityException::class);
        $this->expectExceptionMessage(AbilityException::UNKNOWN_ACTIVATE_TYPE . ': ' . $typeActivate);

        new Ability(
            $unit,
            false,
            'Heavy Strike',
            '/images/icons/ability/335.png',
            [
                [],
            ],
            $typeActivate,
            [],
            0
        );
    }

    /**
     * Тест на ситуацию, когда передан невалидный массив параметров для создания эффекта - нет on_apply_actions,
     * on_next_round_actions или on_disable_actions
     *
     * @dataProvider invalidEffectDataProvider
     * @param array $effectData
     * @throws Exception
     */
    public function testAbilityInvalidEffectActionData(array $effectData): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = new Ability(
            $unit,
            false,
            'Heavy Strike',
            '/images/icons/ability/335.png',
            [
                [
                    'type'           => ActionInterface::EFFECT,
                    'type_target'    => ActionInterface::TARGET_SELF,
                    'name'           => 'Battle Fury',
                    'icon'           => '/images/icons/ability/102.png',
                    'message_method' => 'applyEffect',
                    'effect'         => $effectData,
                ],
            ],
            AbilityInterface::ACTIVATE_CONCENTRATION,
            [],
            0
        );

        $this->expectException(AbilityException::class);
        $this->expectExceptionMessage(AbilityException::INVALID_EFFECT_DATA);

        $ability->getActions($enemyCommand, $command);
    }

    /**
     * У этого теста одна цель - покрыть тестами строчку:
     * "$this->addStageParameters($onDisableActionData, $enemyCommand, $alliesCommand);"
     *
     * Заодно проверяется создание Action для завершения эффекта
     *
     * @throws Exception
     */
    public function testAbilitySetStageParameters(): void
    {
        $name = 'Battle Fury';
        $icon = '/images/icons/ability/102.png';

        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = new Ability(
            $unit,
            false,
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
                        'duration'              => 15,
                        'on_apply_actions'      => [],
                        'on_next_round_actions' => [],
                        'on_disable_actions'    => [
                            [
                                'type'           => ActionInterface::BUFF,
                                'type_target'    => ActionInterface::TARGET_SELF,
                                'name'           => $name,
                                'modify_method'  => BuffAction::ATTACK_SPEED,
                                'power'          => $power = 140,
                                'message_method' => ActionInterface::SKIP_MESSAGE_METHOD,
                            ],
                        ],
                    ],
                ],
            ],
            AbilityInterface::ACTIVATE_RAGE,
            [],
            0
        );

        foreach ($ability->getActions($enemyCommand, $command) as $action) {
            self::assertTrue($action->canByUsed());
            $action->handle();
        }

        self::assertCount(1, $unit->getEffects());

        foreach ($unit->getEffects() as $effect) {
            self::assertEquals($name, $effect->getName());
            self::assertEquals($icon, $effect->getIcon());

            foreach ($effect->getOnDisableActions() as $onDisableAction) {
                self::assertEquals(BuffAction::ATTACK_SPEED, $onDisableAction->getModifyMethod());
                self::assertEquals($power, $onDisableAction->getPower());
            }
        }
    }

    /**
     * Тест на ситуацию, когда тип оружия юнита не подходит для активации способности, и она не активируется несмотря
     * на 100% заполненную концентрацию
     *
     * @throws Exception
     */
    public function testAbilityNotAllowedWeaponType(): void
    {
        $name = 'Heavy Strike';
        $unit = UnitFactory::createByTemplate(40); // unit with staff weapon
        $ability = $this->createAbilityByDataProvider($unit, $name);
        $collection = new AbilityCollection();
        $collection->add($ability);

        // Up unit concentration
        for ($i = 0; $i < 10; $i++) {
            $unit->newRound();
        }

        $collection->update($unit);

        self::assertFalse($ability->isReady());
    }

    /**
     * Тест на активацию способности с типом активации = ACTIVATE_CUNNING
     *
     * @throws Exception
     */
    public function testAbilityActivateCunningSuccess(): void
    {
        // Юнит со 100+ хитрости
        $unit = UnitFactory::createByTemplate(53);

        // Создаем способность с типом активации = ACTIVATE_CUNNING
        $ability = new Ability(
            $unit,
            false,
            'Heavy Strike',
            '/images/icons/ability/335.png',
            [
                [
                    'type'           => ActionInterface::EFFECT,
                    'type_target'    => ActionInterface::TARGET_SELF,
                    'name'           => 'Battle Fury',
                    'icon'           => '/images/icons/ability/102.png',
                    'message_method' => 'applyEffect',
                    'effect'         => [
                        'name'                  => 'Battle Fury',
                        'icon'                  => '/images/icons/ability/102.png',
                        'duration'              => 15,
                        'on_apply_actions'      => [],
                        'on_next_round_actions' => [],
                        'on_disable_actions'    => [],
                    ],
                ],
            ],
            AbilityInterface::ACTIVATE_CUNNING,
            [],
            0
        );

        // Вначале способность неактивна
        self::assertFalse($ability->isReady());

        $abilities = new AbilityCollection();
        $abilities->add($ability);

        $abilities->newRound($unit);

        // Способность активировалась
        self::assertTrue($ability->isReady());
    }

    /**
     * Тест аналогичен testAbilityActivateCunningSuccess(), но юнит имеет не тот тип оружия - соответственно способность
     * не активируется
     *
     * @throws Exception
     */
    public function testAbilityActivateCunningIncorrectWeaponType(): void
    {
        // Юнит со 100+ хитрости
        $unit = UnitFactory::createByTemplate(53);

        // Создаем способность с типом активации = ACTIVATE_CUNNING
        $ability = new Ability(
            $unit,
            false,
            'Heavy Strike',
            '/images/icons/ability/335.png',
            [
                [
                    'type'           => ActionInterface::EFFECT,
                    'type_target'    => ActionInterface::TARGET_SELF,
                    'name'           => 'Battle Fury',
                    'icon'           => '/images/icons/ability/102.png',
                    'message_method' => 'applyEffect',
                    'effect'         => [
                        'name'                  => 'Battle Fury',
                        'icon'                  => '/images/icons/ability/102.png',
                        'duration'              => 15,
                        'on_apply_actions'      => [],
                        'on_next_round_actions' => [],
                        'on_disable_actions'    => [],
                    ],
                ],
            ],
            AbilityInterface::ACTIVATE_CUNNING,
            [
                WeaponTypeInterface::HEAVY_TWO_HAND_AXE,
            ],
            0
        );

        // Вначале способность неактивна
        self::assertFalse($ability->isReady());

        $abilities = new AbilityCollection();
        $abilities->add($ability);

        $abilities->newRound($unit);

        // Способность все равно не активировалась - юнит имеет неподходящий тип оружия
        self::assertFalse($ability->isReady());
    }

    /**
     * Тест аналогичен testAbilityActivateCunningSuccess(), но на этот раз проверяется то, что если способность
     * одноразовая - второй раз она не активируется
     *
     * @throws Exception
     */
    public function testAbilityActivateCunningDisposable(): void
    {
        // Юнит со 100+ хитрости
        $unit = UnitFactory::createByTemplate(53);

        // Создаем способность с типом активации = ACTIVATE_CUNNING
        $ability = new Ability(
            $unit,
            true,
            'Heavy Strike',
            '/images/icons/ability/335.png',
            [
                [
                    'type'           => ActionInterface::EFFECT,
                    'type_target'    => ActionInterface::TARGET_SELF,
                    'name'           => 'Battle Fury',
                    'icon'           => '/images/icons/ability/102.png',
                    'message_method' => 'applyEffect',
                    'effect'         => [
                        'name'                  => 'Battle Fury',
                        'icon'                  => '/images/icons/ability/102.png',
                        'duration'              => 15,
                        'on_apply_actions'      => [],
                        'on_next_round_actions' => [],
                        'on_disable_actions'    => [],
                    ],
                ],
            ],
            AbilityInterface::ACTIVATE_CUNNING,
            [],
            0
        );

        // Вначале способность неактивна
        self::assertFalse($ability->isReady());

        $abilities = new AbilityCollection();
        $abilities->add($ability);

        $abilities->newRound($unit);

        // Способность активировалась
        self::assertTrue($ability->isReady());

        // Отмечаем её использованной
        $ability->usage();

        // Способность стала неактивной
        self::assertFalse($ability->isReady());

        // Еще раз начинаем новый раунд
        $abilities->newRound($unit);

        // Но способность больше не активируется
        self::assertFalse($ability->isReady());
    }

    /**
     * Тест на конвертацию урона в способности
     *
     * @throws Exception
     */
    public function testAbilityConvertDamage(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = new Ability(
            $unit,
            false,
            'Test Ability',
            'icon.png',
            [
                [
                    'type'             => ActionInterface::DAMAGE,
                    'type_target'      => ActionInterface::TARGET_RANDOM_ENEMY,
                    'multiple_offense'          => [
                        'damage'              => 1.0,
                        'speed'               => 1.0,
                        'accuracy'            => 1.0,
                        'critical_chance'     => 1.0,
                        'critical_multiplier' => 1.0,
                        'damage_convert'      => MultipleOffense::CONVERT_FIRE,
                    ],
                    'can_be_avoided'   => true,
                    'name'             => 'Test Ability',
                    'animation_method' => 'damage',
                    'message_method'   => 'damageAbility',
                    'icon'             => 'icon.png',
                ],
            ],
            AbilityInterface::ACTIVATE_CONCENTRATION,
            [],
            0
        );

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

        // Применяем способность
        $actions = $ability->getActions($enemyCommand, $command);

        foreach ($actions as $action) {
            self::assertInstanceOf(DamageAction::class, $action);
            self::assertTrue($action->canByUsed());
            $action->handle();
        }

        // Обычный урон должен нанести 10 урона за счет резиста, но так как урон конвертируется в урон огнем - будет
        // нанесено 20 урона, т.к. сопротивление к огню 0%
        self::assertEquals($enemyUnit->getTotalLife() - 20, $enemyUnit->getLife());
    }

    /**
     * @return array
     */
    public function invalidEffectDataProvider(): array
    {
        return [
            [
                // Отсутствует on_apply_actions
                [
                    'name'                  => 'Battle Fury',
                    'icon'                  => '/images/icons/ability/102.png',
                    'duration'              => 15,
                    'on_next_round_actions' => [],
                    'on_disable_actions'    => [],
                ],
            ],
            [
                // Отсутствует on_next_round_actions
                [
                    'name'               => 'Battle Fury',
                    'icon'               => '/images/icons/ability/102.png',
                    'duration'           => 15,
                    'on_apply_actions'   => [],
                    'on_disable_actions' => [],
                ],
            ],
            [
                // Отсутствует on_disable_actions
                [
                    'name'                  => 'Battle Fury',
                    'icon'                  => '/images/icons/ability/102.png',
                    'duration'              => 15,
                    'on_apply_actions'      => [],
                    'on_next_round_actions' => [],
                ],
            ],
        ];
    }
}
