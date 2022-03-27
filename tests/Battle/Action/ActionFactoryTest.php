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
use Tests\AbstractUnitTest;
use Tests\Battle\Factory\BaseFactory;
use Tests\Battle\Factory\UnitFactory as TestUnitFactory;
use Tests\Battle\Factory\UnitFactoryException;
use Battle\Unit\UnitFactory;

class ActionFactoryTest extends AbstractUnitTest
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

        // Вариант с минимальным набором данных
        $data = [
            'type'           => ActionInterface::DAMAGE,
            'action_unit'    => $unit,
            'enemy_command'  => $enemyCommand,
            'allies_command' => $command,
            'type_target'    => ActionInterface::TARGET_RANDOM_ENEMY,
            'damage'         => $damage = 150,
            'block_ignore'   => 0,
        ];

        $action = $actionFactory->create($data);

        self::assertInstanceOf(DamageAction::class, $action);
        self::assertEquals($unit, $action->getActionUnit());
        self::assertEquals(ActionInterface::TARGET_RANDOM_ENEMY, $action->getTypeTarget());
        self::assertEquals($damage, $action->getPower());
        self::assertEquals('attack', $action->getNameAction());
        self::assertEquals('', $action->getIcon());
        self::assertEquals(0, $action->getBlockIgnore());

        // Полный набор данных
        $data = [
            'type'             => ActionInterface::DAMAGE,
            'action_unit'      => $unit,
            'enemy_command'    => $enemyCommand,
            'allies_command'   => $command,
            'type_target'      => ActionInterface::TARGET_RANDOM_ENEMY,
            'damage'           => $damage = 50,
            'block_ignore'     => $blockIgnore = 10,
            'name'             => $name = 'action name 123',
            'animation_method' => $animationMethod = 'effectDamage',
            'icon'             => $icon = 'icon.png',
        ];

        $action = $actionFactory->create($data);

        self::assertInstanceOf(DamageAction::class, $action);
        self::assertEquals($unit, $action->getActionUnit());
        self::assertEquals(ActionInterface::TARGET_RANDOM_ENEMY, $action->getTypeTarget());
        self::assertEquals($damage, $action->getPower());
        self::assertEquals($name, $action->getNameAction());
        self::assertEquals($animationMethod, $action->getAnimationMethod());
        self::assertEquals($icon, $action->getIcon());
        self::assertEquals($blockIgnore, $action->getBlockIgnore());
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

        // Вариант с минимальным набором данных
        $data = [
            'type'           => ActionInterface::HEAL,
            'action_unit'    => $unit,
            'enemy_command'  => $enemyCommand,
            'allies_command' => $command,
            'type_target'    => ActionInterface::TARGET_WOUNDED_ALLIES,
            'power'          => $power = 123,
        ];

        $action = $actionFactory->create($data);

        self::assertInstanceOf(HealAction::class, $action);
        self::assertEquals($unit, $action->getActionUnit());
        self::assertEquals(ActionInterface::TARGET_WOUNDED_ALLIES, $action->getTypeTarget());
        self::assertEquals($power, $action->getPower());
        self::assertEquals('heal', $action->getNameAction());
        self::assertEquals('', $action->getIcon());

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
            'icon'             => $icon = 'icon.png',
        ];

        $action = $actionFactory->create($data);

        self::assertInstanceOf(HealAction::class, $action);
        self::assertEquals($unit, $action->getActionUnit());
        self::assertEquals(ActionInterface::TARGET_WOUNDED_ALLIES, $action->getTypeTarget());
        self::assertEquals($power, $action->getPower());
        self::assertEquals($name, $action->getNameAction());
        self::assertEquals($animationMethod, $action->getAnimationMethod());
        self::assertEquals($icon, $action->getIcon());
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
            'accuracy'     => 200,
            'defence'      => 100,
            'block'        => 0,
            'block_ignore' => 0,
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

        // Полный набор данных
        $data = [
            'type'           => ActionInterface::BUFF,
            'action_unit'    => $unit,
            'enemy_command'  => $enemyCommand,
            'allies_command' => $command,
            'type_target'    => ActionInterface::TARGET_SELF,
            'name'           => $name = 'buff name test',
            'modify_method'  => $modifyMethod = 'modifyMethod',
            'power'          => $power = 150,
            'message_method' => ActionInterface::SKIP_MESSAGE_METHOD,
        ];

        $action = $actionFactory->create($data);

        self::assertInstanceOf(BuffAction::class, $action);
        self::assertEquals($unit, $action->getActionUnit());
        self::assertEquals(ActionInterface::TARGET_SELF, $action->getTypeTarget());
        self::assertEquals($power, $action->getPower());
        self::assertEquals($name, $action->getNameAction());
        self::assertEquals($modifyMethod, $action->getModifyMethod());
        self::assertEquals(ActionInterface::SKIP_MESSAGE_METHOD, $action->getMessageMethod());

        // Вариант без message_method
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
        self::assertEquals('buff', $action->getMessageMethod());
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

        // Полный набор данных
        $data = [
            'type'           => ActionInterface::RESURRECTION,
            'action_unit'    => $unit,
            'enemy_command'  => $enemyCommand,
            'allies_command' => $command,
            'type_target'    => ActionInterface::TARGET_DEAD_ALLIES,
            'name'           => $name = 'resurrection name test',
            'power'          => $power = 50,
            'icon'           => $icon = 'icon.png',
        ];

        $action = $actionFactory->create($data);

        self::assertInstanceOf(ResurrectionAction::class, $action);
        self::assertEquals($unit, $action->getActionUnit());
        self::assertEquals(ActionInterface::TARGET_DEAD_ALLIES, $action->getTypeTarget());
        self::assertEquals($power, $action->getPower());
        self::assertEquals($name, $action->getNameAction());
        self::assertEquals($icon, $action->getIcon());

        // Минимальный вариант данных
        $data = [
            'type'           => ActionInterface::RESURRECTION,
            'action_unit'    => $unit,
            'enemy_command'  => $enemyCommand,
            'allies_command' => $command,
            'type_target'    => ActionInterface::TARGET_DEAD_ALLIES,
            'power'          => $power = 50,
        ];

        $action = $actionFactory->create($data);

        self::assertInstanceOf(ResurrectionAction::class, $action);
        self::assertEquals($unit, $action->getActionUnit());
        self::assertEquals(ActionInterface::TARGET_DEAD_ALLIES, $action->getTypeTarget());
        self::assertEquals($power, $action->getPower());
        self::assertEquals('resurrected', $action->getNameAction());
        self::assertEquals('', $action->getIcon());
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

        // Минимальный набор данных
        $data = [
            'type'           => ActionInterface::EFFECT,
            'action_unit'    => $unit,
            'enemy_command'  => $enemyCommand,
            'allies_command' => $command,
            'type_target'    => ActionInterface::TARGET_SELF,
            'name'           => $name = 'Effect test',
            'icon'           => $icon = 'icon.png',
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
        self::assertEquals($icon, $action->getIcon());
        self::assertEquals($effectFactory->create($data['effect']), $action->getEffect());
        self::assertEquals(EffectAction::DEFAULT_ANIMATION_METHOD, $action->getAnimationMethod());
        self::assertEquals(EffectAction::DEFAULT_MESSAGE_METHOD, $action->getMessageMethod());

        // Полный набор данных
        $data = [
            'type'             => ActionInterface::EFFECT,
            'action_unit'      => $unit,
            'enemy_command'    => $enemyCommand,
            'allies_command'   => $command,
            'type_target'      => ActionInterface::TARGET_SELF,
            'name'             => $name = 'Effect test',
            'icon'             => $icon = 'icon.png',
            'effect'           => [
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
            'animation_method' => $animationMethod = 'custom_animation_method',
            'message_method'   => $messageMethod = 'custom_message_method',
        ];

        $action = $actionFactory->create($data);

        self::assertInstanceOf(EffectAction::class, $action);
        self::assertEquals($unit, $action->getActionUnit());
        self::assertEquals(ActionInterface::TARGET_SELF, $action->getTypeTarget());
        self::assertEquals($name, $action->getNameAction());
        self::assertEquals($icon, $action->getIcon());
        self::assertEquals($effectFactory->create($data['effect']), $action->getEffect());
        self::assertEquals($animationMethod, $action->getAnimationMethod());
        self::assertEquals($messageMethod, $action->getMessageMethod());
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
                // 0: Отсутствует type
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
                // 1: type некорректного типа
                [
                    'type'           => 'ActionInterface::DAMAGE',
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'type_target'    => ActionInterface::TARGET_RANDOM_ENEMY,
                    'damage'         => 50,
                    'block_ignore'   => 0,
                    'name'           => 'action name',
                ],
                ActionException::INVALID_TYPE_DATA,
            ],
            [
                // 2: type несуществующего типа Action
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
                // 3: Отсутствует action_unit
                [
                    'type'           => ActionInterface::DAMAGE,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'type_target'    => ActionInterface::TARGET_RANDOM_ENEMY,
                    'damage'         => 50,
                    'block_ignore'   => 0,
                    'name'           => 'action name',
                ],
                ActionException::INVALID_ACTION_UNIT_DATA,
            ],
            [
                // 4: action_unit содержит некорректный объект
                [
                    'type'           => ActionInterface::DAMAGE,
                    'action_unit'    => new UnitFactory(),
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'type_target'    => ActionInterface::TARGET_RANDOM_ENEMY,
                    'damage'         => 50,
                    'block_ignore'   => 0,
                    'name'           => 'action name',
                ],
                ActionException::INVALID_ACTION_UNIT_DATA,
            ],
            [
                // 5: Отсутствует enemy_command
                [
                    'type'           => ActionInterface::DAMAGE,
                    'action_unit'    => $actionUnit,
                    'allies_command' => $command,
                    'type_target'    => ActionInterface::TARGET_RANDOM_ENEMY,
                    'damage'         => 50,
                    'block_ignore'   => 0,
                    'name'           => 'action name',
                ],
                ActionException::INVALID_COMMAND_DATA,
            ],
            [
                // 6: enemy_command содержит некорректный объект
                [
                    'type'           => ActionInterface::DAMAGE,
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => new UnitFactory(),
                    'allies_command' => $command,
                    'type_target'    => ActionInterface::TARGET_RANDOM_ENEMY,
                    'damage'         => 50,
                    'block_ignore'   => 0,
                    'name'           => 'action name',
                ],
                ActionException::INVALID_COMMAND_DATA,
            ],
            [
                // 7: Отсутствует allies_command
                [
                    'type'          => ActionInterface::DAMAGE,
                    'action_unit'   => $actionUnit,
                    'enemy_command' => $enemyCommand,
                    'type_target'   => ActionInterface::TARGET_RANDOM_ENEMY,
                    'damage'        => 50,
                    'block_ignore'  => 0,
                    'name'          => 'action name',
                ],
                ActionException::INVALID_COMMAND_DATA,
            ],
            [
                // 8: allies_command содержит некорректный объект
                [
                    'type'           => ActionInterface::DAMAGE,
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => new UnitFactory(),
                    'type_target'    => ActionInterface::TARGET_RANDOM_ENEMY,
                    'damage'         => 50,
                    'block_ignore'   => 0,
                    'name'           => 'action name',
                ],
                ActionException::INVALID_COMMAND_DATA,
            ],
            [
                // 9: Отсутствует type_target [для DamageAction]
                [
                    'type'           => ActionInterface::DAMAGE,
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'damage'         => 50,
                    'block_ignore'   => 0,
                    'name'           => 'action name',
                ],
                ActionException::INVALID_TYPE_TARGET_DATA,
            ],
            [
                // 10: type_target некорректного типа [для DamageAction]
                [
                    'type'           => ActionInterface::DAMAGE,
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'type_target'    => true,
                    'damage'         => 50,
                    'block_ignore'   => 0,
                    'name'           => 'action name',
                ],
                ActionException::INVALID_TYPE_TARGET_DATA,
            ],
            [
                // 11: Отсутствует block_ignore [для DamageAction]
                [
                    'type'           => ActionInterface::DAMAGE,
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'type_target'    => ActionInterface::TARGET_RANDOM_ENEMY,
                    'damage'         => $damage = 150,
                ],
                ActionException::INVALID_BLOCK_IGNORE_DATA,
            ],
            [
                // 12: block_ignore некорректного типа [для DamageAction]
                [
                    'type'           => ActionInterface::DAMAGE,
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'type_target'    => ActionInterface::TARGET_RANDOM_ENEMY,
                    'damage'         => $damage = 150,
                    'block_ignore'   => '0',
                ],
                ActionException::INVALID_BLOCK_IGNORE_DATA,
            ],
            [
                // 13: Отсутствует type_target [для HealAction]
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
                // 14: type_target некорректного типа [для HealAction]
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
                // 15: damage некорректного типа
                [
                    'type'           => ActionInterface::DAMAGE,
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'type_target'    => ActionInterface::TARGET_RANDOM_ENEMY,
                    'damage'         => '50',
                    'block_ignore'   => 0,
                    'name'           => 'action name',
                ],
                ActionException::INVALID_DAMAGE_DATA,
            ],
            [
                // 16: name некорректного типа [для DamageAction]
                [
                    'type'           => ActionInterface::DAMAGE,
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'type_target'    => ActionInterface::TARGET_RANDOM_ENEMY,
                    'damage'         => 50,
                    'block_ignore'   => 0,
                    'name'           => ['action name'],
                ],
                ActionException::INVALID_NAME_DATA,
            ],
            [
                // 17: name некорректного типа [для HealAction]
                [
                    'type'           => ActionInterface::HEAL,
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'type_target'    => ActionInterface::TARGET_RANDOM_ENEMY,
                    'power'          => 50,
                    'block_ignore'   => 0,
                    'name'           => 123,
                ],
                ActionException::INVALID_NAME_DATA,
            ],
            [
                // 18: Отсутствует name [для SummonAction]
                [
                    'type'           => ActionInterface::SUMMON,
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                ],
                ActionException::INVALID_NAME_DATA,
            ],
            [
                // 19: name некорректного типа [для SummonAction]
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
                // 20: Отсутствует summon [для SummonAction]
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
                // 21: summon некорректного типа [для SummonAction]
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
                // 22: summon не содержит нужных параметров [для SummonAction]. Для данного теста достаточно одной проверки,
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
                // 23: Отсутствует type_target [для BuffAction]
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
                // 24: type_target некорректного типа [для BuffAction]
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
                // 25: Отсутствует name [для BuffAction]
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
                // 26: name некорректного типа [для BuffAction]
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
                // 27: Отсутствует modify_method [для BuffAction]
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
                // 28: modify_method некорректного типа [для BuffAction]
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
                // 29: Отсутствует power [для BuffAction]
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
                // 30: power некорректного типа [для BuffAction]
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
            [
                // 31: message_method некорректного типа [для BuffAction]
                [
                    'type'           => ActionInterface::BUFF,
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'type_target'    => ActionInterface::TARGET_SELF,
                    'name'           => 'buff test',
                    'modify_method'  => 'modify method test',
                    'power'          => 150,
                    'message_method' => 123,
                ],
                ActionException::INVALID_MESSAGE_METHOD,
            ],
            // EffectAction
            // 32: Отсутствует type_target
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
            // 33: type_target некорректного типа
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
            // 34: Отсутствует name
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
            // 35: name некорректного типа
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
            // 36: Отсутствует effect
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
            // 37: effects некорректного типа
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
            // 38: effects вместо массива данных по эффекту содержит строку
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
            // 39: ResurrectionAction - отсутствует type_target
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
            // 40: ResurrectionAction - type_target некорректного типа
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
            // 41: ResurrectionAction - отсутствует power
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
            // 42: ResurrectionAction - power некорректного типа
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
            // 43: ResurrectionAction - name некорректного типа
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
            // 44: DamageAction - некорректный icon
            [
                [
                    'type'           => ActionInterface::DAMAGE,
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'type_target'    => ActionInterface::TARGET_RANDOM_ENEMY,
                    'icon'           => 123,
                    'damage'         => 30,
                    'block_ignore'   => 0,
                ],
                ActionException::INVALID_ICON,
            ],
            // 45: EffectAction - некорректный icon
            [
                [
                    'type'           => ActionInterface::EFFECT,
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'type_target'    => ActionInterface::TARGET_SELF,
                    'name'           => 'Effect',
                    'icon'           => true,
                    'effect'           => [
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
                ActionException::INVALID_ICON,
            ],
            // 46: EffectAction - некорректный animation_method
            [
                [
                    'type'             => ActionInterface::EFFECT,
                    'action_unit'      => $actionUnit,
                    'enemy_command'    => $enemyCommand,
                    'allies_command'   => $command,
                    'type_target'      => ActionInterface::TARGET_SELF,
                    'name'             => 'Effect',
                    'icon'             => 'icon.png',
                    'effect'           => [
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
                    'animation_method' => 123,
                ],
                ActionException::INVALID_ANIMATION_DATA,
            ],
            // 47: EffectAction - некорректный message_method
            [
                [
                    'type'           => ActionInterface::EFFECT,
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'type_target'    => ActionInterface::TARGET_SELF,
                    'name'           => 'Effect',
                    'icon'           => 'icon.png',
                    'effect'           => [
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
                    'message_method' => true,
                ],
                ActionException::INVALID_MESSAGE_METHOD,
            ],
            [
                // 48: Некорректный power для HealAction
                [
                    'type'           => ActionInterface::HEAL,
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'type_target'    => ActionInterface::TARGET_WOUNDED_ALLIES,
                    'power'          => true,
                ],
                ActionException::INVALID_POWER_DATA,
            ],
            [
                // 49: отсутствует damage для DamageAction
                [
                    'type'           => ActionInterface::DAMAGE,
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'type_target'    => ActionInterface::TARGET_RANDOM_ENEMY,
                    'block_ignore'   => 0,
                    'name'           => 'action name',
                ],
                ActionException::INVALID_DAMAGE_DATA,
            ],
        ];
    }
}
