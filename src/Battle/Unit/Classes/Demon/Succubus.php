<?php

declare(strict_types=1);

namespace Battle\Unit\Classes\Demon;

use Battle\Unit\Ability\AbilityCollection;
use Battle\Unit\Ability\Effect\PoisonAbility;
use Battle\Unit\Ability\Heal\GeneralHealAbility;
use Battle\Unit\Classes\AbstractUnitClass;
use Battle\Unit\UnitInterface;

class Succubus extends AbstractUnitClass
{
    private const ID         = 7;
    private const NAME       = 'Succubus';
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
        $collection->add(new PoisonAbility($unit));
        // В будущем Суккуб будет иметь уникальную способность переманивать на несколько раундов противника в свою
        // команду. GeneralHealAbility - временная способность для этого класса
        $collection->add(new GeneralHealAbility($unit));
        return $collection;
    }
}
