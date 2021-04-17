<?php

declare(strict_types=1);

namespace Battle\Classes\Human;

use Battle\Action\ActionCollection;
use Battle\Action\ActionException;
use Battle\Action\HeavyStrikeAction;
use Battle\Classes\AbstractUnitClass;
use Battle\Classes\UnitClassInterface;
use Battle\Command\CommandInterface;
use Battle\Unit\UnitInterface;

class Warrior extends AbstractUnitClass
{
    private $id = UnitClassInterface::WARRIOR;
    private $smallIcon = UnitClassInterface::WARRIOR_SMALL_ICON;

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param UnitInterface $actionUnit
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $alliesCommand
     * @return ActionCollection
     * @throws ActionException
     */
    public function getAbility(
        UnitInterface $actionUnit,
        CommandInterface $enemyCommand,
        CommandInterface $alliesCommand
    ): ActionCollection
    {
        return new ActionCollection([new HeavyStrikeAction($actionUnit, $enemyCommand, $alliesCommand)]);
    }

    /**
     * @return string
     */
    public function getSmallIcon(): string
    {
        return $this->smallIcon;
    }
}
