<?php

declare(strict_types=1);

namespace Battle\Unit\Ability\DataProvider;

use Battle\Action\ActionInterface;
use Battle\Unit\Ability\AbilityException;
use Battle\Unit\Ability\AbilityInterface;

/**
 * Пример простого поставщика данных по способностям, когда данные хранятся в самом классе. Сделан для примера - при
 * большом количестве способностей редактировать его будет неудобно, плюс будет съедать много памяти. Лучше хранить
 * данные в базе, а поставщик в этом случае будет делать простой SELECT в базу и все.
 *
 * При этом можно сделать веб-интерфейс (в админ-панели), через который параметры способностей можно будет изменять
 * сразу в браузере.
 *
 * @package Battle\Unit\Ability\DataProvider
 */
class ExampleAbilityDataProvider implements AbilityDataProviderInterface
{
    private static $data = [
        'Heavy Strike' => [
            1 => [
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
        ],
        'Blessed Shield' => [
            1 => [
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
    ];

    /**
     * @param string $abilityName
     * @param int $level
     * @return mixed|void
     * @throws AbilityException
     */
    public function get(string $abilityName, int $level)
    {
        if (!array_key_exists($abilityName, self::$data)) {
            throw new AbilityException(AbilityException::UNDEFINED_ABILITY_NAME . ': ' . $abilityName);
        }

        if (!array_key_exists($level, self::$data[$abilityName])) {
            throw new AbilityException(AbilityException::UNDEFINED_ABILITY_LEVEL . ': ' . $level);
        }

        return self::$data[$abilityName][$level];
    }
}
