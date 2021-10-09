<?php

declare(strict_types=1);

namespace Tests\Battle\Action;

use Battle\Action\ActionException;
use Battle\Action\ActionFactory;
use Battle\Action\ActionInterface;
use Battle\Action\BuffAction;
use Battle\Action\DamageAction;
use Battle\Action\EffectAction;
use Battle\Action\HealAction;
use Battle\Action\ResurrectionAction;
use Battle\Action\SummonAction;
use Battle\Action\WaitAction;
use Battle\Command\CommandException;
use Battle\Command\CommandFactory;
use Battle\Unit\Effect\EffectFactory;
use Battle\Unit\UnitException;
use Exception;
use PHPUnit\Framework\TestCase;
use Tests\Battle\Factory\BaseFactory;
use Tests\Battle\Factory\UnitFactory as TestUnitFactory;
use Tests\Battle\Factory\UnitFactoryException;
use Battle\Unit\UnitFactory;

class ActionFactoryTest extends TestCase
{
    /**
     * Тест на успешное создание DamageAction на основе массива с данными
     *
     * @throws Exception
     */
    public function testActionFactoryCreateDamageSuccess(): void
    {
        [$unit, $command, $enemyCommand] = BaseFactory::create(1, 2);

        $actionFactory = new ActionFactory();

        // Вариант данных без damage, name и animation_method
        $data = [
            'type'           => ActionInterface::DAMAGE,
            'action_unit'    => $unit,
            'enemy_command'  => $enemyCommand,
            'allies_command' => $command,
            'type_target'    => ActionInterface::TARGET_RANDOM_ENEMY,
        ];

        $action = $actionFactory->create($data);

        self::assertInstanceOf(DamageAction::class, $action);
        self::assertEquals($unit, $action->getActionUnit());
        self::assertEquals(ActionInterface::TARGET_RANDOM_ENEMY, $action->getTypeTarget());
        self::assertEquals($unit->getDamage(), $action->getPower());
        self::assertEquals('attack', $action->getNameAction());

        // Полный набор данных
        $data = [
            'type'             => ActionInterface::DAMAGE,
            'action_unit'      => $unit,
            'enemy_command'    => $enemyCommand,
            'allies_command'   => $command,
            'type_target'      => ActionInterface::TARGET_RANDOM_ENEMY,
            'power'            => $damage = 50,
            'name'             => $name = 'action name 123',
            'animation_method' => $animationMethod = 'effectDamage',
        ];

        $action = $actionFactory->create($data);

        self::assertInstanceOf(DamageAction::class, $action);
        self::assertEquals($unit, $action->getActionUnit());
        self::assertEquals(ActionInterface::TARGET_RANDOM_ENEMY, $action->getTypeTarget());
        self::assertEquals($damage, $action->getPower());
        self::assertEquals($name, $action->getNameAction());
        self::assertEquals($animationMethod, $action->getAnimationMethod());
    }

    /**
     * Тест на успешное создание HealAction на основе массива с данными
     *
     * @throws Exception
     */
    public function testActionFactoryCreateHealSuccess(): void
    {
        [$unit, $command, $enemyCommand] = BaseFactory::create(1, 2);

        $actionFactory = new ActionFactory();

        // Вариант данных без damage и name
        $data = [
            'type'           => ActionInterface::HEAL,
            'action_unit'    => $unit,
            'enemy_command'  => $enemyCommand,
            'allies_command' => $command,
            'type_target'    => ActionInterface::TARGET_WOUNDED_ALLIES,
        ];

        $action = $actionFactory->create($data);

        self::assertInstanceOf(HealAction::class, $action);
        self::assertEquals($unit, $action->getActionUnit());
        self::assertEquals(ActionInterface::TARGET_WOUNDED_ALLIES, $action->getTypeTarget());
        self::assertEquals($unit->getDamage(), $action->getPower());
        self::assertEquals('heal', $action->getNameAction());

        // Полный набор данных
        $data = [
            'type'             => ActionInterface::HEAL,
            'action_unit'      => $unit,
            'enemy_command'    => $enemyCommand,
            'allies_command'   => $command,
            'type_target'      => ActionInterface::TARGET_WOUNDED_ALLIES,
            'power'            => $power = 50,
            'name'             => $name = 'action name 123',
            'animation_method' => $animationMethod = 'effectHeal',
        ];

        $action = $actionFactory->create($data);

        self::assertInstanceOf(HealAction::class, $action);
        self::assertEquals($unit, $action->getActionUnit());
        self::assertEquals(ActionInterface::TARGET_WOUNDED_ALLIES, $action->getTypeTarget());
        self::assertEquals($power, $action->getPower());
        self::assertEquals($name, $action->getNameAction());
        self::assertEquals($animationMethod, $action->getAnimationMethod());
    }

