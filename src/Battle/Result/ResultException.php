<?php

declare(strict_types=1);

namespace Battle\Result;

use Exception;

class ResultException extends Exception
{
    public const INCORRECT_WINNER = 'Incorrect winner';
}
