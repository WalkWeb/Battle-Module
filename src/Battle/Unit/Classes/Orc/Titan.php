<?php

declare(strict_types=1);

namespace Battle\Unit\Classes\Orc;

use Battle\Unit\Ability\Effect\BattleFuryAbility;
use Battle\Unit\Classes\AbstractUnitClass;
use Battle\Unit\Ability\AbilityCollection;
use Battle\Unit\Ability\Effect\ReserveForcesAbility;
use Battle\Unit\UnitInterface;

class Titan extends AbstractUnitClass
{
    private const ID         = 5;
    private const NAME       = 'Titan';
    private const SMALL_ICON = '/images/icons/small/titan.png';

    public function getAbilities(UnitInterface $unit): AbilityCollection
    {
        $collection = new AbilityCollection();
        $collection->add(new ReserveForcesAbility($unit));
        $collection->add(new BattleFuryAbility($unit));
        return $collection;
    }

    public function getId(): int
    {
        return self::ID;
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function getSmallIcon(): string
    {
        return self::SMALL_ICON;
    }
}