    /**
     * Тест на успешное создание WaitAction на основе массива с данными
     *
     * @throws Exception
     */
    public function testActionFactoryCreateWaitSuccess(): void
    {
        [$unit, $command, $enemyCommand] = BaseFactory::create(1, 2);

        $actionFactory = new ActionFactory();

        $data = [
            'type'           => ActionInterface::WAIT,
            'action_unit'    => $unit,
            'enemy_command'  => $enemyCommand,
            'allies_command' => $command,
        ];

        $action = $actionFactory->create($data);

        self::assertInstanceOf(WaitAction::class, $action);
        self::assertEquals($unit, $action->getActionUnit());
        self::assertEquals(ActionInterface::TARGET_SELF, $action->getTypeTarget());
        self::assertEquals('preparing to attack', $action->getNameAction());
    }

    /**
     * Тест на успешное создание SummonAction на основе массива с данными
     *
     * @throws Exception
     */
    public function testActionFactoryCreateSummonSuccess(): void
    {
        [$unit, $command, $enemyCommand] = BaseFactory::create(1, 2);

        $actionFactory = new ActionFactory();

        $summonData = [
            'name'         => 'Imp',
            'level'        => 1,
            'avatar'       => '/images/avas/monsters/004.png',
            'damage'       => 10,
            'attack_speed' => 1,
            'life'         => 30,
            'total_life'   => 30,
            'melee'        => true,
            'class'        => 1,
            'race'         => 9,
        ];

        $data = [
            'type'           => ActionInterface::SUMMON,
            'action_unit'    => $unit,
            'enemy_command'  => $enemyCommand,
            'allies_command' => $command,
            'name'           => 'Summon Imp',
            'summon'         => $summonData,
        ];

        $action = $actionFactory->create($data);

        self::assertInstanceOf(SummonAction::class, $action);
        self::assertEquals($unit, $action->getActionUnit());
        self::assertEquals(ActionInterface::TARGET_SELF, $action->getTypeTarget());
        self::assertEquals('Summon Imp', $action->getNameAction());

        self::assertEquals($summonData['name'], $action->getSummonUnit()->getName());
        self::assertEquals($summonData['level'], $action->getSummonUnit()->getLevel());
        self::assertEquals($summonData['avatar'], $action->getSummonUnit()->getAvatar());
        self::assertEquals($summonData['damage'], $action->getSummonUnit()->getDamage());
        self::assertEquals($summonData['attack_speed'], $action->getSummonUnit()->getAttackSpeed());
        self::assertEquals($summonData['life'], $action->getSummonUnit()->getLife());
        self::assertEquals($summonData['total_life'], $action->getSummonUnit()->getTotalLife());
        self::assertEquals($summonData['melee'], $action->getSummonUnit()->isMelee());
        self::assertEquals($summonData['class'], $action->getSummonUnit()->getClass()->getId());
        self::assertEquals($summonData['race'], $action->getSummonUnit()->getRace()->getId());
    }

