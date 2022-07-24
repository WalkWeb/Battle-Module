<?php

declare(strict_types=1);

namespace Battle\Unit\Offense;

use Exception;

class OffenseException extends Exception
{
    public const INCORRECT_DAMAGE               = 'Incorrect damage, it required and type int';
    public const INCORRECT_DAMAGE_VALUE         = 'Incorrect damage, should be min-max value: ';
    public const INCORRECT_ACCURACY             = 'Incorrect accuracy, it required and type int';
    public const INCORRECT_ACCURACY_VALUE       = 'Incorrect accuracy, should be min value: ';
    public const INCORRECT_MAGIC_ACCURACY       = 'Incorrect magic_accuracy, it required and type int';
    public const INCORRECT_MAGIC_ACCURACY_VALUE = 'Incorrect magic_accuracy, should be min value: ';
    public const INCORRECT_ATTACK_SPEED         = 'Incorrect attack speed, it required and type float or int';
    public const INCORRECT_ATTACK_SPEED_VALUE   = 'Incorrect attack speed, should be min-max value: ';
    public const INCORRECT_BLOCK_IGNORE         = 'Incorrect block_ignore, it required and type int';
    public const INCORRECT_BLOCK_IGNORE_VALUE   = 'Incorrect block_ignore, should be min-max value: ';
}
