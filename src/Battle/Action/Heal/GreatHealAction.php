<?php

declare(strict_types=1);

namespace Battle\Action\Heal;

class GreatHealAction extends HealAction
{
    protected const NAME = 'use Great Heal and heal';

    public function getPower(): int
    {
        // Лечение в 300% от силы удара юнита
        return (int)round($this->getActionUnit()->getDamage() * 3);
    }
}
