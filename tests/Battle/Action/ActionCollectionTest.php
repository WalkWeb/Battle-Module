<?php

declare(strict_types=1);

namespace Tests\Battle\Action;

use Battle\Action\ActionCollection;
use Battle\Action\Damage\DamageAction;
use Battle\Command\CommandException;
use Battle\Command\CommandFactory;
use Battle\Result\Chat\Message;
use Battle\Unit\UnitException;
use Exception;
use PHPUnit\Framework\TestCase;
use Tests\Battle\Factory\UnitFactory;

class ActionCollectionTest extends TestCase
{
    /**
     * @throws CommandException
     * @throws UnitException
     * @throws Exception
     */
    public function testCreateActionCollectionSuccess(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $defendUnit = UnitFactory::createByTemplate(2);
        $defendCommand = CommandFactory::create([$defendUnit]);
        $alliesCommand = CommandFactory::create([$unit]);
        $action = new DamageAction($unit, $defendCommand, $alliesCommand, new Message());

        $actionCollection = new ActionCollection();
        $actionCollection->add($action);

        foreach ($actionCollection as $action) {
            self::assertInstanceOf(DamageAction::class, $action);
        }
    }
}
