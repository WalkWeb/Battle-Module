<?php

declare(strict_types=1);

namespace Tests\Battle\Action;

use Battle\Action\HealAction;
use Exception;
use Battle\Action\ActionCollection;
use Battle\Action\DamageAction;
use Battle\Command\CommandFactory;
use Tests\AbstractUnitTest;
use Tests\Battle\Factory\UnitFactory;

class ActionCollectionTest extends AbstractUnitTest
{
    /**
     * @throws Exception
     */
    public function testActionCollectionCreateSuccess(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = new DamageAction(
            $unit,
            $enemyCommand,
            $command,
            DamageAction::TARGET_RANDOM_ENEMY,
            $unit->getOffense()->getDamage($enemyUnit->getDefense()),
            true,
            DamageAction::DEFAULT_NAME,
            DamageAction::UNIT_ANIMATION_METHOD,
            DamageAction::DEFAULT_MESSAGE_METHOD
        );

        $actionCollection = new ActionCollection();
        $actionCollection->add($action);

        foreach ($actionCollection as $action) {
            self::assertInstanceOf(DamageAction::class, $action);
        }
    }

    /**
     * @throws Exception
     */
    public function testActionCollectionAddCollection(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $damageAction = new DamageAction(
            $unit,
            $enemyCommand,
            $command,
            DamageAction::TARGET_RANDOM_ENEMY,
            $unit->getOffense()->getDamage($enemyUnit->getDefense()),
            true,
            DamageAction::DEFAULT_NAME,
            DamageAction::UNIT_ANIMATION_METHOD,
            DamageAction::DEFAULT_MESSAGE_METHOD
        );

        $healAction = new HealAction(
            $unit,
            $enemyCommand,
            $command,
            HealAction::TARGET_WOUNDED_ALLIES,
            20
        );

        $actionCollection = new ActionCollection();
        $actionCollection->add($damageAction);

        $secondaryActionCollection = new ActionCollection();
        $secondaryActionCollection->add($healAction);

        $actionCollection->addCollection($secondaryActionCollection);

        self::assertCount(2, $actionCollection);

        $expectedAction = [$damageAction, $healAction];

        foreach ($actionCollection as $key => $action) {
            self::assertEquals($expectedAction[$key], $action);
        }
    }
}
