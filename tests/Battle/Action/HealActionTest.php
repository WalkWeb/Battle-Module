<?php

declare(strict_types=1);

namespace Tests\Battle\Action;

use Battle\Action\HealAction;
use Battle\Classes\ClassFactoryException;
use Battle\Command\Command;
use Battle\Exception\CommandException;
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
        $unit = UnitFactory::create(1);
        $alliesUnit = UnitFactory::create(2);
        $alliesCommand = new Command([$unit, $alliesUnit]);

        $actionCollection = $unit->getHealAction($alliesCommand);

        foreach ($actionCollection->getActions() as $action) {
            self::assertContainsOnlyInstancesOf(HealAction::class, [$action]);
            $message = $action->handle();
        }


        self::assertEquals(self::NO_TARGET, $message);
    }
}
