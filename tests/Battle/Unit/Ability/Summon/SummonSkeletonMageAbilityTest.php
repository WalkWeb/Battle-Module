<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Ability\Summon;

use Exception;
use Battle\Action\SummonAction;
use Battle\Command\CommandFactory;
use Battle\Unit\Ability\AbilityCollection;
use Battle\Unit\Ability\Summon\SummonSkeletonMageAbility;
use Tests\AbstractUnitTest;
use Tests\Battle\Factory\UnitFactory;

class SummonSkeletonMageAbilityTest extends AbstractUnitTest
{
    private const MESSAGE = '<span style="color: #1e72e3">unit_1</span> <img src="/images/icons/ability/503.png" alt="" /> summon Skeleton Mage';

    /**
     * @throws Exception
     */
    public function testSummonSkeletonMageAbility(): void
    {
        $name = 'Summon Skeleton Mage';
        $icon = '/images/icons/ability/503.png';
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = new SummonSkeletonMageAbility($unit);

        self::assertEquals($name, $ability->getName());
        self::assertEquals($icon, $ability->getIcon());
        self::assertEquals($unit, $ability->getUnit());
        self::assertFalse($ability->isReady());
        self::assertTrue($ability->canByUsed($enemyCommand, $command));

        // Up concentration
        for ($i = 0; $i < 10; $i++) {
            $unit->newRound();
        }

        $collection = new AbilityCollection();
        $collection->add($ability);

        foreach ($collection as $item) {
            self::assertEquals($ability, $item);
        }

        $collection->update($unit);

        self::assertTrue($ability->isReady());

        $actions = $ability->getAction($enemyCommand, $command);

        foreach ($actions as $action) {
            self::assertInstanceOf(SummonAction::class, $action);
            self::assertTrue($action->canByUsed());
            self::assertEquals(self::MESSAGE, $action->handle());
        }

        $ability->usage();

        self::assertFalse($ability->isReady());

        self::assertEquals(0, $unit->getConcentration());
    }
}
