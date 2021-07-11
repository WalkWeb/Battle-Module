<?php

declare(strict_types=1);

namespace Battle\Classes\Undead;

use Battle\Action\ActionCollection;
use Battle\Action\Summon\SummonSkeletonAction;
use Battle\Classes\AbstractUnitClass;
use Battle\Command\CommandInterface;
use Battle\Unit\Ability\AbilityCollection;
use Battle\Unit\Ability\Summon\SummonSkeletonAbility;
use Battle\Unit\UnitInterface;

class DarkMage extends AbstractUnitClass
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
        $collection->add(new SummonSkeletonAction($actionUnit, $alliesCommand, $alliesCommand, $this->message));
        return $collection;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return self::DARK_MAGE_ID;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return self::DARK_MAGE_NAME;
    }

    /**
     * @return string
     */
    public function getSmallIcon(): string
    {
        return self::DARK_MAGE_SMALL_ICON;
    }

    /**
     * @param UnitInterface $unit
     * @return AbilityCollection
     */
    public function getAbilities(UnitInterface $unit): AbilityCollection
    {
        $collection = new AbilityCollection();

        $collection->add(new SummonSkeletonAbility(
            'Summon Skeleton',
            '/images/icons/ability/338.png',
            $unit,
            $unit->getContainer()
        ));

        return $collection;
    }
}
