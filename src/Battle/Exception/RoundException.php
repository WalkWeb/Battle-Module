<?php

declare(strict_types=1);

namespace Battle\Exception;

use Exception;

class RoundException extends Exception
{
    public const INCORRECT_START_COMMAND = 'Некорректное указание команды, начинающей раунд';
    public const UNEXPECTED_ENDING       = 'Неожиданное завершение раунда';
}
