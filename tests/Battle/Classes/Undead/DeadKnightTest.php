<?php

declare(strict_types=1);

namespace Tests\Battle\Classes\Undead;

use Battle\Action\Damage\HeavyStrikeAction;
use Battle\Classes\UnitClassInterface;
use Battle\Command\CommandException;
use Battle\Command\CommandFactory;
use Battle\Unit\UnitException;
use Exception;
use PHPUnit\Framework\TestCase;
use Tests\Battle\Factory\UnitFactory;

class DeadKnightTest extends TestCase
{
    /**
     * @throws CommandException
     * @throws UnitException
     * @throws Exception
     */
    public function testCreateDeadKnightClass(): void
    {
        $actionUnit = UnitFactory::createByTemplate(8);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $actionCommand = CommandFactory::create([$actionUnit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $warrior = $actionUnit->getClass();

        self::assertEquals(UnitClassInterface::DEAD_KNIGHT_ID, $warrior->getId());
        self::assertEquals(UnitClassInterface::DEAD_KNIGHT_NAME, $warrior->getName());
        self::assertEquals(UnitClassInterface::DEAD_KNIGHT_SMALL_ICON, $warrior->getSmallIcon());

        $actionCollection = $warrior->getAbility($actionUnit, $enemyCommand, $actionCommand);

        foreach ($actionCollection as $action) {
            self::assertContainsOnlyInstancesOf(HeavyStrikeAction::class, [$action]);
            self::assertEquals($actionUnit->getDamage() * 2.5, $action->getPower());
        }
    }
}
