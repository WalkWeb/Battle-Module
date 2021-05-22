<?php

declare(strict_types=1);

namespace Battle\Classes\Undead;

use Battle\Action\ActionCollection;
use Battle\Action\Damage\HeavyStrikeAction;
use Battle\Classes\AbstractUnitClass;
use Battle\Classes\UnitClassInterface;
use Battle\Command\CommandInterface;
use Battle\Unit\UnitInterface;

class DeadKnight extends AbstractUnitClass
{
    private $id = UnitClassInterface::DEAD_KNIGHT;
    private $smallIcon = UnitClassInterface::DEAD_KNIGHT_SMALL_ICON;

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param UnitInterface $actionUnit
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $alliesCommand
     * @return ActionCollection
     */
    public function getAbility(
        UnitInterface $actionUnit,
        CommandInterface $enemyCommand,
        CommandInterface $alliesCommand
    ): ActionCollection
    {
        $collection = new ActionCollection();
        $collection->add(new HeavyStrikeAction($actionUnit, $enemyCommand, $alliesCommand));
        return $collection;
    }

    /**
     * @return string
     */
    public function getSmallIcon(): string
    {
        return $this->smallIcon;
    }
}
