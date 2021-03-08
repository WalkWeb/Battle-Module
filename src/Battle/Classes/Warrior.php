<?php

declare(strict_types=1);

namespace Battle\Classes;

use Battle\Action\ActionCollection;
use Battle\Action\HeavyStrikeAction;
use Battle\Command;
use Battle\Exception\ActionCollectionException;
use Battle\Unit;

class Warrior extends UnitClass
{
    private $id = UnitClassInterface::WARRIOR;

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param Unit $actionUnit
     * @param Command $enemyCommand
     * @param Command $alliesCommand
     * @return ActionCollection
     * @throws ActionCollectionException
     */
    public function getAbility(Unit $actionUnit, Command $enemyCommand, Command $alliesCommand): ActionCollection
    {
        return new ActionCollection([new HeavyStrikeAction($actionUnit, $enemyCommand)]);
    }
}
