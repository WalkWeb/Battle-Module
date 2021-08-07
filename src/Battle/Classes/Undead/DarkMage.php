<?php

declare(strict_types=1);

namespace Battle\Classes\Undead;

use Battle\Classes\AbstractUnitClass;
use Battle\Unit\Ability\AbilityCollection;
use Battle\Unit\Ability\Summon\SummonSkeletonAbility;
use Battle\Unit\UnitInterface;

class DarkMage extends AbstractUnitClass
{
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
        $collection->add(new SummonSkeletonAbility($unit));
        return $collection;
    }
}
