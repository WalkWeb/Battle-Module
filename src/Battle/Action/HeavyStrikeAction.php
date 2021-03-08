<?php

declare(strict_types=1);

namespace Battle\Action;

class HeavyStrikeAction extends DamageAction
{
    protected const NAME = 'Heavy Strike';

    public function getPower(): int
    {
        // Удар в 250% от силы удара юнита
        return (int)round($this->getActionUnit()->getDamage() * 2.5);
    }
}
