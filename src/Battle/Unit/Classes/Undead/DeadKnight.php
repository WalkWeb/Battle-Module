<?php

declare(strict_types=1);

namespace Battle\Unit\Classes\Undead;

use Battle\Unit\Classes\AbstractUnitClass;
use Battle\Unit\Ability\AbilityCollection;
use Battle\Unit\Ability\Damage\HeavyStrikeAbility;
use Battle\Unit\UnitInterface;

class DeadKnight extends AbstractUnitClass
{
    private const ID         = 3;
    private const NAME       = 'Dead Knight';
    private const SMALL_ICON = '/images/icons/small/dead-knight.png';

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
        $collection->add(new HeavyStrikeAbility($unit));
        return $collection;
    }
}
