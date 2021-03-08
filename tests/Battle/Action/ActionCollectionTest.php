<?php

declare(strict_types=1);

namespace Tests\Battle\Action;

use Battle\Action\ActionCollection;
use Battle\Action\DamageAction;
use Battle\Classes\ClassFactoryException;
use Battle\Command;
use Battle\Exception\ActionCollectionException;
use Battle\Exception\CommandException;
use PHPUnit\Framework\TestCase;
use Tests\Battle\Factory\UnitFactory;
use Tests\Battle\Factory\UnitFactoryException;

class ActionCollectionTest extends TestCase
{
    /**
     * @throws ActionCollectionException
     * @throws CommandException
     * @throws UnitFactoryException
     * @throws ClassFactoryException
     */
    public function testCreateSuccess(): void
    {
        $unit = UnitFactory::create(1);
        $defendUnit = UnitFactory::create(2);
        $defendCommand = new Command([$defendUnit]);
        $action = new DamageAction($unit, $defendCommand);

        $actionCollection = new ActionCollection([$action]);

        foreach ($actionCollection->getActions() as $action) {
            self::assertInstanceOf(DamageAction::class, $action);
        }
    }

    /**
     * @throws ActionCollectionException
     */
    public function testCreateFail(): void
    {
        $this->expectException(ActionCollectionException::class);
        new ActionCollection(['action']);
    }
}
