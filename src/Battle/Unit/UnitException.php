<?php

declare(strict_types=1);

namespace Battle\Unit;

use Exception;

class UnitException extends Exception
{
    public const UNDEFINED_ACTION_METHOD = 'Undefined action';
    public const INCORRECT_ID            = 'Incorrect parameter id, it required and type string';
    public const INCORRECT_NAME          = 'Incorrect parameter name, it required and type string';
    public const INCORRECT_AVATAR        = 'Incorrect parameter avatar, it required and type string';
    public const INCORRECT_DAMAGE        = 'Incorrect damage, it required and type  int';
    public const INCORRECT_ATTACK_SPEED  = 'Incorrect attack speed, it required and type float or int';
    public const INCORRECT_LIFE          = 'Incorrect life, it required and type  int';
    public const INCORRECT_MELEE         = 'Incorrect melee, it required and type  bool';
    public const INCORRECT_CLASS         = 'Incorrect class, it required and type  int';
}
