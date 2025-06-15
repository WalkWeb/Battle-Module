<?php

declare(strict_types=1);

namespace Battle\Command;

use Exception;

class CommandException extends Exception
{
    public const string NO_UNITS                        = 'No Units';
    public const string UNEXPECTED_EVENT_NO_ACTION_UNIT = 'Отсутствуют юниты для совершения хода, хотя они должны быть';
    public const string INCORRECT_UNIT_DATA             = 'Incorrect unit data, excepted array';
    public const string INCORRECT_OBJECT_UNIT           = 'Incorrect unit object, excepted array or UnitInterface implements object';
}
