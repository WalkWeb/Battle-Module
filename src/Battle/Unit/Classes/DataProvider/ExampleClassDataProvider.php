<?php

declare(strict_types=1);

namespace Battle\Unit\Classes\DataProvider;

use Battle\Action\ActionInterface;
use Battle\Unit\Ability\AbilityInterface;
use Battle\Unit\Classes\UnitClassException;

/**
 * Пример простого поставщика данных по классу юнита, когда данные хранятся в самом классе. Сделан для примера - при
 * большом количестве классов редактировать его будет неудобно, плюс будет съедать много памяти. Лучше хранить данные в
 * базе, а поставщик в этом случае будет делать простой SELECT в базу и все.
 *
 * При этом можно сделать веб-интерфейс (в админ-панели), через который параметры классов можно будет изменять сразу в
 * браузере.
 *
 * TODO Сейчас класс хранит в себе и все данные о себе и своих способностях, в будущем будет отдельный поставщик данных
 * TODO по способностям, а в классе будет храниться только названия способностей и их уровни.
 *
 * @package Battle\Unit\Classes\DataProvider
 */
class ExampleClassDataProvider implements ClassDataProviderInterface
{
    private static $data = [
        1 => [
            'id'         => 1,
            'name'       => 'Warrior',
            'small_icon' => '/images/icons/small/warrior.png',
            'abilities'  => [
                [
                    'name'          => 'Heavy Strike',
                    'icon'          => '/images/icons/ability/335.png',
                    'disposable'    => false,
                    'type_activate' => AbilityInterface::ACTIVATE_CONCENTRATION,
                    'actions'       => [
                        [
                            'type'             => ActionInterface::DAMAGE,
                            'type_target'      => ActionInterface::TARGET_RANDOM_ENEMY,
                            'damage'           => 50,
                            'can_be_avoided'   => true,
                            'name'             => 'Heavy Strike',
                            'animation_method' => 'damage',
                            'message_method'   => 'damageAbility',
                            'icon'             => '/images/icons/ability/335.png',
                        ],
                    ],
                ],
                [
                    'name'          => 'Blessed Shield',
                    'icon'          => '/images/icons/ability/271.png',
                    'disposable'    => false,
                    'type_activate' => AbilityInterface::ACTIVATE_CONCENTRATION,
                    'actions'       => [
                        [
                            'type'           => ActionInterface::EFFECT,
                            'type_target'    => ActionInterface::TARGET_SELF,
                            'name'           => 'Blessed Shield',
                            'icon'           => '/images/icons/ability/271.png',
                            'message_method' => 'applyEffect',
                            'effect'         => [
                                'name'                  => 'Blessed Shield',
                                'icon'                  => '/images/icons/ability/271.png',
                                'duration'              => 6,
                                'on_apply_actions'      => [
                                    [
                                        'type'           => ActionInterface::BUFF,
                                        'type_target'    => ActionInterface::TARGET_SELF,
                                        'name'           => 'Blessed Shield',
                                        'modify_method'  => 'addBlock',
                                        'power'          => 15,
                                        'message_method' => ActionInterface::SKIP_MESSAGE_METHOD,
                                    ],
                                ],
                                'on_next_round_actions' => [],
                                'on_disable_actions'    => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ];

    /**
     * @param int $id
     * @return array
     * @throws UnitClassException
     */
    public function get(int $id): array
    {
        if (!array_key_exists($id, self::$data)) {
            throw new UnitClassException(UnitClassException::UNDEFINED_CLASS_ID . ': ' . $id);
        }

        return self::$data[$id];
    }
}
