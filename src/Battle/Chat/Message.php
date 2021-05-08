<?php

declare(strict_types=1);

namespace Battle\Chat;

use Battle\Action\Damage\DamageAction;
use Battle\Action\Heal\HealAction;
use Battle\Action\Summon\SummonAction;

class Message
{
    public static function damage(DamageAction $action): string
    {
        return '<b>' .
            $action->getActionUnit()->getName() . '</b> ' . $action->getNameAction() . ' <b>' .
            $action->getTargetUnit()->getName() . '</b> on ' . $action->getFactualPower() . ' damage';
    }

    public static function heal(HealAction $action): string
    {
        return '<b>' .
            $action->getActionUnit()->getName() . '</b> ' . $action->getNameAction() . ' to <b>' .
            $action->getTargetUnit()->getName() . '</b> on ' . $action->getFactualPower() . ' life';
    }

    public static function summon(SummonAction $action): string
    {
        return '<b>' .
            $action->getActionUnit()->getName() . '</b> '  . $action->getNameAction();
    }
}
