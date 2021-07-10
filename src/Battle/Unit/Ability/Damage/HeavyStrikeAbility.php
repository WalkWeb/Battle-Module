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
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $alliesCommand
     * @return ActionCollection
     * @throws ContainerException
     */
    public function getAction(CommandInterface $enemyCommand, CommandInterface $alliesCommand): ActionCollection
    {
        $collection = new ActionCollection();

        // TODO Переделать механику DamageAction таким образом, чтобы можно было устанавливать любую силу удара
        $collection->add(new DamageAction($this->unit, $enemyCommand, $alliesCommand, $this->container->getMessage()));

        return $collection;
    }

    public function update(UnitInterface $unit): void
    {
        if (!$this->ready && $unit->getConcentration() === UnitInterface::MAX_CONS) {
            $this->ready = true;
        }
    }
}
