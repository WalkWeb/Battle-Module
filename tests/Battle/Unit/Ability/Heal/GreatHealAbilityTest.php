<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Ability\Heal;

use Exception;
use PHPUnit\Framework\TestCase;
use Battle\Container\Container;
use Battle\Action\Heal\HealAction;
use Battle\Command\CommandFactory;
use Tests\Battle\Factory\UnitFactory;
use Battle\Unit\Ability\AbilityCollection;
use Battle\Unit\Ability\Heal\GreatHealAbility;

class GreatHealAbilityTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testGreatHealAbility(): void
    {
        $name = 'Great Heal Ability';
        $icon = '/images/icons/ability/196.png';
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = new GreatHealAbility($name, $icon, $unit, new Container());

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
            self::assertInstanceOf(HealAction::class, $action);
            self::assertEquals($unit->getDamage() * 3, $action->getPower());
        }
    }
}
