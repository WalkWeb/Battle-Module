<?php

declare(strict_types=1);

namespace Battle\Exception;

use Exception;

class ActionCollectionException extends Exception
{
    public const INCORRECT_ACTION = 'INCORRECT_ACTION';
}
