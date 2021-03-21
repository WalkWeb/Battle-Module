<?php

declare(strict_types=1);

namespace Battle\Command;

use Exception;

class CommandException extends Exception
{
    public const INCORRECT_USER                  = 'User Incorrect';
    public const NO_UNITS                        = 'No Units';
    public const UNEXPECTED_EVENT_NO_ACTION_UNIT = 'Отсутствуют юниты для совершения хода, хотя они должны быть';
    public const INCORRECT_UNIT_DATA             = 'Incorrect unit data, excepted array';
    public const INCORRECT_OBJECT_UNIT           = 'Incorrect unit object, excepted array or UnitInterface implements object';
}
