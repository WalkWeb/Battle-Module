<?php

declare(strict_types=1);

namespace Battle\Unit\Ability;

use Exception;

class AbilityException extends Exception
{
    public const INVALID_ACTION_DATA = 'Invalid action data: array expected';
    public const INVALID_EFFECT_DATA = 'Invalid effect action data: expected array parameters: "on_apply_actions", "on_next_round_actions" and "on_disable_actions"';
}
