<?php

declare(strict_types=1);

namespace Battle\Unit\Ability\Damage;

use Battle\Action\ActionCollection;
use Battle\Action\Damage\DamageAction;
use Battle\Command\CommandInterface;
use Battle\Container\ContainerException;
use Battle\Unit\Ability\AbstractAbility;
use Battle\Unit\UnitInterface;

class HeavyStrikeAbility extends AbstractAbility
{
    /**
     * Heavy Strike наносит 250% урона от базового урона юнита
     *
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $alliesCommand
     * @return ActionCollection
     * @throws ContainerException
     */
    public function getAction(CommandInterface $enemyCommand, CommandInterface $alliesCommand): ActionCollection
    {
        $collection = new ActionCollection();

        $collection->add(new DamageAction(
            $this->unit,
            $enemyCommand,
            $alliesCommand,
            $this->container->getMessage(),
            (int)($this->unit->getDamage() * 2.5),
            'use Heavy Strike at'
        ));

        return $collection;
    }

    /**
     * Способность активируется при полной концентрации юнита
     *
     * @param UnitInterface $unit
     */
    public function update(UnitInterface $unit): void
    {
        if (!$this->ready && $unit->getConcentration() === UnitInterface::MAX_CONS) {
            $this->ready = true;
        }
    }

    /**
     * Способность отмечает свое использование - переходит в неактивный статус и обнуляет концентрацию у юнита
     *
     * @param UnitInterface $unit
     */
    public function usage(UnitInterface $unit): void
    {
        $this->ready = false;
        $unit->useConcentrationAbility();
    }

    /**
     * Атакующие способности всегда готовы к использованию - потому что, если живых противников не остается - бой
     * заканчивается
     *
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $alliesCommand
     * @return bool
     */
    public function canByUsed(CommandInterface $enemyCommand, CommandInterface $alliesCommand): bool
    {
        return true;
    }
}