    /**
     * Тест на успешное создание BuffAction на основе массива с данными
     *
     * @throws Exception
     */
    public function testActionFactoryCreateBuffSuccess(): void
    {
        [$unit, $command, $enemyCommand] = BaseFactory::create(1, 2);

        $actionFactory = new ActionFactory();

        $data = [
            'type'           => ActionInterface::BUFF,
            'action_unit'    => $unit,
            'enemy_command'  => $enemyCommand,
            'allies_command' => $command,
            'type_target'    => ActionInterface::TARGET_SELF,
            'name'           => $name = 'buff name test',
            'modify_method'  => $modifyMethod = 'modifyMethod',
            'power'          => $power = 150,
        ];

        $action = $actionFactory->create($data);

        self::assertInstanceOf(BuffAction::class, $action);
        self::assertEquals($unit, $action->getActionUnit());
        self::assertEquals(ActionInterface::TARGET_SELF, $action->getTypeTarget());
        self::assertEquals($power, $action->getPower());
        self::assertEquals($name, $action->getNameAction());
        self::assertEquals($modifyMethod, $action->getModifyMethod());
    }

    /**
     * Тест на успешное создание ResurrectionAction на основе массива с данными
     *
     * @throws Exception
     */
    public function testActionFactoryCreateResurrectionSuccess(): void
    {
        [$unit, $command, $enemyCommand] = BaseFactory::create(1, 2);

        $actionFactory = new ActionFactory();

        $data = [
            'type'           => ActionInterface::RESURRECTION,
            'action_unit'    => $unit,
            'enemy_command'  => $enemyCommand,
            'allies_command' => $command,
            'type_target'    => ActionInterface::TARGET_DEAD_ALLIES,
            'name'           => $name = 'resurrection name test',
            'power'          => $power = 50,
        ];

        $action = $actionFactory->create($data);

        self::assertInstanceOf(ResurrectionAction::class, $action);
        self::assertEquals($unit, $action->getActionUnit());
        self::assertEquals(ActionInterface::TARGET_DEAD_ALLIES, $action->getTypeTarget());
        self::assertEquals($power, $action->getPower());
        self::assertEquals($name, $action->getNameAction());
    }

    /**
     * Тест на успешное создание EffectAction на основе массива с данными
     *
     * @throws Exception
     */
    public function testActionFactoryCreateEffectSuccess(): void
    {
        [$unit, $command, $enemyCommand] = BaseFactory::create(1, 2);

        $actionFactory = new ActionFactory();
        $effectFactory = new EffectFactory($actionFactory);

        $data = [
            'type'           => ActionInterface::EFFECT,
            'action_unit'    => $unit,
            'enemy_command'  => $enemyCommand,
            'allies_command' => $command,
            'type_target'    => ActionInterface::TARGET_SELF,
            'name'           => $name = 'Effect test',
            'effect'         => [
                'name'                  => 'Effect test #1',
                'icon'                  => 'effect_icon_#1',
                'duration'              => 10,
                'on_apply_actions'      => [
                    [
                        'type'           => ActionInterface::BUFF,
                        'action_unit'    => $unit,
                        'enemy_command'  => $enemyCommand,
                        'allies_command' => $command,
                        'type_target'    => ActionInterface::TARGET_SELF,
                        'name'           => 'use Reserve Forces',
                        'modify_method'  => 'multiplierMaxLife',
                        'power'          => 130,
                    ],
                ],
                'on_next_round_actions' => [],
                'on_disable_actions'    => [],
            ],
        ];

        $action = $actionFactory->create($data);

        self::assertInstanceOf(EffectAction::class, $action);
        self::assertEquals($unit, $action->getActionUnit());
        self::assertEquals(ActionInterface::TARGET_SELF, $action->getTypeTarget());
        self::assertEquals($name, $action->getNameAction());
        self::assertEquals($effectFactory->create($data['effect']), $action->getEffect());
    }

    /**
     * Тесты на различные варианты невалидных данных для (перебираются некорректные варианты для всех видов Action)
     *
     * @dataProvider failDataProvider
     * @param array $data
     * @param string $error
     * @throws Exception
     */
    public function testActionFactoryCreateFail(array $data, string $error): void
    {
        $actionFactory = new ActionFactory();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage($error);
        $actionFactory->create($data);
    }

