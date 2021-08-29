<?php

declare(strict_types=1);

namespace Tests\Battle\Action;

use Battle\Action\ActionException;
use Battle\Action\ActionFactory;
use Battle\Action\ActionInterface;
use Battle\Action\DamageAction;
use Battle\Action\HealAction;
use Battle\Action\WaitAction;
use Battle\Command\CommandException;
use Battle\Command\CommandFactory;
use Battle\Unit\UnitException;
use Exception;
use PHPUnit\Framework\TestCase;
use Tests\Battle\Factory\BaseFactory;
use Tests\Battle\Factory\UnitFactory;
use Tests\Battle\Factory\UnitFactoryException;

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

        // Вариант данных без damage и name
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
            'type'           => ActionInterface::DAMAGE,
            'action_unit'    => $unit,
            'enemy_command'  => $enemyCommand,
            'allies_command' => $command,
            'type_target'    => ActionInterface::TARGET_RANDOM_ENEMY,
            'power'          => $damage = 50,
            'name'           => $name = 'action name 123',
        ];

        $action = $actionFactory->create($data);

        self::assertInstanceOf(DamageAction::class, $action);
        self::assertEquals($unit, $action->getActionUnit());
        self::assertEquals(ActionInterface::TARGET_RANDOM_ENEMY, $action->getTypeTarget());
        self::assertEquals($damage, $action->getPower());
        self::assertEquals($name, $action->getNameAction());
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
        self::assertEquals((int)($unit->getDamage() * 1.2), $action->getPower());
        self::assertEquals('heal', $action->getNameAction());

        // Полный набор данных
        $data = [
            'type'           => ActionInterface::HEAL,
            'action_unit'    => $unit,
            'enemy_command'  => $enemyCommand,
            'allies_command' => $command,
            'type_target'    => ActionInterface::TARGET_WOUNDED_ALLIES,
            'power'          => $power = 50,
            'name'           => $name = 'action name 123',
        ];

        $action = $actionFactory->create($data);

        self::assertInstanceOf(HealAction::class, $action);
        self::assertEquals($unit, $action->getActionUnit());
        self::assertEquals(ActionInterface::TARGET_WOUNDED_ALLIES, $action->getTypeTarget());
        self::assertEquals($power, $action->getPower());
        self::assertEquals($name, $action->getNameAction());
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

        // Вариант данных без damage и name
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
     * Временный тест на создание Action, который пока не реализован
     *
     * @throws Exception
     */
    public function testActionFactoryNoRealize(): void
    {
        [$unit, $command, $enemyCommand] = BaseFactory::create(1, 2);

        $actionFactory = new ActionFactory();

        // Вариант данных без damage и name
        $data = [
            'type'           => ActionInterface::EFFECT,
            'action_unit'    => $unit,
            'enemy_command'  => $enemyCommand,
            'allies_command' => $command,
            'type_target'    => ActionInterface::TARGET_WOUNDED_ALLIES,
        ];

        $this->expectException(ActionException::class);
        $this->expectExceptionMessage(ActionException::NO_REALIZE);
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
        $actionUnit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
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
                // Отсутствует type_target
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
                // type_target некорректного типа
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
                // name некорректного типа
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
        ];
    }
}
