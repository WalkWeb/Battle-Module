<?php

declare(strict_types=1);

namespace Battle\Classes\Orcs;

use Battle\Classes\AbstractUnitClass;
use Battle\Unit\Ability\AbilityCollection;
use Battle\Unit\Ability\Effect\ReserveForcesAbility;
use Battle\Unit\UnitInterface;

class Titan extends AbstractUnitClass
{
    public function getAbilities(UnitInterface $unit): AbilityCollection
    {
        $collection = new AbilityCollection();
        $collection->add(new ReserveForcesAbility($unit));
        return $collection;
    }

    public function getId(): int
    {
        return self::TITAN_ID;
    }

    public function getName(): string
    {
        return self::TITAN_NAME;
    }

    public function getSmallIcon(): string
    {
        return self::TITAN_SMALL_ICON;
    }
}
