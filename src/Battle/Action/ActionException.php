<?php

declare(strict_types=1);

namespace Battle\Action;

use Exception;

class ActionException extends Exception
{
    public const NO_DEFINED       = 'No defined unit';
    public const NO_DEFINED_AGAIN = 'Despite the fact that command said that it has live units, getUnitForAttacks() return null';
}
