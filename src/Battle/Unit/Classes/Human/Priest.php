<?php

declare(strict_types=1);

namespace Battle\Unit\Classes\Human;

use Battle\Unit\Ability\Resurrection\BackToLifeAbility;
use Battle\Unit\Classes\AbstractUnitClass;
use Battle\Unit\Ability\AbilityCollection;
use Battle\Unit\Ability\Heal\GreatHealAbility;
use Battle\Unit\UnitInterface;

class Priest extends AbstractUnitClass
{
    private const ID         = 2;
    private const NAME       = 'Priest';
    private const SMALL_ICON = '/images/icons/small/priest.png';

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
        $collection->add(new GreatHealAbility($unit));
        $collection->add(new BackToLifeAbility($unit));
        return $collection;
    }
}
