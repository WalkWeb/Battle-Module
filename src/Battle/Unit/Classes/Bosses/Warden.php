<?php

declare(strict_types=1);

namespace Battle\Unit\Classes\Bosses;

use Battle\Unit\Ability\AbilityCollection;
use Battle\Unit\Ability\Damage\HellfireAbility;
use Battle\Unit\Ability\Effect\IncinerationAbility;
use Battle\Unit\Classes\AbstractUnitClass;
use Battle\Unit\UnitInterface;

class Warden extends AbstractUnitClass
{
    private const ID         = 50;
    private const NAME       = 'Warden';
    private const SMALL_ICON = '/images/icons/small/base-inferno.png';

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
        $collection->add(new HellfireAbility($unit));
        $collection->add(new IncinerationAbility($unit));
        return $collection;
    }
}
