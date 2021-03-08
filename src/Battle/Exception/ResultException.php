<?php

declare(strict_types=1);

namespace Battle\Exception;

use Exception;

class ResultException extends Exception
{
    public const INCORRECT_WINNER = 'Incorrect winner';
}
