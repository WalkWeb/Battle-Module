<?php

declare(strict_types=1);

namespace Battle\Classes\Human;

use Battle\Action\ActionCollection;
use Battle\Action\Heal\GreatHealAction;
use Battle\Classes\AbstractUnitClass;
use Battle\Command\CommandInterface;
use Battle\Unit\Ability\AbilityCollection;
use Battle\Unit\Ability\Heal\GreatHealAbility;
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

    /**
     * @param UnitInterface $unit
     * @return AbilityCollection
     */
    public function getAbilities(UnitInterface $unit): AbilityCollection
    {
        $collection = new AbilityCollection();

        $collection->add(new GreatHealAbility(
            'Great Heal',
            '/images/icons/ability/338.png',
            $unit,
            $unit->getContainer()
        ));

        return $collection;
    }
}
