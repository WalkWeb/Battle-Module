<?php

declare(strict_types=1);

namespace Battle\Classes\Human;

use Battle\Action\ActionCollection;
use Battle\Action\Heal\GreatHealAction;
use Battle\Classes\AbstractUnitClass;
use Battle\Classes\UnitClassInterface;
use Battle\Command\CommandInterface;
use Battle\Unit\UnitInterface;

class Priest extends AbstractUnitClass
{
    private $id = UnitClassInterface::PRIEST;
    private $smallIcon = UnitClassInterface::PRIEST_SMALL_ICON;

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
        $collection->add(new GreatHealAction($actionUnit, $alliesCommand, $alliesCommand));
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
