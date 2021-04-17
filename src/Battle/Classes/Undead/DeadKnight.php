<?php

declare(strict_types=1);

namespace Battle\Classes\Undead;

use Battle\Action\ActionCollection;
use Battle\Action\ActionException;
use Battle\Action\HeavyStrikeAction;
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
     * @throws ActionException
     */
    public function getAbility(
        UnitInterface $actionUnit,
        CommandInterface $enemyCommand,
        CommandInterface $alliesCommand
    ): ActionCollection
    {
        // В будущем ассортимент способностей будет расширен
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
