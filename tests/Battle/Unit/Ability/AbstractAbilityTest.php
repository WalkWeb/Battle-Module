<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Ability;

use Battle\Container\Container;
use Battle\Unit\Ability\AbilityCollection;
use Battle\Unit\Ability\AbilityInterface;
use Battle\Unit\UnitInterface;
use Exception;
use Tests\AbstractUnitTest;

abstract class AbstractAbilityTest extends AbstractUnitTest
{
    /**
     * @param UnitInterface $unit
     * @param string $abilityName
     * @param int $abilityLevel
     * @return AbilityInterface
     * @throws Exception
     */
    protected function createAbilityByDataProvider(UnitInterface $unit, string $abilityName, int $abilityLevel): AbilityInterface
    {
        $container = new Container();

        return $container->getAbilityFactory()->create(
            $unit,
            $container->getAbilityDataProvider()->get($abilityName, $abilityLevel)
        );
    }

    /**
     * @param AbilityInterface $ability
     * @param UnitInterface $unit
     * @throws Exception
     */
    protected function activateAbility(AbilityInterface $ability, UnitInterface $unit): void
    {
        for ($i = 0; $i < 20; $i++) {
            $unit->newRound();
        }

        $collection = new AbilityCollection();
        $collection->add($ability);

        foreach ($collection as $item) {
            self::assertEquals($ability, $item);
        }

        $collection->update($unit);
    }
}
