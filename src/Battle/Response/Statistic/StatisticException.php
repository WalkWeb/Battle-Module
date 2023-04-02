<?php

declare(strict_types=1);

namespace Battle\Response\Statistic;

use Exception;

class StatisticException extends Exception
{
    public const NO_UNIT   = 'Undefined unit';
    public const DOUBLE_ID = 'Double UnitStatistic ID';
}
