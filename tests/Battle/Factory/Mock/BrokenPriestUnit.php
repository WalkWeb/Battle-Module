<?php

declare(strict_types=1);

namespace Tests\Battle\Factory\Mock;

use Battle\Action\ActionCollection;
use Battle\Action\Heal\HealAction;
use Battle\Command\CommandInterface;
use Battle\Container\ContainerException;
use Battle\Unit\Unit;

class BrokenPriestUnit extends Unit
{
    /**
     * Заменяем родительский метод getAction(), заменяя его методом, который всегда будет возвращать лечение
     *
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $alliesCommand
     * @return ActionCollection
     * @throws ContainerException
     */
    public function getAction(CommandInterface $enemyCommand, CommandInterface $alliesCommand): ActionCollection
    {
        $collection = new ActionCollection();
        $collection->add(new HealAction($this, $enemyCommand, $alliesCommand, $this->getContainer()->getMessage()));
        return $collection;
    }
}
