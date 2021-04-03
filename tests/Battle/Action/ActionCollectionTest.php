<?php

declare(strict_types=1);

namespace Tests\Battle\Action;

use Battle\Action\ActionCollection;
use Battle\Action\DamageAction;
use Battle\Classes\ClassFactoryException;
use Battle\Action\ActionException;
use Battle\Command\CommandException;
use Battle\Command\CommandFactory;
use PHPUnit\Framework\TestCase;
use Tests\Battle\Factory\UnitFactory;
use Tests\Battle\Factory\UnitFactoryException;

class ActionCollectionTest extends TestCase
{
    /**
     * @throws ActionException
     * @throws CommandException
     * @throws UnitFactoryException
     * @throws ClassFactoryException
     */
    public function testCreateActionCollectionSuccess(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $defendUnit = UnitFactory::createByTemplate(2);
        $defendCommand = CommandFactory::create([$defendUnit]);
        $alliesCommand = CommandFactory::create([$unit]);
        $action = new DamageAction($unit, $defendCommand, $alliesCommand);

        $actionCollection = new ActionCollection([$action]);

        foreach ($actionCollection->getActions() as $action) {
            self::assertInstanceOf(DamageAction::class, $action);
        }
    }

    /**
     * @throws ActionException
     */
    public function testCreateActionCollectionFail(): void
    {
        $this->expectException(ActionException::class);
        new ActionCollection(['action']);
    }
}
