<?php

declare(strict_types=1);

namespace Battle\Classes\Human;

use Battle\Action\ActionCollection;
use Battle\Action\Damage\HeavyStrikeAction;
use Battle\Classes\AbstractUnitClass;
use Battle\Command\CommandInterface;
use Battle\Unit\UnitInterface;

class Warrior extends AbstractUnitClass
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
        $collection->add(new HeavyStrikeAction($actionUnit, $enemyCommand, $alliesCommand, $this->message));
        return $collection;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return self::WARRIOR_ID;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return self::WARRIOR_NAME;
    }

    /**
     * @return string
     */
    public function getSmallIcon(): string
    {
        return self::WARRIOR_SMALL_ICON;
    }
}
