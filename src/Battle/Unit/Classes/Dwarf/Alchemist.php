<?php

declare(strict_types=1);

namespace Battle\Unit\Classes\Dwarf;

use Battle\Unit\Ability\AbilityCollection;
use Battle\Unit\Ability\Effect\HealingPotionAbility;
use Battle\Unit\Classes\AbstractUnitClass;
use Battle\Unit\UnitInterface;

class Alchemist extends AbstractUnitClass
{
    /**
     * @return int
     */
    public function getId(): int
    {
        return self::ALCHEMIST_ID;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return self::ALCHEMIST_NAME;
    }

    /**
     * @return string
     */
    public function getSmallIcon(): string
    {
        return self::ALCHEMIST_SMALL_ICON;
    }

    /**
     * @param UnitInterface $unit
     * @return AbilityCollection
     */
    public function getAbilities(UnitInterface $unit): AbilityCollection
    {
        $collection = new AbilityCollection();
        $collection->add(new HealingPotionAbility($unit));
        return $collection;
    }
}
