<?php

declare(strict_types=1);

namespace Tests\Battle\Action\Other;

use Battle\Action\Other\WaitAction;
use Battle\Command\CommandException;
use Battle\Command\CommandFactory;
use Battle\Unit\UnitException;
use Exception;
use PHPUnit\Framework\TestCase;
use Tests\Battle\Factory\UnitFactory;

class WaitActionTest extends TestCase
{
    private const MESSAGE = '<span style="color: #1e72e3">unit_14</span> preparing to attack';

    /**
     * @throws CommandException
     * @throws UnitException
     * @throws Exception
     */
    public function testCreateWaitAction(): void
    {
        $message = '';
        $alliesUnit = UnitFactory::createByTemplate(14);
        $enemyUnit = UnitFactory::createByTemplate(3);

        $alliesCommand = CommandFactory::create([$alliesUnit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $actionCollection = $alliesUnit->getAction($enemyCommand, $alliesCommand);

        foreach ($actionCollection as $action) {
            self::assertContainsOnlyInstancesOf(WaitAction::class, [$action]);
            $message .= $action->handle();
        }

        self::assertEquals(self::MESSAGE, $message);
    }
}
