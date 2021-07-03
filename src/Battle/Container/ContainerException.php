<?php

declare(strict_types=1);

namespace Battle\Container;

use Exception;

class ContainerException extends Exception
{
    public const UNKNOWN_SERVICE = 'Unknown service';
}
