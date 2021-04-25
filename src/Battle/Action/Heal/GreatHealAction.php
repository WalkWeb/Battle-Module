<?php

declare(strict_types=1);

namespace Battle\Action\Heal;

class GreatHealAction extends HealAction
{
    protected const NAME = 'Great Heal';

    public function getPower(): int
    {
        // Лечение в 300% от силы удара юнита
        return (int)round($this->getActionUnit()->getDamage() * 3);
    }
}
