<?php

declare(strict_types=1);

namespace Battle\Unit\Ability\DataProvider;

use Battle\Action\ActionInterface;
use Battle\Action\DamageAction;
use Battle\Action\HealAction;
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
        'Heavy Strike'   => [
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
        'Hellfire'       => [
            1 => [
                'name'          => 'Hellfire',
                'icon'          => '/images/icons/ability/276.png',
                'disposable'    => false,
                'type_activate' => AbilityInterface::ACTIVATE_RAGE,
                'actions'       => [
                    [
                        'type'             => ActionInterface::DAMAGE,
                        'type_target'      => ActionInterface::TARGET_ALL_ENEMY,
                        'damage'           => 30,
                        'can_be_avoided'   => true,
                        'name'             => 'Hellfire',
                        'animation_method' => 'damage',
                        'message_method'   => 'damageAbility',
                        'icon'             => '/images/icons/ability/276.png',
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
        'Battle Fury'    => [
            1 => [
                'name'          => 'Battle Fury',
                'icon'          => '/images/icons/ability/102.png',
                'disposable'    => false,
                'type_activate' => AbilityInterface::ACTIVATE_RAGE,
                'actions'       => [
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
                            'on_apply_actions'      => [
                                [
                                    'type'           => ActionInterface::BUFF,
                                    'type_target'    => ActionInterface::TARGET_SELF,
                                    'name'           => 'Battle Fury',
                                    'modify_method'  => 'multiplierAttackSpeed',
                                    'power'          => 140,
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
        'Healing Potion' => [
            1 => [
                'name'          => 'Healing Potion',
                'icon'          => '/images/icons/ability/234.png',
                'disposable'    => false,
                'type_activate' => AbilityInterface::ACTIVATE_CONCENTRATION,
                'actions'       => [
                    [
                        'type'           => ActionInterface::EFFECT,
                        'type_target'    => ActionInterface::TARGET_WOUNDED_ALLIES_EFFECT,
                        'name'           => 'Healing Potion',
                        'icon'           => '/images/icons/ability/234.png',
                        'message_method' => 'applyEffect',
                        'effect'         => [
                            'name'                  => 'Healing Potion',
                            'icon'                  => '/images/icons/ability/234.png',
                            'duration'              => 4,
                            'on_apply_actions'      => [],
                            'on_next_round_actions' => [
                                [
                                    'type'             => ActionInterface::HEAL,
                                    'type_target'      => ActionInterface::TARGET_SELF,
                                    'name'             => 'Healing Potion',
                                    'power'            => 15,
                                    'animation_method' => HealAction::EFFECT_ANIMATION_METHOD,
                                    'message_method'   => HealAction::EFFECT_MESSAGE_METHOD,
                                    'icon'             => '/images/icons/ability/234.png',
                                ],
                            ],
                            'on_disable_actions'    => [],
                        ],
                    ],
                ],
            ],
        ],
        'Incineration'   => [
            1 => [
                'name'          => 'Incineration',
                'icon'          => '/images/icons/ability/232.png',
                'disposable'    => false,
                'type_activate' => AbilityInterface::ACTIVATE_CONCENTRATION,
                'actions'       => [
                    [
                        'type'           => ActionInterface::EFFECT,
                        'type_target'    => ActionInterface::TARGET_ALL_ENEMY,
                        'name'           => 'Incineration',
                        'icon'           => '/images/icons/ability/232.png',
                        'message_method' => 'applyEffect',
                        'effect'         => [
                            'name'                  => 'Incineration',
                            'icon'                  => '/images/icons/ability/232.png',
                            'duration'              => 8,
                            'on_apply_actions'      => [],
                            'on_next_round_actions' => [
                                [
                                    'type'             => ActionInterface::DAMAGE,
                                    'type_target'      => ActionInterface::TARGET_SELF,
                                    'name'             => 'Incineration',
                                    'damage'           => 6,
                                    'can_be_avoided'   => false,
                                    'animation_method' => DamageAction::EFFECT_ANIMATION_METHOD,
                                    'message_method'   => DamageAction::EFFECT_MESSAGE_METHOD,
                                    'icon'             => '/images/icons/ability/232.png',
                                ],
                            ],
                            'on_disable_actions'    => [],
                        ],
                    ],
                ],
            ],
        ],
        'Paralysis'      => [
            1 => [
                'name'          => 'Paralysis',
                'icon'          => '/images/icons/ability/086.png',
                'disposable'    => false,
                'type_activate' => AbilityInterface::ACTIVATE_RAGE,
                'actions'       => [
                    [
                        'type'           => ActionInterface::EFFECT,
                        'type_target'    => ActionInterface::TARGET_RANDOM_ENEMY,
                        'name'           => 'Paralysis',
                        'icon'           => '/images/icons/ability/086.png',
                        'message_method' => 'applyEffect',
                        'effect'         => [
                            'name'                  => 'Paralysis',
                            'icon'                  => '/images/icons/ability/086.png',
                            'duration'              => 2,
                            'on_apply_actions'      => [],
                            'on_next_round_actions' => [
                                [
                                    'type'             => ActionInterface::PARALYSIS,
                                    'type_target'      => ActionInterface::TARGET_SELF,
                                    'name'             => 'Paralysis',
                                    'can_be_avoided'   => false,
                                    'animation_method' => DamageAction::EFFECT_ANIMATION_METHOD,
                                    'message_method'   => DamageAction::EFFECT_MESSAGE_METHOD,
                                    'icon'             => '/images/icons/ability/086.png',
                                ],
                            ],
                            'on_disable_actions'    => [],
                        ],
                    ],
                ],
            ],
        ],
        'Poison'         => [
            1 => [
                'name'          => 'Poison',
                'icon'          => '/images/icons/ability/202.png',
                'disposable'    => false,
                'type_activate' => AbilityInterface::ACTIVATE_CONCENTRATION,
                'actions'       => [
                    [
                        'type'           => ActionInterface::EFFECT,
                        'type_target'    => ActionInterface::TARGET_EFFECT_ENEMY,
                        'name'           => 'Poison',
                        'icon'           => '/images/icons/ability/202.png',
                        'message_method' => 'applyEffect',
                        'effect'         => [
                            'name'                  => 'Poison',
                            'icon'                  => '/images/icons/ability/202.png',
                            'duration'              => 5,
                            'on_apply_actions'      => [],
                            'on_next_round_actions' => [
                                [
                                    'type'             => ActionInterface::DAMAGE,
                                    'type_target'      => ActionInterface::TARGET_SELF,
                                    'name'             => 'Poison',
                                    'damage'           => 8,
                                    'can_be_avoided'   => false,
                                    'animation_method' => DamageAction::EFFECT_ANIMATION_METHOD,
                                    'message_method'   => DamageAction::EFFECT_MESSAGE_METHOD,
                                    'icon'             => '/images/icons/ability/202.png',
                                ],
                            ],
                            'on_disable_actions'    => [],
                        ],
                    ],
                ],
            ],
        ],
        'Rage'           => [
            1 => [
                'name'          => 'Rage',
                'icon'          => '/images/icons/ability/285.png',
                'disposable'    => true,
                'type_activate' => AbilityInterface::ACTIVATE_LOW_LIFE,
                'actions'       => [
                    [
                        'type'           => ActionInterface::EFFECT,
                        'type_target'    => ActionInterface::TARGET_SELF,
                        'name'           => 'Rage',
                        'icon'           => '/images/icons/ability/285.png',
                        'message_method' => 'applyEffect',
                        'effect'         => [
                            'name'                  => 'Rage',
                            'icon'                  => '/images/icons/ability/285.png',
                            'duration'              => 8,
                            'on_apply_actions'      => [
                                [
                                    'type'           => ActionInterface::BUFF,
                                    'type_target'    => ActionInterface::TARGET_SELF,
                                    'name'           => 'Rage',
                                    'modify_method'  => 'multiplierDamage',
                                    'power'          => 200,
                                    'icon'           => '/images/icons/ability/285.png',
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
        'Reserve Forces' => [
            1 => [
                'name'          => 'Reserve Forces',
                'icon'          => '/images/icons/ability/156.png',
                'disposable'    => false,
                'type_activate' => AbilityInterface::ACTIVATE_CONCENTRATION,
                'actions'       => [
                    [
                        'type'           => ActionInterface::EFFECT,
                        'type_target'    => ActionInterface::TARGET_SELF,
                        'name'           => 'Reserve Forces',
                        'icon'           => '/images/icons/ability/156.png',
                        'message_method' => 'applyEffect',
                        'effect'         => [
                            'name'                  => 'Reserve Forces',
                            'icon'                  => '/images/icons/ability/156.png',
                            'duration'              => 6,
                            'on_apply_actions'      => [
                                [
                                    'type'           => ActionInterface::BUFF,
                                    'type_target'    => ActionInterface::TARGET_SELF,
                                    'name'           => 'Reserve Forces',
                                    'modify_method'  => 'multiplierMaxLife',
                                    'power'          => 130,
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
        'General Heal'   => [
            1 => [
                'name'          => 'General Heal',
                'icon'          => '/images/icons/ability/452.png',
                'disposable'    => false,
                'type_activate' => AbilityInterface::ACTIVATE_RAGE,
                'actions'       => [
                    [
                        'type'             => ActionInterface::HEAL,
                        'type_target'      => ActionInterface::TARGET_ALL_WOUNDED_ALLIES,
                        'power'            => 24,
                        'can_be_avoided'   => true,
                        'name'             => 'General Heal',
                        'animation_method' => 'heal',
                        'message_method'   => 'healAbility',
                        'icon'             => '/images/icons/ability/452.png',
                    ],
                ],
            ],
        ],
        'Great Heal'     => [
            1 => [
                'name'          => 'Great Heal',
                'icon'          => '/images/icons/ability/196.png',
                'disposable'    => false,
                'type_activate' => AbilityInterface::ACTIVATE_CONCENTRATION,
                'actions'       => [
                    [
                        'type'             => ActionInterface::HEAL,
                        'type_target'      => ActionInterface::TARGET_ALL_WOUNDED_ALLIES,
                        'power'            => 60,
                        'can_be_avoided'   => true,
                        'name'             => 'Great Heal',
                        'animation_method' => 'heal',
                        'message_method'   => 'healAbility',
                        'icon'             => '/images/icons/ability/196.png',
                    ],
                ],
            ],
        ],
        'Back to Life'   => [
            1 => [
                'name'          => 'Back to Life',
                'icon'          => '/images/icons/ability/053.png',
                'disposable'    => false,
                'type_activate' => AbilityInterface::ACTIVATE_RAGE,
                'actions'       => [
                    [
                        'type'        => ActionInterface::RESURRECTION,
                        'type_target' => ActionInterface::TARGET_DEAD_ALLIES,
                        'power'       => 30,
                        'name'        => 'Back to Life',
                        'icon'        => '/images/icons/ability/053.png',
                    ],
                ],
            ],
        ],
        'Will to live'   => [
            1 => [
                'name'          => 'Will to live',
                'icon'          => '/images/icons/ability/429.png',
                'disposable'    => true,
                'type_activate' => AbilityInterface::ACTIVATE_DEAD,
                'actions'       => [
                    [
                        'type'           => ActionInterface::RESURRECTION,
                        'type_target'    => ActionInterface::TARGET_SELF,
                        'power'          => 50,
                        'name'           => 'Will to live',
                        'icon'           => '/images/icons/ability/429.png',
                        'message_method' => 'selfRaceResurrected',
                    ],
                ],
            ],
        ],
        'Fire Elemental' => [
            1 => [
                'name'          => 'Fire Elemental',
                'icon'          => '/images/icons/ability/198.png',
                'disposable'    => false,
                'type_activate' => AbilityInterface::ACTIVATE_RAGE,
                'actions'       => [
                    [
                        'type'   => ActionInterface::SUMMON,
                        'name'   => 'Fire Elemental',
                        'icon'   => '/images/icons/ability/198.png',
                        'summon' => [
                            'name'       => 'Fire Elemental',
                            'level'      => 3,
                            'avatar'     => '/images/avas/summon/fire-elemental.png',
                            'life'       => 62,
                            'total_life' => 62,
                            'mana'       => 17,
                            'total_mana' => 17,
                            'melee'      => true,
                            'class'      => null,
                            'race'       => 10,
                            'offense'    => [
                                'damage'       => 17,
                                'attack_speed' => 1.1,
                                'accuracy'     => 200,
                                'block_ignore' => 0,
                            ],
                            'defense'    => [
                                'defense'        => 100,
                                'magic_defense'  => 50,
                                'block'          => 0,
                                'mental_barrier' => 0,
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'Imp'            => [
            1 => [
                'name'          => 'Imp',
                'icon'          => '/images/icons/ability/275.png',
                'disposable'    => false,
                'type_activate' => AbilityInterface::ACTIVATE_CONCENTRATION,
                'actions'       => [
                    [
                        'type'   => ActionInterface::SUMMON,
                        'name'   => 'Imp',
                        'icon'   => '/images/icons/ability/275.png',
                        'summon' => [
                            'name'       => 'Imp',
                            'level'      => 1,
                            'avatar'     => '/images/avas/monsters/004.png',
                            'life'       => 30,
                            'total_life' => 30,
                            'mana'       => 0,
                            'total_mana' => 0,
                            'melee'      => true,
                            'class'      => null,
                            'race'       => 9,
                            'offense'    => [
                                'damage'       => 10,
                                'attack_speed' => 1,
                                'accuracy'     => 200,
                                'block_ignore' => 0,
                            ],
                            'defense'    => [
                                'defense'        => 100,
                                'magic_defense'  => 50,
                                'block'          => 0,
                                'mental_barrier' => 0,
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'Skeleton'       => [
            1 => [
                'name'          => 'Skeleton',
                'icon'          => '/images/icons/ability/338.png',
                'disposable'    => false,
                'type_activate' => AbilityInterface::ACTIVATE_CONCENTRATION,
                'actions'       => [
                    [
                        'type'   => ActionInterface::SUMMON,
                        'name'   => 'Skeleton',
                        'icon'   => '/images/icons/ability/338.png',
                        'summon' => [
                            'name'       => 'Skeleton',
                            'level'      => 1,
                            'avatar'     => '/images/avas/monsters/003.png',
                            'life'       => 38,
                            'total_life' => 38,
                            'mana'       => 0,
                            'total_mana' => 0,
                            'melee'      => true,
                            'class'      => null,
                            'race'       => 8,
                            'offense'    => [
                                'damage'       => 16,
                                'attack_speed' => 1,
                                'accuracy'     => 200,
                                'block_ignore' => 0,
                            ],
                            'defense'    => [
                                'defense'        => 100,
                                'magic_defense'  => 50,
                                'block'          => 0,
                                'mental_barrier' => 0,
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'Skeleton Mage'  => [
            1 => [
                'name'          => 'Skeleton Mage',
                'icon'          => '/images/icons/ability/503.png',
                'disposable'    => false,
                'type_activate' => AbilityInterface::ACTIVATE_CONCENTRATION,
                'actions'       => [
                    [
                        'type'   => ActionInterface::SUMMON,
                        'name'   => 'Skeleton Mage',
                        'icon'   => '/images/icons/ability/503.png',
                        'summon' => [
                            'name'       => 'Skeleton Mage',
                            'level'      => 2,
                            'avatar'     => '/images/avas/monsters/008.png',
                            'life'       => 42,
                            'total_life' => 42,
                            'mana'       => 115,
                            'total_mana' => 115,
                            'melee'      => true,
                            'class'      => null,
                            'race'       => 8,
                            'offense'    => [
                                'damage'       => 13,
                                'attack_speed' => 1.2,
                                'accuracy'     => 200,
                                'block_ignore' => 0,
                            ],
                            'defense'    => [
                                'defense'        => 100,
                                'magic_defense'  => 150,
                                'block'          => 0,
                                'mental_barrier' => 0,
                            ],
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