<?php

declare(strict_types=1);

namespace Battle\Classes\Human;

use Battle\Action\ActionCollection;
use Battle\Action\Heal\GreatHealAction;
use Battle\Classes\AbstractUnitClass;
use Battle\Command\CommandInterface;
use Battle\Unit\UnitInterface;

class Priest extends AbstractUnitClass
{
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
        $collection->add(new GreatHealAction($actionUnit, $enemyCommand, $alliesCommand, $this->message));
        return $collection;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return self::PRIEST_ID;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return self::PRIEST_NAME;
    }

    /**
     * @return string
     */
    public function getSmallIcon(): string
    {
        return self::PRIEST_SMALL_ICON;
    }
}
