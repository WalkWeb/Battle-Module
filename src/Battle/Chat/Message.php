<?php

declare(strict_types=1);

namespace Battle\Chat;

use Battle\Action\DamageAction;
use Battle\Action\HealAction;

class Message
{
    public static function damage(DamageAction $action): string
    {
        return '<b>' .
            $action->getActionUnit()->getName() . '</b> [' .
            $action->getActionUnit()->getLife() . '/' .
            $action->getActionUnit()->getTotalLife() . '] ' . $action->getNameAction() . ' <b>' .
            $action->getTargetUnit()->getName() . '</b> [' .
            $action->getTargetUnit()->getLife() . '/' .
            $action->getTargetUnit()->getTotalLife() . '] on ' .
            $action->getFactualPower() . ' damage';
    }

    public static function heal(HealAction $action): string
    {
        return '<b>' .
            $action->getActionUnit()->getName() . '</b> [' .
            $action->getActionUnit()->getLife() . '/' .
            $action->getActionUnit()->getTotalLife() . '] ' . $action->getNameAction() . ' to <b>' .
            $action->getTargetUnit()->getName() . '</b> [' .
            $action->getTargetUnit()->getLife() . '/' .
            $action->getTargetUnit()->getTotalLife() . '] on ' .
            $action->getFactualPower() . ' life';
    }

    public static function hoTargetForHeal(HealAction $action): string
    {
        return '<b>' .
            $action->getActionUnit()->getName() . '</b> [' .
            $action->getActionUnit()->getLife() . '/' .
            $action->getActionUnit()->getTotalLife() . '] wanted to use heal, but no one';
    }
}
