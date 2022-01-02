<?php

declare(strict_types=1);

namespace Tests\Battle\Action;

use Battle\Action\WaitAction;
use Battle\Command\CommandException;
use Battle\Command\CommandFactory;
use Battle\Unit\UnitException;
use Exception;
use Tests\AbstractUnitTest;
use Tests\Battle\Factory\UnitFactory;

class WaitActionTest extends AbstractUnitTest
{
    /**
     * @throws CommandException
     * @throws UnitException
     * @throws Exception
     */
    public function testCreateWaitAction(): void
    {
        $alliesUnit = UnitFactory::createByTemplate(14);
        $enemyUnit = UnitFactory::createByTemplate(3);

        $alliesCommand = CommandFactory::create([$alliesUnit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $actionCollection = $alliesUnit->getAction($enemyCommand, $alliesCommand);

        foreach ($actionCollection as $action) {
            self::assertContainsOnlyInstancesOf(WaitAction::class, [$action]);
            self::assertEquals('wait', $action->getAnimationMethod());
            self::assertEquals('wait', $action->getMessageMethod());
            self::assertTrue($action->canByUsed());
            $action->handle();
        }
    }
}
