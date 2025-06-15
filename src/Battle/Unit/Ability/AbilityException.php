<?php

declare(strict_types=1);

namespace Battle\Unit\Ability;

use Exception;

class AbilityException extends Exception
{
    public const string UNDEFINED_ABILITY_NAME        = 'Undefined ability name';
    public const string UNDEFINED_ABILITY_LEVEL       = 'Undefined ability level';
    public const string INVALID_ACTION_DATA           = 'Invalid action data: array expected';
    public const string INVALID_EFFECT_DATA           = 'Invalid effect action data: expected array parameters: "on_apply_actions", "on_next_round_actions" and "on_disable_actions"';
    public const string INVALID_ACTIONS_DATA          = 'Invalid "actions" data: array expected';
    public const string INVALID_NAME_DATA             = 'Invalid "name" data: string expected';
    public const string INVALID_ICON_DATA             = 'Invalid "icon" data: string expected';
    public const string INVALID_DISPOSABLE_DATA       = 'Invalid "disposable" data: bool expected';
    public const string INVALID_TYPE_ACTIVATE_DATA    = 'Invalid "type_activate" data: int expected';
    public const string UNKNOWN_ACTIVATE_TYPE         = 'Unknown activate type';
    public const string INVALID_CHANCE_ACTIVATE_DATA  = 'Invalid "chance_activate" data: absence or int expected';
    public const string INVALID_ALLOWED_WEAPON_DATA   = 'Invalid "allowed_weapon_types" data: absence or int[] expected';
    public const string INVALID_VALUE_DATA            = 'AbilityDescriptionFactory: invalid value data: expected numeric[]';
    public const string INVALID_VALUES_DATA           = 'AbilityDescriptionFactory: invalid values data: expected array';
    public const string INVALID_DESCRIPTION_DATA      = 'AbilityDescriptionFactory: invalid description: string expected';
}
