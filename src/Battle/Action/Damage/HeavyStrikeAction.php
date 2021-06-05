<?php

declare(strict_types=1);

namespace Battle\Action\Damage;

class HeavyStrikeAction extends DamageAction
{
    protected const NAME = 'use Heavy Strike at';

    public function getPower(): int
    {
        // Удар в 250% от силы удара юнита
        return (int)($this->getActionUnit()->getDamage() * 2.5);
    }
}
