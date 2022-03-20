<?php

declare(strict_types=1);

namespace Tests\Battle\Factory\Mock;

use Battle\Action\ActionCollection;
use Battle\Action\HealAction;
use Battle\Command\CommandInterface;
use Battle\Unit\Unit;

class BrokenPriestUnit extends Unit
{
    /**
     * Заменяем родительский метод getAction(), заменяя его методом, который всегда будет возвращать лечение
     *
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $alliesCommand
     * @return ActionCollection
     */
    public function getAction(CommandInterface $enemyCommand, CommandInterface $alliesCommand): ActionCollection
    {
        $collection = new ActionCollection();

        $collection->add(new HealAction(
            $this,
            $enemyCommand,
            $alliesCommand,
            HealAction::TARGET_WOUNDED_ALLIES,
            $this->getDamage()
        ));

        return $collection;
    }
}
