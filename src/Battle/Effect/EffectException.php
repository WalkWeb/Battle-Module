<?php

declare(strict_types=1);

namespace Battle\Effect;

use Exception;

class EffectException extends Exception
{
    public const NO_EFFECT = 'No effect';
    public const INVALID_CHANGES_DATA = 'Invalid changes data';
    public const INVALID_EFFECT_DATA = 'Invalid effect data';
}
