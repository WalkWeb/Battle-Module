<?php

declare(strict_types=1);

namespace Tests\Battle\Action\Summon;

use Battle\Action\Summon\SummonAction;
use Battle\Classes\ClassFactoryException;
use Battle\Command\CommandException;
use Battle\Command\CommandFactory;
use Battle\Unit\UnitException;
use PHPUnit\Framework\TestCase;
use Tests\Battle\Factory\UnitFactory;
use Tests\Battle\Factory\UnitFactoryException;

class SummonActionTest extends TestCase
{
    /**
     * @throws ClassFactoryException
     * @throws CommandException
     * @throws UnitException
     * @throws UnitFactoryException
     */
    public function testCreateSummonAction(): void
    {
        $message = '';
        $alliesUnit = UnitFactory::createByTemplate(7);
        $enemyUnit = UnitFactory::createByTemplate(3);
        $alliesCommand = CommandFactory::create([$alliesUnit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        for ($i = 0; $i < 10; $i++) {
            $alliesUnit->newRound();
        }

        $actionCollection = $alliesUnit->getAction($enemyCommand, $alliesCommand);

        foreach ($actionCollection->getActions() as $action) {
            self::assertContainsOnlyInstancesOf(SummonAction::class, [$action]);
            $message = $action->handle();
        }

        self::assertEquals('<b>unit_7</b> [80/80] summon Imp', $message);
        self::assertCount(2, $alliesCommand->getUnits());
    }
}
