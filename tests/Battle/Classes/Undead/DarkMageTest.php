<?php

declare(strict_types=1);

namespace Tests\Battle\Classes\Undead;

use Battle\Action\Summon\SummonAction;
use Battle\Classes\ClassFactoryException;
use Battle\Classes\UnitClassInterface;
use Battle\Command\CommandException;
use Battle\Command\CommandFactory;
use Battle\Unit\UnitException;
use PHPUnit\Framework\TestCase;
use Tests\Battle\Factory\UnitFactory;
use Tests\Battle\Factory\UnitFactoryException;

class DarkMageTest extends TestCase
{
    /**
     * @throws ClassFactoryException
     * @throws CommandException
     * @throws UnitException
     * @throws UnitFactoryException
     */
    public function testCreateDarkMageClass(): void
    {
        $actionUnit = UnitFactory::createByTemplate(7);
        $enemyUnit = UnitFactory::createByTemplate(1);
        $actionCommand = CommandFactory::create([$actionUnit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $darkMage = $actionUnit->getClass();

        self::assertEquals(UnitClassInterface::DARK_MAGE, $darkMage->getId());
        self::assertEquals(UnitClassInterface::DARK_MAGE_SMALL_ICON, $darkMage->getSmallIcon());

        $actionCollection = $darkMage->getAbility($actionUnit, $enemyCommand, $actionCommand);

        foreach ($actionCollection as $action) {
            self::assertContainsOnlyInstancesOf(SummonAction::class, [$action]);
            $action->handle();
        }

        // Размер команды увеличился на 1 юнита
        self::assertCount(2, $actionCommand->getUnits());
    }
}
