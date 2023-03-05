<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Effect;

use Battle\Action\ActionInterface;
use Battle\Unit\Effect\EffectException;
use Exception;
use Tests\AbstractUnitTest;
use Tests\Factory\BaseFactory;

class EffectFactoryTest extends AbstractUnitTest
{
    /**
     * Тест на успешный вариант создания эффекта из массива параметров
     *
     * @dataProvider successDataProvider
     * @param array $data
     * @throws Exception
     */
    public function testEffectFactoryCreateSuccess(array $data): void
    {
        $effect = $this->getContainer()->getEffectFactory()->create($data);

        self::assertEquals($data['name'], $effect->getName());
        self::assertEquals($data['icon'], $effect->getIcon());
        self::assertEquals($data['duration'], $effect->getDuration());
        self::assertEquals($data['duration'], $effect->getBaseDuration());

        self::assertSameSize($data['on_apply_actions'], $effect->getOnApplyActions());
        self::assertSameSize($data['on_next_round_actions'], $effect->getOnNextRoundActions());

        // Получить коллекцию событий при завершении эффекта можно тогда, когда были применены события при применении
        foreach ($effect->getOnApplyActions() as $action) {
            self::assertTrue($action->canByUsed());
            $action->handle();
        }

        // Количество событий при откате = количество событий при применении (если это баффы) + другие события из on_disable_actions
        self::assertCount(
            count($data['on_disable_actions']) + count($data['on_apply_actions']),
            $effect->getOnDisableActions()
        );
    }

    /**
     * Тест на некорректные варианты данных для создания эффекта
     *
     * @dataProvider failDataProvider
     * @param array $data
     * @param string $error
     * @throws Exception
     */
    public function testEffectFactoryCreateFail(array $data, string $error): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage($error);
        $this->getContainer()->getEffectFactory()->create($data);
    }

    /**
     * @return array
     * @throws Exception
     */
    public function successDataProvider(): array
    {
        [$unit, $command, $enemyCommand] = BaseFactory::create(1, 2);

        return [
            [
                [
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
                        ]
                    ],
                    'on_next_round_actions' => [],
                    'on_disable_actions'    => [],
                ],
            ],
        ];
    }

    /**
     * @return array
     * @throws Exception
     */
    public function failDataProvider(): array
    {
        [$unit, $command, $enemyCommand] = BaseFactory::create(1, 2);

        return [
            [
                // Отсутствует name
                [
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
                        ]
                    ],
                    'on_next_round_actions' => [],
                    'on_disable_actions'    => [],
                ],
                EffectException::INVALID_NAME,
            ],
            [
                // name некорректного типа
                [
                    'name'                  => 123,
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
                        ]
                    ],
                    'on_next_round_actions' => [],
                    'on_disable_actions'    => [],
                ],
                EffectException::INVALID_NAME,
            ],
            [
                // Отсутствует icon
                [
                    'name'                  => 'Effect test #1',
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
                        ]
                    ],
                    'on_next_round_actions' => [],
                    'on_disable_actions'    => [],
                ],
                EffectException::INVALID_ICON,
            ],
            [
                // icon некорректного типа
                [
                    'name'                  => 'Effect test #1',
                    'icon'                  => true,
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
                        ]
                    ],
                    'on_next_round_actions' => [],
                    'on_disable_actions'    => [],
                ],
                EffectException::INVALID_ICON,
            ],
            [
                // Отсутствует duration
                [
                    'name'                  => 'Effect test #1',
                    'icon'                  => 'effect_icon_#1',
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
                        ]
                    ],
                    'on_next_round_actions' => [],
                    'on_disable_actions'    => [],
                ],
                EffectException::INVALID_DURATION,
            ],
            [
                // duration некорректного типа
                [
                    'name'                  => 'Effect test #1',
                    'icon'                  => 'effect_icon_#1',
                    'duration'              => '10',
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
                        ]
                    ],
                    'on_next_round_actions' => [],
                    'on_disable_actions'    => [],
                ],
                EffectException::INVALID_DURATION,
            ],
            [
                // Отсутствует on_apply_actions
                [
                    'name'                  => 'Effect test #1',
                    'icon'                  => 'effect_icon_#1',
                    'duration'              => 10,
                    'on_next_round_actions' => [],
                    'on_disable_actions'    => [],
                ],
                EffectException::INVALID_ON_APPLY,
            ],
            [
                // on_apply_actions некорректного типа
                [
                    'name'                  => 'Effect test #1',
                    'icon'                  => 'effect_icon_#1',
                    'duration'              => 10,
                    'on_apply_actions'      => '',
                    'on_next_round_actions' => [],
                    'on_disable_actions'    => [],
                ],
                EffectException::INVALID_ON_APPLY,
            ],
            [
                // Отсутствует on_next_round_actions
                [
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
                        ]
                    ],
                    'on_disable_actions'    => [],
                ],
                EffectException::INVALID_ON_NEXT_ROUND,
            ],
            [
                // on_next_round_actions некорректного типа
                [
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
                        ]
                    ],
                    'on_next_round_actions' => '[]',
                    'on_disable_actions'    => [],
                ],
                EffectException::INVALID_ON_NEXT_ROUND,
            ],
            [
                // Отсутствует on_disable_actions
                [
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
                        ]
                    ],
                    'on_next_round_actions' => [],
                ],
                EffectException::INVALID_ON_DISABLE,
            ],
            [
                // on_disable_actions некорректного типа
                [
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
                        ]
                    ],
                    'on_next_round_actions' => [],
                    'on_disable_actions'    => 12,
                ],
                EffectException::INVALID_ON_DISABLE,
            ],
            [
                // эффект без эффектов
                [
                    'name'                  => 'Effect test #1',
                    'icon'                  => 'effect_icon_#1',
                    'duration'              => 10,
                    'on_apply_actions'      => [],
                    'on_next_round_actions' => [],
                    'on_disable_actions'    => [],
                ],
                EffectException::ZERO_ACTION,
            ],
            [
                // on_disable_actions имеет некорректный формат данных по эффекту
                [
                    'name'                  => 'Effect test #1',
                    'icon'                  => 'effect_icon_#1',
                    'duration'              => 10,
                    'on_apply_actions'      => [
                        'effect data',
                    ],
                    'on_next_round_actions' => [],
                    'on_disable_actions'    => [],
                ],
                EffectException::INVALID_ACTION_DATA,
            ],
        ];
    }
}
