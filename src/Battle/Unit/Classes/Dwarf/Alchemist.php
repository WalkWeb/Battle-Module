<?php

declare(strict_types=1);

namespace Battle\Unit\Classes\Dwarf;

use Battle\Unit\Ability\AbilityCollection;
use Battle\Unit\Ability\Effect\HealingPotionAbility;
use Battle\Unit\Classes\AbstractUnitClass;
use Battle\Unit\UnitInterface;

class Alchemist extends AbstractUnitClass
{
    private const ID         = 6;
    private const NAME       = 'Alchemist';
    private const SMALL_ICON = '/images/icons/small/alchemist.png';

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
        $collection->add(new HealingPotionAbility($unit));
        return $collection;
    }
}
