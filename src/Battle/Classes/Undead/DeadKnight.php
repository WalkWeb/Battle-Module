<?php

declare(strict_types=1);

namespace Battle\Classes\Undead;

use Battle\Classes\AbstractUnitClass;
use Battle\Unit\Ability\AbilityCollection;
use Battle\Unit\Ability\Damage\HeavyStrikeAbility;
use Battle\Unit\UnitInterface;

class DeadKnight extends AbstractUnitClass
{
    /**
     * @return int
     */
    public function getId(): int
    {
        return self::DEAD_KNIGHT_ID;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return self::DEAD_KNIGHT_NAME;
    }

    /**
     * @return string
     */
    public function getSmallIcon(): string
    {
        return self::DEAD_KNIGHT_SMALL_ICON;
    }

    /**
     * @param UnitInterface $unit
     * @return AbilityCollection
     */
    public function getAbilities(UnitInterface $unit): AbilityCollection
    {
        $collection = new AbilityCollection();

        $collection->add(new HeavyStrikeAbility(
            'Heavy Strike',
            '/images/icons/ability/335.png',
            $unit
        ));

        return $collection;
    }
}
