<?php

declare(strict_types=1);

namespace Battle\Classes\Human;

use Battle\Classes\AbstractUnitClass;
use Battle\Unit\Ability\AbilityCollection;
use Battle\Unit\Ability\Damage\HeavyStrikeAbility;
use Battle\Unit\UnitInterface;

class Warrior extends AbstractUnitClass
{
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

    /**
     * @param UnitInterface $unit
     * @return AbilityCollection
     */
    public function getAbilities(UnitInterface $unit): AbilityCollection
    {
        $collection = new AbilityCollection();
        $collection->add(new HeavyStrikeAbility($unit));
        return $collection;
    }
}
