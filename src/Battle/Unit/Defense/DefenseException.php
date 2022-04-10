<?php

declare(strict_types=1);

namespace Battle\Unit\Defense;

use Exception;

class DefenseException extends Exception
{
    public const INCORRECT_DEFENSE       = 'Incorrect defense, it required and type int';
    public const INCORRECT_DEFENSE_VALUE = 'Incorrect defense, should be min value: ';
    public const INCORRECT_BLOCK         = 'Incorrect block, it required and type int';
    public const INCORRECT_BLOCK_VALUE   = 'Incorrect block, should be min-max value: ';
}
