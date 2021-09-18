<?php

declare(strict_types=1);

namespace Battle\Unit\Classes\Undead;

use Battle\Unit\Classes\AbstractUnitClass;
use Battle\Unit\Ability\AbilityCollection;
use Battle\Unit\Ability\Summon\SummonSkeletonAbility;
use Battle\Unit\UnitInterface;

class DarkMage extends AbstractUnitClass
{
    private const ID         = 4;
    private const NAME       = 'Dark Mage';
    private const SMALL_ICON = '/images/icons/small/dark-mage.png';

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

    public function getAbilities(UnitInterface $unit): AbilityCollection
    {
        $collection = new AbilityCollection();
        $collection->add(new SummonSkeletonAbility($unit));
        return $collection;
    }
}
