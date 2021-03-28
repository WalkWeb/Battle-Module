<?php

declare(strict_types=1);

namespace Battle\Classes;

use Exception;

class ClassFactoryException extends Exception
{
    public const UNDEFINED_CLASS_ID = 'Undefined class ID';
    public const INCORRECT_CLASS    = 'Object no implements UnitClassInterface';
}
