<?php

declare(strict_types=1);

namespace Battle\Action;

use Exception;

class ActionException extends Exception
{
    public const NO_DEFINED           = 'Action: No defined unit';
    public const NO_TARGET_UNIT       = 'Action: No target unit. Probably getTargetUnit() method is called before handle() is called';
    public const NO_DEFINED_AGAIN     = 'Action: Despite the fact that command said that it has live units, getUnitForAttacks() return null';
    public const NO_METHOD            = 'Action: No method';
    public const NO_TARGET_FOR_HEAL   = 'Action: No target for heal';
    public const NO_TARGET_FOR_BUFF   = 'Action: No target for buff';
    public const NO_TARGET_FOR_EFFECT = 'Action: No target for effect';
    public const UNKNOWN_TYPE_TARGET  = 'Action: Unknown type target';
}
