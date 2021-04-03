<?php

declare(strict_types=1);

namespace Tests\Battle\Action;

use Battle\Action\HealAction;
use Battle\Classes\ClassFactoryException;
use Battle\Command\Command;
use Battle\Command\CommandException;
use Battle\Command\CommandFactory;
use PHPUnit\Framework\TestCase;
use Tests\Battle\Factory\UnitFactory;
use Tests\Battle\Factory\UnitFactoryException;

class HealActionTest extends TestCase
{
    private const NO_TARGET = '<b>unit_1</b> [100/100] wanted to use heal, but no one';

    /**
     * @throws ClassFactoryException
     * @throws CommandException
     * @throws UnitFactoryException
     */
    public function testCreate(): void
    {
        $message = '';
        $unit = UnitFactory::createByTemplate(1);
        $alliesUnit = UnitFactory::createByTemplate(2);
        $enemyUnit = UnitFactory::createByTemplate(3);
        $alliesCommand = CommandFactory::create([$unit, $alliesUnit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $actionCollection = $unit->getHealAction($enemyCommand, $alliesCommand);

        foreach ($actionCollection->getActions() as $action) {
            self::assertContainsOnlyInstancesOf(HealAction::class, [$action]);
            $message = $action->handle();
        }

        self::assertEquals(self::NO_TARGET, $message);
    }
}
