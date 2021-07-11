<?php

declare(strict_types=1);

namespace Battle\Unit\Ability\Heal;

use Battle\Action\ActionCollection;
use Battle\Action\Heal\HealAction;
use Battle\Command\CommandInterface;
use Battle\Container\ContainerException;
use Battle\Unit\Ability\AbstractAbility;
use Battle\Unit\UnitInterface;

class GreatHealAbility extends AbstractAbility
{
    /**
     * Great Heal лечение в 300% от силы удара юнита
     *
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $alliesCommand
     * @return ActionCollection
     * @throws ContainerException
     */
    public function getAction(CommandInterface $enemyCommand, CommandInterface $alliesCommand): ActionCollection
    {
        $collection = new ActionCollection();

        $collection->add(new HealAction(
            $this->unit,
            $enemyCommand,
            $alliesCommand,
            $this->container->getMessage(),
            $this->unit->getDamage() * 3,
            'use Great Heal and heal'
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
}
