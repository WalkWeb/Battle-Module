<?php

declare(strict_types=1);

namespace Battle\Action;

use Exception;

class ActionException extends Exception
{
    public const string NO_DEFINED                    = 'Action: No defined unit';
    public const string NO_TARGET_UNIT                = 'Action: No target unit. Probably getTargetUnit() method is called before handle() is called';
    public const string NO_DEFINED_AGAIN              = 'Action: Despite the fact that command said that it has live units, getUnitForAttacks() return null';
    public const string NO_METHOD                     = 'Action: No method';
    public const string NO_TARGET_FOR_HEAL            = 'Action: No target for heal';
    public const string NO_TARGET_FOR_BUFF            = 'Action: No target for buff';
    public const string NO_TARGET_FOR_EFFECT          = 'Action: No target for effect';
    public const string UNKNOWN_TYPE_TARGET           = 'Action: Unknown type target';
    public const string NO_POWER_BY_UNIT              = 'Action: No power by unit';
    public const string INVALID_TARGET_TRACKING       = 'Action: Invalid "target_tracking" data: bool expected';
    public const string INVALID_RANDOM_DAMAGE         = 'Action: Invalid "random_damage" data: bool expected';
    public const string UNKNOWN_TYPE_ACTION           = 'ActionFactory: Unknown type action';
    public const string UNKNOWN_FACTORY_METHOD        = 'ActionFactory: Unknown factory method';
    public const string INVALID_TYPE_DATA             = 'ActionFactory: Invalid "type" data: int expected';
    public const string INVALID_POWER_DATA            = 'ActionFactory: Invalid "power" data: int expected';
    public const string INVALID_OFFENSE_DATA          = 'ActionFactory: Invalid "offense data": array or null expected';
    public const string INVALID_MULTIPLE_OFFENSE_DATA = 'ActionFactory: Invalid "multiple_offense" data: array or null expected';
    public const string INVALID_CAN_BE_AVOIDED        = 'ActionFactory: Invalid "can_be_avoided" data: bool expected';
    public const string INVALID_TYPE_TARGET_DATA      = 'ActionFactory: Invalid "type_target": int expected';
    public const string INVALID_NAME_DATA             = 'ActionFactory: Invalid "name" data: string expected';
    public const string INVALID_ACTION_UNIT_DATA      = 'ActionFactory: Invalid "unit" data';
    public const string INVALID_COMMAND_DATA          = 'ActionFactory: Invalid "command" data';
    public const string INVALID_SUMMON_DATA           = 'ActionFactory: Invalid "summon" data: array expected';
    public const string INVALID_EFFECT_DATA           = 'ActionFactory: Invalid "effect" data: array expected';
    public const string INVALID_MODIFY_METHOD_DATA    = 'ActionFactory: Invalid "modify_method" data: string expected';
    public const string INVALID_ANIMATION_METHOD_DATA = 'ActionFactory: Invalid "animation_method" data: string expected';
    public const string INVALID_MESSAGE_METHOD_DATA   = 'ActionFactory: invalid "message_method": string expected';
    public const string INVALID_ICON_DATA             = 'ActionFactory: invalid "icon": missing or string';
    public const string INVALID_RESURRECTED_POWER     = 'ResurrectionAction: invalid power';
    public const string INVALID_RESURRECTED_TARGET    = 'ResurrectionAction: invalid type target';
    public const string INVALID_MANA_RESTORE_TARGET   = 'ManaRestoreAction: invalid type target: available self-target only';
    public const string EMPTY_OFFENSE_AND_MULTIPLE    = 'DamageAction: empty $offense and $multipleOffense - nullable only one of them';
}