    /**
     * @return array
     * @throws CommandException
     * @throws UnitException
     * @throws UnitFactoryException
     */
    public function failDataProvider(): array
    {
        $actionUnit = TestUnitFactory::createByTemplate(1);
        $enemyUnit = TestUnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$actionUnit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        return [
            [
                // Отсутствует type
                [
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'type_target'    => ActionInterface::TARGET_RANDOM_ENEMY,
                    'power'          => 50,
                    'name'           => 'action name',
                ],
                ActionException::INVALID_TYPE_DATA,
            ],
            [
                // type некорректного типа
                [
                    'type'           => 'ActionInterface::DAMAGE',
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'type_target'    => ActionInterface::TARGET_RANDOM_ENEMY,
                    'power'          => 50,
                    'name'           => 'action name',
                ],
                ActionException::INVALID_TYPE_DATA,
            ],
            [
                // type несуществующего типа Action
                [
                    'type'           => 33,
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'type_target'    => ActionInterface::TARGET_RANDOM_ENEMY,
                    'power'          => 50,
                    'name'           => 'action name',
                ],
                ActionException::UNKNOWN_TYPE_ACTION,
            ],
            [
                // Отсутствует action_unit
                [
                    'type'           => ActionInterface::DAMAGE,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'type_target'    => ActionInterface::TARGET_RANDOM_ENEMY,
                    'power'          => 50,
                    'name'           => 'action name',
                ],
                ActionException::INVALID_ACTION_UNIT_DATA,
            ],
            [
                // action_unit содержит некорректный объект
                [
                    'type'           => ActionInterface::DAMAGE,
                    'action_unit'    => new UnitFactory(),
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'type_target'    => ActionInterface::TARGET_RANDOM_ENEMY,
                    'power'          => 50,
                    'name'           => 'action name',
                ],
                ActionException::INVALID_ACTION_UNIT_DATA,
            ],
            [
                // Отсутствует enemy_command
                [
                    'type'           => ActionInterface::DAMAGE,
                    'action_unit'    => $actionUnit,
                    'allies_command' => $command,
                    'type_target'    => ActionInterface::TARGET_RANDOM_ENEMY,
                    'power'          => 50,
                    'name'           => 'action name',
                ],
                ActionException::INVALID_COMMAND_DATA,
            ],
            [
                // enemy_command содержит некорректный объект
                [
                    'type'           => ActionInterface::DAMAGE,
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => new UnitFactory(),
                    'allies_command' => $command,
                    'type_target'    => ActionInterface::TARGET_RANDOM_ENEMY,
                    'power'          => 50,
                    'name'           => 'action name',
                ],
                ActionException::INVALID_COMMAND_DATA,
            ],
            [
                // Отсутствует allies_command
                [
                    'type'          => ActionInterface::DAMAGE,
                    'action_unit'   => $actionUnit,
                    'enemy_command' => $enemyCommand,
                    'type_target'   => ActionInterface::TARGET_RANDOM_ENEMY,
                    'power'         => 50,
                    'name'          => 'action name',
                ],
                ActionException::INVALID_COMMAND_DATA,
            ],
            [
                // allies_command содержит некорректный объект
                [
                    'type'           => ActionInterface::DAMAGE,
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => new UnitFactory(),
                    'type_target'    => ActionInterface::TARGET_RANDOM_ENEMY,
                    'power'          => 50,
                    'name'           => 'action name',
                ],
                ActionException::INVALID_COMMAND_DATA,
            ],
            [
                // Отсутствует type_target [для DamageAction]
                [
                    'type'           => ActionInterface::DAMAGE,
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'power'          => 50,
                    'name'           => 'action name',
                ],
                ActionException::INVALID_TYPE_TARGET_DATA,
            ],
            [
                // type_target некорректного типа [для DamageAction]
                [
                    'type'           => ActionInterface::DAMAGE,
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'type_target'    => true,
                    'power'          => 50,
                    'name'           => 'action name',
                ],
                ActionException::INVALID_TYPE_TARGET_DATA,
            ],
            [
                // Отсутствует type_target [для HealAction]
                [
                    'type'           => ActionInterface::HEAL,
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'power'          => 50,
                    'name'           => 'action name',
                ],
                ActionException::INVALID_TYPE_TARGET_DATA,
            ],
            [
                // type_target некорректного типа [для HealAction]
                [
                    'type'           => ActionInterface::HEAL,
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'type_target'    => true,
                    'power'          => 50,
                    'name'           => 'action name',
                ],
                ActionException::INVALID_TYPE_TARGET_DATA,
            ],
            [
                // damage некорректного типа
                [
                    'type'           => ActionInterface::DAMAGE,
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'type_target'    => ActionInterface::TARGET_RANDOM_ENEMY,
                    'power'          => '50',
                    'name'           => 'action name',
                ],
                ActionException::INVALID_POWER_DATA,
            ],
            [
                // name некорректного типа [для DamageAction]
                [
                    'type'           => ActionInterface::DAMAGE,
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'type_target'    => ActionInterface::TARGET_RANDOM_ENEMY,
                    'power'          => 50,
                    'name'           => ['action name'],
                ],
                ActionException::INVALID_NAME_DATA,
            ],
            [
                // name некорректного типа [для HealAction]
                [
                    'type'           => ActionInterface::DAMAGE,
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'type_target'    => ActionInterface::TARGET_RANDOM_ENEMY,
                    'power'          => 50,
                    'name'           => 123,
                ],
                ActionException::INVALID_NAME_DATA,
            ],
            [
                // Отсутствует name [для SummonAction]
                [
                    'type'           => ActionInterface::SUMMON,
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                ],
                ActionException::INVALID_NAME_DATA,
            ],
            [
                // name некорректного типа [для SummonAction]
                [
                    'type'           => ActionInterface::SUMMON,
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'name'           => [],
                ],
                ActionException::INVALID_NAME_DATA,
            ],
            [
                // Отсутствует summon [для SummonAction]
                [
                    'type'           => ActionInterface::SUMMON,
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'name'           => 'summon test',
                ],
                ActionException::INVALID_SUMMON_DATA,
            ],
            [
                // summon некорректного типа [для SummonAction]
                [
                    'type'           => ActionInterface::SUMMON,
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'name'           => 'summon test',
                    'summon'         => 'summon data',
                ],
                ActionException::INVALID_SUMMON_DATA,
            ],
            [
                // summon не содержит нужных параметров [для SummonAction]. Для данного теста достаточно одной проверки,
                // так как все варианты невалидных данных по юниту проверяются уже в UnitFactory
                [
                    'type'           => ActionInterface::SUMMON,
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'name'           => 'summon test',
                    'summon'         => [],
                ],
                UnitException::INCORRECT_NAME,
            ],
            [
                // Отсутствует type_target [для BuffAction]
                [
                    'type'           => ActionInterface::HEAL,
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'name'           => 'buff test',
                    'modify_method'  => 'modify method test',
                    'power'          => 150,
                ],
                ActionException::INVALID_TYPE_TARGET_DATA,
            ],
            [
                // type_target некорректного типа [для BuffAction]
                [
                    'type'           => ActionInterface::HEAL,
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'type_target'    => true,
                    'name'           => 'buff test',
                    'modify_method'  => 'modify method test',
                    'power'          => 150,
                ],
                ActionException::INVALID_TYPE_TARGET_DATA,
            ],
            [
                // Отсутствует name [для BuffAction]
                [
                    'type'           => ActionInterface::BUFF,
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'type_target'    => ActionInterface::TARGET_SELF,
                    'modify_method'  => 'modify method test',
                    'power'          => 150,
                ],
                ActionException::INVALID_NAME_DATA,
            ],
            [
                // name некорректного типа [для BuffAction]
                [
                    'type'           => ActionInterface::BUFF,
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'type_target'    => ActionInterface::TARGET_SELF,
                    'name'           => 123,
                    'modify_method'  => 'modify method test',
                    'power'          => 150,
                ],
                ActionException::INVALID_NAME_DATA,
            ],
            [
                // Отсутствует modify_method [для BuffAction]
                [
                    'type'           => ActionInterface::BUFF,
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'type_target'    => ActionInterface::TARGET_SELF,
                    'name'           => 'buff test',
                    'power'          => 150,
                ],
                ActionException::INVALID_MODIFY_METHOD_DATA,
            ],
            [
                // modify_method некорректного типа [для BuffAction]
                [
                    'type'           => ActionInterface::BUFF,
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'type_target'    => ActionInterface::TARGET_SELF,
                    'name'           => 'buff test',
                    'modify_method'  => ['modify method test'],
                    'power'          => 150,
                ],
                ActionException::INVALID_MODIFY_METHOD_DATA,
            ],
            [
                // Отсутствует power [для BuffAction]
                [
                    'type'           => ActionInterface::BUFF,
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'type_target'    => ActionInterface::TARGET_SELF,
                    'name'           => 'buff test',
                    'modify_method'  => 'modify method test',
                ],
                ActionException::INVALID_POWER_DATA,
            ],
            [
                // power некорректного типа [для BuffAction]
                [
                    'type'           => ActionInterface::BUFF,
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'type_target'    => ActionInterface::TARGET_SELF,
                    'name'           => 'buff test',
                    'modify_method'  => 'modify method test',
                    'power'          => '150',
                ],
                ActionException::INVALID_POWER_DATA,
            ],
            // EffectAction
            // Отсутствует type_target
            [
                [
                    'type'           => ActionInterface::EFFECT,
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'name'           => 'Effect test',
                    'effects'        => [
                        [
                            'name'                  => 'Effect test #1',
                            'icon'                  => 'effect_icon_#1',
                            'duration'              => 10,
                            'on_apply_actions'      => [
                                [
                                    'type'           => ActionInterface::BUFF,
                                    'action_unit'    => $actionUnit,
                                    'enemy_command'  => $enemyCommand,
                                    'allies_command' => $command,
                                    'type_target'    => ActionInterface::TARGET_SELF,
                                    'name'           => 'use Reserve Forces',
                                    'modify_method'  => 'multiplierMaxLife',
                                    'power'          => 130,
                                ],
                            ],
                            'on_next_round_actions' => [],
                            'on_disable_actions'    => [],
                        ],
                    ],
                ],
                ActionException::INVALID_TYPE_TARGET_DATA,
            ],
            // type_target некорректного типа
            [
                [
                    'type'           => ActionInterface::EFFECT,
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'type_target'    => 'self',
                    'name'           => 'Effect test',
                    'effects'        => [
                        [
                            'name'                  => 'Effect test #1',
                            'icon'                  => 'effect_icon_#1',
                            'duration'              => 10,
                            'on_apply_actions'      => [
                                [
                                    'type'           => ActionInterface::BUFF,
                                    'action_unit'    => $actionUnit,
                                    'enemy_command'  => $enemyCommand,
                                    'allies_command' => $command,
                                    'type_target'    => ActionInterface::TARGET_SELF,
                                    'name'           => 'use Reserve Forces',
                                    'modify_method'  => 'multiplierMaxLife',
                                    'power'          => 130,
                                ],
                            ],
                            'on_next_round_actions' => [],
                            'on_disable_actions'    => [],
                        ],
                    ],
                ],
                ActionException::INVALID_TYPE_TARGET_DATA,
            ],
            // Отсутствует name
            [
                [
                    'type'           => ActionInterface::EFFECT,
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'type_target'    => ActionInterface::TARGET_SELF,
                    'effects'        => [
                        [
                            'name'                  => 'Effect test #1',
                            'icon'                  => 'effect_icon_#1',
                            'duration'              => 10,
                            'on_apply_actions'      => [
                                [
                                    'type'           => ActionInterface::BUFF,
                                    'action_unit'    => $actionUnit,
                                    'enemy_command'  => $enemyCommand,
                                    'allies_command' => $command,
                                    'type_target'    => ActionInterface::TARGET_SELF,
                                    'name'           => 'use Reserve Forces',
                                    'modify_method'  => 'multiplierMaxLife',
                                    'power'          => 130,
                                ],
                            ],
                            'on_next_round_actions' => [],
                            'on_disable_actions'    => [],
                        ],
                    ],
                ],
                ActionException::INVALID_NAME_DATA,
            ],
            // name некорректного типа
            [
                [
                    'type'           => ActionInterface::EFFECT,
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'type_target'    => ActionInterface::TARGET_SELF,
                    'name'           => true,
                    'effects'        => [
                        [
                            'name'                  => 'Effect test #1',
                            'icon'                  => 'effect_icon_#1',
                            'duration'              => 10,
                            'on_apply_actions'      => [
                                [
                                    'type'           => ActionInterface::BUFF,
                                    'action_unit'    => $actionUnit,
                                    'enemy_command'  => $enemyCommand,
                                    'allies_command' => $command,
                                    'type_target'    => ActionInterface::TARGET_SELF,
                                    'name'           => 'use Reserve Forces',
                                    'modify_method'  => 'multiplierMaxLife',
                                    'power'          => 130,
                                ],
                            ],
                            'on_next_round_actions' => [],
                            'on_disable_actions'    => [],
                        ],
                    ],
                ],
                ActionException::INVALID_NAME_DATA,
            ],
            // Отсутствует effect
            [
                [
                    'type'           => ActionInterface::EFFECT,
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'type_target'    => ActionInterface::TARGET_SELF,
                    'name'           => 'Effect test',
                ],
                ActionException::INVALID_EFFECT_DATA,
            ],
            // effects некорректного типа
            [
                [
                    'type'           => ActionInterface::EFFECT,
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'type_target'    => ActionInterface::TARGET_SELF,
                    'name'           => 'Effect test',
                    'effect'         => 'effects',
                ],
                ActionException::INVALID_EFFECT_DATA,
            ],
            // effects вместо массива данных по эффекту содержит строку
            [
                [
                    'type'           => ActionInterface::EFFECT,
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'type_target'    => ActionInterface::TARGET_SELF,
                    'name'           => 'Effect test',
                    'effects'        => [
                        'buff effect',
                    ],
                ],
                ActionException::INVALID_EFFECT_DATA,
            ],
            // ResurrectionAction - отсутствует type_target
            [
                [
                    'type'           => ActionInterface::RESURRECTION,
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'name'           => 'resurrection name test',
                    'power'          => 50,
                ],
                ActionException::INVALID_TYPE_TARGET_DATA,
            ],
            // ResurrectionAction - type_target некорректного типа
            [
                [
                    'type'           => ActionInterface::RESURRECTION,
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'type_target'    => 'self',
                    'name'           => 'resurrection name test',
                    'power'          => 50,
                ],
                ActionException::INVALID_TYPE_TARGET_DATA,
            ],
            // ResurrectionAction - отсутствует power
            [
                [
                    'type'           => ActionInterface::RESURRECTION,
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'type_target'    => ActionInterface::TARGET_DEAD_ALLIES,
                    'name'           => 'resurrection name test',
                ],
                ActionException::INVALID_POWER_DATA,
            ],
            // ResurrectionAction - power некорректного типа
            [
                [
                    'type'           => ActionInterface::RESURRECTION,
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'type_target'    => ActionInterface::TARGET_DEAD_ALLIES,
                    'name'           => 'resurrection name test',
                    'power'          => true,
                ],
                ActionException::INVALID_POWER_DATA,
            ],
            // ResurrectionAction - отсутствует name
            [
                [
                    'type'           => ActionInterface::RESURRECTION,
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'type_target'    => ActionInterface::TARGET_DEAD_ALLIES,
                    'power'          => 50,
                ],
                ActionException::INVALID_NAME_DATA,
            ],
            // ResurrectionAction - name некорректного типа
            [
                [
                    'type'           => ActionInterface::RESURRECTION,
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'type_target'    => ActionInterface::TARGET_DEAD_ALLIES,
                    'name'           => 100,
                    'power'          => 50,
                ],
                ActionException::INVALID_NAME_DATA,
            ],
        ];
    }
}
