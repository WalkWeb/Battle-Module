<?php

declare(strict_types=1);

namespace Battle\Unit\Ability\Summon;

use Battle\Action\ActionCollection;
use Battle\Action\Summon\SummonSkeletonAction;
use Battle\Command\CommandInterface;
use Battle\Container\ContainerException;
use Battle\Unit\Ability\AbstractAbility;
use Battle\Unit\UnitInterface;

class SummonSkeletonAbility extends AbstractAbility
{
    /**
     * Призывает скелета
     *
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $alliesCommand
     * @return ActionCollection
     * @throws ContainerException
     */
    public function getAction(CommandInterface $enemyCommand, CommandInterface $alliesCommand): ActionCollection
    {
        $collection = new ActionCollection();

        $collection->add(new SummonSkeletonAction(
            $this->unit,
            $enemyCommand,
            $alliesCommand,
            $this->container->getMessage()
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
