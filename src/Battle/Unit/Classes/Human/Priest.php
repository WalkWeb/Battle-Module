<?php

declare(strict_types=1);

namespace Battle\Unit\Classes\Human;

use Battle\Unit\Classes\AbstractUnitClass;
use Battle\Unit\Ability\AbilityCollection;
use Battle\Unit\Ability\Heal\GreatHealAbility;
use Battle\Unit\UnitInterface;

class Priest extends AbstractUnitClass
{
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
        $collection->add(new GreatHealAbility($unit));
        return $collection;
    }
}
