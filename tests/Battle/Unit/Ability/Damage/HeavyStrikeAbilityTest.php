<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Ability\Damage;

use Exception;
use Battle\Action\DamageAction;
use Battle\Command\CommandFactory;
use Battle\Unit\Ability\AbilityCollection;
use Tests\AbstractUnitTest;
use Tests\Battle\Factory\UnitFactory;
use Battle\Unit\Ability\Damage\HeavyStrikeAbility;

class HeavyStrikeAbilityTest extends AbstractUnitTest
{
    // TODO Сообщение на русском

    private const MESSAGE = '<span style="color: #1e72e3">unit_1</span> use <img src="/images/icons/ability/335.png" alt="" /> <span class="ability">Heavy Strike</span> at <span style="color: #1e72e3">unit_2</span> on 50 damage';

    /**
     * @throws Exception
     */
    public function testHeavyStrikeAbility(): void
    {
        $name = 'Heavy Strike';
        $icon = '/images/icons/ability/335.png';
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = new HeavyStrikeAbility($unit);

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
            self::assertInstanceOf(DamageAction::class, $action);
            self::assertEquals((int)($unit->getDamage() * 2.5), $action->getPower());
            self::assertTrue($action->canByUsed());
            self::assertEquals(self::MESSAGE, $action->handle());
        }
    }
}
