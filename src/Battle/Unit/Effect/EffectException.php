<?php

declare(strict_types=1);

namespace Battle\Unit\Effect;

use Exception;

class EffectException extends Exception
{
    public const INVALID_NAME          = 'EffectFactory: invalid name parameter, string excepted';
    public const INVALID_ICON          = 'EffectFactory: invalid icon parameter, string excepted';
    public const INVALID_DURATION      = 'EffectFactory: invalid duration parameter, int excepted';
    public const INVALID_ON_APPLY      = 'EffectFactory: invalid on_apply_actions parameter, array excepted';
    public const INVALID_ON_NEXT_ROUND = 'EffectFactory: invalid on_next_round_actions parameter, array excepted';
    public const INVALID_ON_DISABLE    = 'EffectFactory: invalid on_disable_actions parameter, array excepted';
    public const INVALID_ACTION_DATA   = 'EffectFactory: invalid action data, array excepted';
    public const ZERO_ACTION           = 'EffectFactory: Effect have zero actions';
}
