<?php

declare(strict_types=1);

namespace Battle\Unit\Effect;

use Exception;

class EffectException extends Exception
{
    public const string INVALID_NAME          = 'EffectFactory: invalid "name" parameter, string excepted';
    public const string INVALID_ICON          = 'EffectFactory: invalid "icon" parameter, string excepted';
    public const string INVALID_DURATION      = 'EffectFactory: invalid "duration" parameter, int excepted';
    public const string INVALID_ON_APPLY      = 'EffectFactory: invalid "on_apply_actions" parameter, array excepted';
    public const string INVALID_ON_NEXT_ROUND = 'EffectFactory: invalid "on_next_round_actions" parameter, array excepted';
    public const string INVALID_ON_DISABLE    = 'EffectFactory: invalid "on_disable_actions" parameter, array excepted';
    public const string INVALID_ACTION_DATA   = 'EffectFactory: invalid "action" data, array excepted';
    public const string ZERO_ACTION           = 'EffectFactory: Effect have zero actions';
}
