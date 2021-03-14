<?php

declare(strict_types=1);

namespace Battle\Classes;

use Battle\Action\ActionCollection;
use Battle\Action\GreatHealAction;
use Battle\Command;
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
     * @param Command $enemyCommand
     * @param Command $alliesCommand
     * @return ActionCollection
     * @throws ActionCollectionException
     */
    public function getAbility(UnitInterface $actionUnit, Command $enemyCommand, Command $alliesCommand): ActionCollection
    {
        return new ActionCollection([new GreatHealAction($actionUnit, $alliesCommand)]);
    }
}
