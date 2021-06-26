<?php

declare(strict_types=1);

namespace Tests\Battle\Classes\Human;

use Battle\Action\Heal\GreatHealAction;
use Battle\Classes\UnitClassInterface;
use Battle\Command\CommandException;
use Battle\Command\CommandFactory;
use Battle\Unit\UnitException;
use Exception;
use PHPUnit\Framework\TestCase;
use Tests\Battle\Factory\UnitFactory;

class PriestTest extends TestCase
{
    /**
     * @throws CommandException
     * @throws UnitException
     * @throws Exception
     */
    public function testCreatePriestClass(): void
    {
        $actionUnit = UnitFactory::createByTemplate(5);
        $enemyUnit = UnitFactory::createByTemplate(1);
        $actionCommand = CommandFactory::create([$actionUnit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $priest = $actionUnit->getClass();

        self::assertEquals(UnitClassInterface::PRIEST_ID, $priest->getId());
        self::assertEquals(UnitClassInterface::PRIEST_NAME, $priest->getName());
        self::assertEquals(UnitClassInterface::PRIEST_SMALL_ICON, $priest->getSmallIcon());

        $actionCollection = $priest->getAbility($actionUnit, $enemyCommand, $actionCommand);

        foreach ($actionCollection as $action) {
            self::assertContainsOnlyInstancesOf(GreatHealAction::class, [$action]);
            self::assertEquals($actionUnit->getDamage() * 3, $action->getPower());
        }
    }
}
