<?php

declare(strict_types=1);

namespace Battle\Classes;

use Battle\Action\ActionCollection;
use Battle\Action\GreatHealAction;
use Battle\Command\CommandInterface;
use Battle\Exception\ActionCollectionException;
use Battle\Unit\UnitInterface;

class Priest extends UnitClass
{
    private $id = UnitClassInterface::PRIEST;

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param UnitInterface $actionUnit
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $alliesCommand
     * @return ActionCollection
     * @throws ActionCollectionException
     */
    public function getAbility(UnitInterface $actionUnit, CommandInterface $enemyCommand, CommandInterface $alliesCommand): ActionCollection
    {
        return new ActionCollection([new GreatHealAction($actionUnit, $alliesCommand)]);
    }
}
