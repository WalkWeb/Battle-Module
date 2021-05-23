<?php

declare(strict_types=1);

namespace Battle\Action\Damage;

class HeavyStrikeAction extends DamageAction
{
    protected const NAME = 'Heavy Strike';

    public function getPower(): int
    {
        // todo сообщение в чате звучит: Warrior Тяжелый Удар Necromancer на 1 урона
        // todo а должно быть: Warrior использовал Тяжелый Удар по Necromancer на 1 урона
        // Удар в 250% от силы удара юнита
        return (int)round($this->getActionUnit()->getDamage() * 2.5);
    }
}
