<?php

declare(strict_types=1);

namespace Battle;

use Exception;

class BattleException extends Exception
{
    public const string INCORRECT_UNIT_DATA      = 'Переданы некорректные данные по юнитам. Ожидается массив параметров';
    public const string NO_COMMAND_PARAMETER     = 'Отсутствует параметр, указывающий на принадлежность юнита к какой-либо команде';
    public const string DOUBLE_UNIT_ID           = 'Найден повторяющийся ID юнита';
    public const string INCORRECT_START_COMMAND  = 'Некорректное указание команды, начинающей раунд';
}
