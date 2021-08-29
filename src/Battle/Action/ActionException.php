<?php

declare(strict_types=1);

namespace Battle\Action;

use Exception;

class ActionException extends Exception
{
    public const NO_DEFINED               = 'Action: No defined unit';
    public const NO_TARGET_UNIT           = 'Action: No target unit. Probably getTargetUnit() method is called before handle() is called';
    public const NO_DEFINED_AGAIN         = 'Action: Despite the fact that command said that it has live units, getUnitForAttacks() return null';
    public const NO_METHOD                = 'Action: No method';
    public const NO_TARGET_FOR_HEAL       = 'Action: No target for heal';
    public const NO_TARGET_FOR_BUFF       = 'Action: No target for buff';
    public const NO_TARGET_FOR_EFFECT     = 'Action: No target for effect';
    public const UNKNOWN_TYPE_TARGET      = 'Action: Unknown type target';
    public const UNKNOWN_TYPE_ACTION      = 'ActionFactory: Unknown type action: int expected';
    public const INVALID_TYPE_DATA        = 'ActionFactory: Invalid type data';
    public const INVALID_POWER_DATA       = 'ActionFactory: Invalid power data: int or null expected';
    public const INVALID_TYPE_TARGET_DATA = 'ActionFactory: Invalid type_target: int expected';
    public const INVALID_NAME_DATA        = 'ActionFactory: Invalid name data: string or null expected';
    public const INVALID_ACTION_UNIT_DATA = 'ActionFactory: Invalid unit data';
    public const INVALID_COMMAND_DATA     = 'ActionFactory: Invalid command data';
    public const INVALID_SUMMON_DATA      = 'ActionFactory: Invalid summon data: array expected';
    public const NO_REALIZE               = 'ActionFactory: event type has not yet been implemented';
}
