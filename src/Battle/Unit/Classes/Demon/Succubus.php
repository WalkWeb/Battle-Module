<?php

declare(strict_types=1);

namespace Battle\Unit\Classes\Demon;

use Battle\Unit\Ability\AbilityCollection;
use Battle\Unit\Ability\Effect\PoisonAbility;
use Battle\Unit\Classes\AbstractUnitClass;
use Battle\Unit\UnitInterface;

class Succubus extends AbstractUnitClass
{
    /**
     * @return int
     */
    public function getId(): int
    {
        return self::SUCCUBUS_ID;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return self::SUCCUBUS_NAME;
    }

    /**
     * @return string
     */
    public function getSmallIcon(): string
    {
        return self::SUCCUBUS_SMALL_ICON;
    }

    /**
     * @param UnitInterface $unit
     * @return AbilityCollection
     */
    public function getAbilities(UnitInterface $unit): AbilityCollection
    {
        $collection = new AbilityCollection();
        $collection->add(new PoisonAbility($unit));
        return $collection;
    }
}
