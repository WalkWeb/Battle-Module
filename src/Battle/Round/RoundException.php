<?php

declare(strict_types=1);

namespace Battle\Round;

use Exception;

class RoundException extends Exception
{
    public const string INCORRECT_START_COMMAND = 'Некорректное указание команды, начинающей раунд';
    public const string UNEXPECTED_ENDING       = 'Неожиданное завершение раунда';
}
