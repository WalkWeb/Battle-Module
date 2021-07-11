<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Ability\Summon;

use Battle\Action\Summon\SummonSkeletonAction;
use Battle\Unit\Ability\Summon\SummonSkeletonAbility;
use Exception;
use Battle\Container\Container;
use PHPUnit\Framework\TestCase;
use Battle\Command\CommandFactory;
use Tests\Battle\Factory\UnitFactory;
use Battle\Unit\Ability\AbilityCollection;

class SummonSkeletonAbilityTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testSummonSkeletonAbility(): void
    {
        $name = 'Summon Skeleton Ability';
        $icon = '/images/icons/ability/338.png';
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = new SummonSkeletonAbility($name, $icon, $unit, new Container());

        self::assertEquals($name, $ability->getName());
        self::assertEquals($icon, $ability->getIcon());
        self::assertEquals($unit, $ability->getUnit());
        self::assertFalse($ability->isReady());

        $unit->upMaxConcentration();

        $collection = new AbilityCollection();
        $collection->add($ability);

        foreach ($collection as $item) {
            self::assertEquals($ability, $item);
        }

        $collection->update($unit);

        self::assertTrue($ability->isReady());

        $actions = $ability->getAction($enemyCommand, $command);

        foreach ($actions as $action) {
            self::assertInstanceOf(SummonSkeletonAction::class, $action);
        }
    }
}
