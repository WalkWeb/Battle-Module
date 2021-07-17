<?php

declare(strict_types=1);

namespace Battle\Action;

use Exception;

class ActionException extends Exception
{
    public const NO_DEFINED         = 'No defined unit';
    public const NO_TARGET_UNIT     = 'No target unit. Probably getTargetUnit() method is called before handle() is called';
    public const NO_DEFINED_AGAIN   = 'Despite the fact that command said that it has live units, getUnitForAttacks() return null';
    public const NO_METHOD          = 'No method';
    public const NO_TARGET_FOR_HEAL = 'No target for heal';
}
