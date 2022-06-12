<?php

declare(strict_types=1);

namespace Battle\Unit\Ability;

use Exception;

class AbilityException extends Exception
{
    public const INVALID_ACTION_DATA           = 'Invalid action data: array expected';
    public const INVALID_EFFECT_DATA           = 'Invalid effect action data: expected array parameters: "on_apply_actions", "on_next_round_actions" and "on_disable_actions"';
    public const INVALID_ACTIONS_DATA          = 'Invalid "actions" data: array expected';
    public const INVALID_NAME_DATA             = 'Invalid "name" data: string excepted';
    public const INVALID_ICON_DATA             = 'Invalid "icon" data: string excepted';
    public const INVALID_DISPOSABLE_DATA       = 'Invalid "disposable" data: bool excepted';
    public const INVALID_TYPE_ACTIVATE_DATA    = 'Invalid "type_activate" data: int excepted';
    public const UNKNOWN_ACTIVATE_TYPE         = 'Unknown activate type';
    public const INVALID_CHANCE_ACTIVATE_DATA  = 'Invalid "chance_activate" data: absence or int excepted';
}
