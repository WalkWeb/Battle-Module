<?php

declare(strict_types=1);

namespace Battle\Exception;

use Exception;

class BattleException extends Exception
{
    public const UNEXPECTED_ENDING_BATTLE = 'Превышен лимит раундов';
    public const INCORRECT_UNIT_DATA      = 'Переданы некорректные данные по юнитам. Ожидается массив параметров';
    public const NO_COMMAND_PARAMETER     = 'Отсутствует параметр, указывающий на принадлежность юнита к какой-либо команде';
}
