<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Ability;

use Battle\Action\ActionCollection;
use Battle\Action\ActionFactory;
use Battle\Action\ActionInterface;
use Battle\Command\CommandFactory;
use Battle\Command\CommandInterface;
use Battle\Unit\Ability\AbilityException;
use Battle\Unit\Ability\AbilityFactory;
use Battle\Unit\Ability\AbilityInterface;
use Battle\Unit\UnitInterface;
use Exception;
use Tests\AbstractUnitTest;
use Tests\Battle\Factory\UnitFactory;

class AbilityFactoryTest extends AbstractUnitTest
{
    /**
     * Тест на успешное создание способности на основе массива параметров
     *
     * @dataProvider successDataProvider
     * @param array $data
     * @throws Exception
     */
    public function testAbilityFactoryCreateSuccess(array $data): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = $this->getFactory()->create($unit, $data);

        self::assertEquals($data['name'], $ability->getName());
        self::assertEquals($data['icon'], $ability->getIcon());
        self::assertEquals($data['disposable'], $ability->isDisposable());

        if (array_key_exists('chance_activate', $data)) {
            self::assertEquals($data['chance_activate'], $ability->getChanceActivate());
        } else {
            self::assertEquals(0, $ability->getChanceActivate());
        }

        self::assertEquals(
            $this->createActionCollections($unit, $enemyCommand, $command, $data['actions']),
            $ability->getActions($enemyCommand, $command)
        );
    }

    /**
     * Тесты на различные невалидные варианты данных
     *
     * @dataProvider failDataProvider
     * @param array $data
     * @param string $error
     * @throws Exception
     */
    public function testAbilityFactoryCreateFail(array $data, string $error): void
    {
        $unit = UnitFactory::createByTemplate(1);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage($error);

        $this->getFactory()->create($unit, $data);
    }

    /**
     * @return array
     */
    public function successDataProvider(): array
    {
        return [
            [
                // Вариант с полным набором данных (с chance_activate)
                [
                    'name'            => 'Demo Ability',
                    'icon'            => 'icon.png',
                    'disposable'      => true,
                    'type_activate'   => AbilityInterface::ACTIVATE_CONCENTRATION,
                    'actions'         => [
                        [
                            'type'             => ActionInterface::DAMAGE,
                            'type_target'      => ActionInterface::TARGET_RANDOM_ENEMY,
                            'damage'           => 50,
                            'can_be_avoided'   => true,
                            'name'             => 'Demo Ability',
                            'animation_method' => 'damage',
                            'message_method'   => 'damageAbility',
                            'icon'             => 'icon.png',
                        ],
                    ],
                    'chance_activate' => 50,
                ],
            ],
            [
                // Вариант с неполным набором данных (без chance_activate)
                [
                    'name'            => 'Demo Ability',
                    'icon'            => 'icon.png',
                    'disposable'      => true,
                    'type_activate'   => AbilityInterface::ACTIVATE_CONCENTRATION,
                    'actions'         => [
                        [
                            'type'             => ActionInterface::DAMAGE,
                            'type_target'      => ActionInterface::TARGET_RANDOM_ENEMY,
                            'damage'           => 50,
                            'can_be_avoided'   => true,
                            'name'             => 'Demo Ability',
                            'animation_method' => 'damage',
                            'message_method'   => 'damageAbility',
                            'icon'             => 'icon.png',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function failDataProvider(): array
    {
        return [
            // Отсутствует name
            [
                [
                    'icon'            => 'icon.png',
                    'disposable'      => true,
                    'type_activate'   => AbilityInterface::ACTIVATE_CONCENTRATION,
                    'actions'         => [
                        [
                            'type'             => ActionInterface::DAMAGE,
                            'type_target'      => ActionInterface::TARGET_RANDOM_ENEMY,
                            'damage'           => 50,
                            'can_be_avoided'   => true,
                            'name'             => 'Demo Ability',
                            'animation_method' => 'damage',
                            'message_method'   => 'damageAbility',
                            'icon'             => 'icon.png',
                        ],
                    ],
                    'chance_activate' => 50,
                ],
                AbilityException::INVALID_NAME_DATA,
            ],
            // name некорректного типа
            [
                [
                    'name'            => 10,
                    'icon'            => 'icon.png',
                    'disposable'      => true,
                    'type_activate'   => AbilityInterface::ACTIVATE_CONCENTRATION,
                    'actions'         => [
                        [
                            'type'             => ActionInterface::DAMAGE,
                            'type_target'      => ActionInterface::TARGET_RANDOM_ENEMY,
                            'damage'           => 50,
                            'can_be_avoided'   => true,
                            'name'             => 'Demo Ability',
                            'animation_method' => 'damage',
                            'message_method'   => 'damageAbility',
                            'icon'             => 'icon.png',
                        ],
                    ],
                    'chance_activate' => 50,
                ],
                AbilityException::INVALID_NAME_DATA,
            ],
            // Отсутствует icon
            [
                [
                    'name'            => 'Demo Ability',
                    'disposable'      => true,
                    'type_activate'   => AbilityInterface::ACTIVATE_CONCENTRATION,
                    'actions'         => [
                        [
                            'type'             => ActionInterface::DAMAGE,
                            'type_target'      => ActionInterface::TARGET_RANDOM_ENEMY,
                            'damage'           => 50,
                            'can_be_avoided'   => true,
                            'name'             => 'Demo Ability',
                            'animation_method' => 'damage',
                            'message_method'   => 'damageAbility',
                            'icon'             => 'icon.png',
                        ],
                    ],
                    'chance_activate' => 50,
                ],
                AbilityException::INVALID_ICON_DATA,
            ],
            // icon некорректного типа
            [
                [
                    'name'            => 'Demo Ability',
                    'icon'            => ['icon.png'],
                    'disposable'      => true,
                    'type_activate'   => AbilityInterface::ACTIVATE_CONCENTRATION,
                    'actions'         => [
                        [
                            'type'             => ActionInterface::DAMAGE,
                            'type_target'      => ActionInterface::TARGET_RANDOM_ENEMY,
                            'damage'           => 50,
                            'can_be_avoided'   => true,
                            'name'             => 'Demo Ability',
                            'animation_method' => 'damage',
                            'message_method'   => 'damageAbility',
                            'icon'             => 'icon.png',
                        ],
                    ],
                    'chance_activate' => 50,
                ],
                AbilityException::INVALID_ICON_DATA,
            ],
            // Отсутствует disposable
            [
                [
                    'name'            => 'Demo Ability',
                    'icon'            => 'icon.png',
                    'type_activate'   => AbilityInterface::ACTIVATE_CONCENTRATION,
                    'actions'         => [
                        [
                            'type'             => ActionInterface::DAMAGE,
                            'type_target'      => ActionInterface::TARGET_RANDOM_ENEMY,
                            'damage'           => 50,
                            'can_be_avoided'   => true,
                            'name'             => 'Demo Ability',
                            'animation_method' => 'damage',
                            'message_method'   => 'damageAbility',
                            'icon'             => 'icon.png',
                        ],
                    ],
                    'chance_activate' => 50,
                ],
                AbilityException::INVALID_DISPOSABLE_DATA,
            ],
            // disposable некорректного типа
            [
                [
                    'name'            => 'Demo Ability',
                    'icon'            => 'icon.png',
                    'disposable'      => 1,
                    'type_activate'   => AbilityInterface::ACTIVATE_CONCENTRATION,
                    'actions'         => [
                        [
                            'type'             => ActionInterface::DAMAGE,
                            'type_target'      => ActionInterface::TARGET_RANDOM_ENEMY,
                            'damage'           => 50,
                            'can_be_avoided'   => true,
                            'name'             => 'Demo Ability',
                            'animation_method' => 'damage',
                            'message_method'   => 'damageAbility',
                            'icon'             => 'icon.png',
                        ],
                    ],
                    'chance_activate' => 50,
                ],
                AbilityException::INVALID_DISPOSABLE_DATA,
            ],
            // Отсутствует type_activate
            [
                [
                    'name'            => 'Demo Ability',
                    'icon'            => 'icon.png',
                    'disposable'      => true,
                    'actions'         => [
                        [
                            'type'             => ActionInterface::DAMAGE,
                            'type_target'      => ActionInterface::TARGET_RANDOM_ENEMY,
                            'damage'           => 50,
                            'can_be_avoided'   => true,
                            'name'             => 'Demo Ability',
                            'animation_method' => 'damage',
                            'message_method'   => 'damageAbility',
                            'icon'             => 'icon.png',
                        ],
                    ],
                    'chance_activate' => 50,
                ],
                AbilityException::INVALID_TYPE_ACTIVATE_DATA,
            ],
            // type_activate некорректного типа
            [
                [
                    'name'            => 'Demo Ability',
                    'icon'            => 'icon.png',
                    'disposable'      => true,
                    'type_activate'   => 'AbilityInterface::ACTIVATE_CONCENTRATION',
                    'actions'         => [
                        [
                            'type'             => ActionInterface::DAMAGE,
                            'type_target'      => ActionInterface::TARGET_RANDOM_ENEMY,
                            'damage'           => 50,
                            'can_be_avoided'   => true,
                            'name'             => 'Demo Ability',
                            'animation_method' => 'damage',
                            'message_method'   => 'damageAbility',
                            'icon'             => 'icon.png',
                        ],
                    ],
                    'chance_activate' => 50,
                ],
                AbilityException::INVALID_TYPE_ACTIVATE_DATA,
            ],
            // type_activate неизвестного типа
            [
                [
                    'name'            => 'Demo Ability',
                    'icon'            => 'icon.png',
                    'disposable'      => true,
                    'type_activate'   => 8,
                    'actions'         => [
                        [
                            'type'             => ActionInterface::DAMAGE,
                            'type_target'      => ActionInterface::TARGET_RANDOM_ENEMY,
                            'damage'           => 50,
                            'can_be_avoided'   => true,
                            'name'             => 'Demo Ability',
                            'animation_method' => 'damage',
                            'message_method'   => 'damageAbility',
                            'icon'             => 'icon.png',
                        ],
                    ],
                    'chance_activate' => 50,
                ],
                AbilityException::UNKNOWN_ACTIVATE_TYPE,
            ],
            // chance_activate некорректного типа
            [
                [
                    'name'            => 'Demo Ability',
                    'icon'            => 'icon.png',
                    'disposable'      => true,
                    'type_activate'   => AbilityInterface::ACTIVATE_CONCENTRATION,
                    'actions'         => [
                        [
                            'type'             => ActionInterface::DAMAGE,
                            'type_target'      => ActionInterface::TARGET_RANDOM_ENEMY,
                            'damage'           => 50,
                            'can_be_avoided'   => true,
                            'name'             => 'Demo Ability',
                            'animation_method' => 'damage',
                            'message_method'   => 'damageAbility',
                            'icon'             => 'icon.png',
                        ],
                    ],
                    'chance_activate' => '50',
                ],
                AbilityException::INVALID_CHANCE_ACTIVATE_DATA,
            ],
            // Отсутствует actions
            [
                [
                    'name'            => 'Demo Ability',
                    'icon'            => 'icon.png',
                    'disposable'      => true,
                    'type_activate'   => AbilityInterface::ACTIVATE_CONCENTRATION,
                    'chance_activate' => 50,
                ],
                AbilityException::INVALID_ACTIONS_DATA,
            ],
            // actions некорректного типа
            [
                [
                    'name'            => 'Demo Ability',
                    'icon'            => 'icon.png',
                    'disposable'      => true,
                    'type_activate'   => AbilityInterface::ACTIVATE_CONCENTRATION,
                    'actions'         => '',
                    'chance_activate' => 50,
                ],
                AbilityException::INVALID_ACTIONS_DATA,
            ],
            // actions содержит не массивы
            [
                [
                    'name'            => 'Demo Ability',
                    'icon'            => 'icon.png',
                    'disposable'      => true,
                    'type_activate'   => AbilityInterface::ACTIVATE_CONCENTRATION,
                    'actions'         => [
                        'invalid_data',
                    ],
                    'chance_activate' => 50,
                ],
                AbilityException::INVALID_ACTION_DATA,
            ],
        ];
    }

    /**
     * @return AbilityFactory
     */
    private function getFactory(): AbilityFactory
    {
        return new AbilityFactory();
    }

    /**
     * @param UnitInterface $unit
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $alliesCommand
     * @param array $actionsData
     * @return ActionCollection
     * @throws Exception
     */
    private function createActionCollections(
        UnitInterface $unit,
        CommandInterface $enemyCommand,
        CommandInterface $alliesCommand,
        array $actionsData
    ): ActionCollection
    {
        $actionFactory = new ActionFactory();
        $collection = new ActionCollection();

        foreach ($actionsData as $actionData) {

            $actionData['action_unit'] = $unit;
            $actionData['enemy_command'] = $enemyCommand;
            $actionData['allies_command'] = $alliesCommand;

            $collection->add($actionFactory->create($actionData));
        }

        return $collection;
    }
}