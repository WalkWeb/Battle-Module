<?php

declare(strict_types=1);

namespace Tests\Battle\Factory;

use Exception;

class UnitFactoryException extends Exception
{
    public const NO_TEMPLATE = 'Unknown Unit Template';
}
