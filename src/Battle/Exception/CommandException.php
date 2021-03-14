<?php

declare(strict_types=1);

namespace Battle\Exception;

use Exception;

class CommandException extends Exception
{
    public const INCORRECT_USER                  = 'User Incorrect';
    public const NO_UNITS                        = 'No Units';
    public const UNEXPECTED_EVENT_NO_ACTION_UNIT = 'Отсутствуют юниты для совершения хода, хотя они должны быть';
}
