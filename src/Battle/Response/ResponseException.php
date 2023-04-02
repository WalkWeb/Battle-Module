<?php

declare(strict_types=1);

namespace Battle\Response;

use Exception;

class ResponseException extends Exception
{
    public const INCORRECT_WINNER = 'Incorrect winner';
}
