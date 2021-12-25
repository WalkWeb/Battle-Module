<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Ability\Summon;

use Exception;
use Battle\Action\SummonAction;
use PHPUnit\Framework\TestCase;
use Battle\Command\CommandFactory;
use Tests\Battle\Factory\UnitFactory;
use Battle\Unit\Ability\AbilityCollection;
use Battle\Unit\Ability\Summon\SummonImpAbility;

class SummonImpAbilityTest extends TestCase
{
    private const MESSAGE = '<span style="color: #1e72e3">unit_1</span> <img src="/images/icons/ability/275.png" alt="" /> summon Imp';

    /**
     * @throws Exception
     */
    public function testSummonImpAbility(): void
    {
        $name = 'Summon Imp';
        $icon = '/images/icons/ability/275.png';
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = new SummonImpAbility($unit);

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
