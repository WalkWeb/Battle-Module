<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Ability;

use Battle\Action\ActionInterface;
use Battle\Command\CommandFactory;
use Battle\Unit\Ability\Ability;
use Battle\Unit\Ability\AbilityException;
use Battle\Unit\Ability\AbilityInterface;
use Exception;
use Tests\AbstractUnitTest;
use Tests\Battle\Factory\UnitFactory;

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
                    'damage'           => 50,
                    'can_be_avoided'   => true,
                    'name'             => $name,
                    'animation_method' => 'damageAbility',
                    'message_method'   => 'damageAbility',
                    'icon'             => $icon,
                ],
            ],
            AbilityInterface::ACTIVATE_CONCENTRATION,
            $chanceActivate
        );

        self::assertEquals($unit, $ability->getUnit());
        self::assertEquals($name, $ability->getName());
        self::assertEquals($icon, $ability->getIcon());
        self::assertEquals($disposable, $ability->isDisposable());

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
            0
        );

        $this->expectException(AbilityException::class);
        $this->expectExceptionMessage(AbilityException::INVALID_EFFECT_DATA);

        $ability->getAction($enemyCommand, $command);
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
                                'modify_method'  => $modifyMethod = 'multiplierAttackSpeed',
                                'power'          => $power = 140,
                                'message_method' => ActionInterface::SKIP_MESSAGE_METHOD,
                            ],
                        ],
                    ],
                ],
            ],
            AbilityInterface::ACTIVATE_RAGE,
            0
        );

        foreach ($ability->getAction($enemyCommand, $command) as $action) {
            self::assertTrue($action->canByUsed());
            $action->handle();
        }

        self::assertCount(1, $unit->getEffects());

        foreach ($unit->getEffects() as $effect) {
            self::assertEquals($name, $effect->getName());
            self::assertEquals($icon, $effect->getIcon());

            foreach ($effect->getOnDisableActions() as $onDisableAction) {
                self::assertEquals($modifyMethod, $onDisableAction->getModifyMethod());
                self::assertEquals($power, $onDisableAction->getPower());
            }
        }
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
                    'name'                  => 'Battle Fury',
                    'icon'                  => '/images/icons/ability/102.png',
                    'duration'              => 15,
                    'on_apply_actions'      => [],
                    'on_disable_actions'    => [],
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
