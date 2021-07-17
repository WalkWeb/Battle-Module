<?php

declare(strict_types=1);

namespace Battle\Stroke;

use Exception;

class StrokeException extends Exception
{
    public const CANT_BE_USED_ACTION   = "Can't be used action";
}
