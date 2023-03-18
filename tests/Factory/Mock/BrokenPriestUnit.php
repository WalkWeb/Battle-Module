<?php

declare(strict_types=1);

namespace Tests\Factory\Mock;

use Battle\Action\ActionCollection;
use Battle\Action\HealAction;
use Battle\Command\CommandInterface;
use Battle\Container\Container;
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
    public function getActions(CommandInterface $enemyCommand, CommandInterface $alliesCommand): ActionCollection
    {
        $collection = new ActionCollection();

        $collection->add(new HealAction(
            new Container(),
            $this,
            $enemyCommand,
            $alliesCommand,
            HealAction::TARGET_WOUNDED_ALLIES,
            30,
            '',
            HealAction::UNIT_ANIMATION_METHOD,
            HealAction::DEFAULT_MESSAGE_METHOD
        ));

        return $collection;
    }
}
