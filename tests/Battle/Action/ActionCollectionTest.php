<?php

declare(strict_types=1);

namespace Tests\Battle\Action;

use Battle\Action\HealAction;
use Exception;
use Battle\Action\ActionCollection;
use Battle\Action\DamageAction;
use Battle\Command\CommandFactory;
use PHPUnit\Framework\TestCase;
use Tests\Battle\Factory\UnitFactory;

class ActionCollectionTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testActionCollectionCreateSuccess(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $defendUnit = UnitFactory::createByTemplate(2);
        $defendCommand = CommandFactory::create([$defendUnit]);
        $alliesCommand = CommandFactory::create([$unit]);
        $action = new DamageAction($unit, $defendCommand, $alliesCommand);

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
        $defendUnit = UnitFactory::createByTemplate(2);
        $defendCommand = CommandFactory::create([$defendUnit]);
        $alliesCommand = CommandFactory::create([$unit]);

        $damageAction = new DamageAction($unit, $defendCommand, $alliesCommand);
        $healAction = new HealAction($unit, $defendCommand, $alliesCommand);

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
