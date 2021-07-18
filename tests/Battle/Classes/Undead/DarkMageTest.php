<?php

declare(strict_types=1);

namespace Tests\Battle\Classes\Undead;

use Battle\Classes\UnitClassInterface;
use Battle\Command\CommandException;
use Battle\Command\CommandFactory;
use Battle\Unit\Ability\Summon\SummonSkeletonAbility;
use Battle\Unit\UnitException;
use Exception;
use PHPUnit\Framework\TestCase;
use Tests\Battle\Factory\UnitFactory;

class DarkMageTest extends TestCase
{
    /**
     * @throws CommandException
     * @throws UnitException
     * @throws Exception
     */
    public function testCreateDarkMageClass(): void
    {
        $actionUnit = UnitFactory::createByTemplate(7);
        $enemyUnit = UnitFactory::createByTemplate(1);
        $actionCommand = CommandFactory::create([$actionUnit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $darkMage = $actionUnit->getClass();

        self::assertEquals(UnitClassInterface::DARK_MAGE_ID, $darkMage->getId());
        self::assertEquals(UnitClassInterface::DARK_MAGE_NAME, $darkMage->getName());
        self::assertEquals(UnitClassInterface::DARK_MAGE_SMALL_ICON, $darkMage->getSmallIcon());

        $abilities = $darkMage->getAbilities($actionUnit);

        foreach ($abilities as $ability) {
            self::assertContainsOnlyInstancesOf(SummonSkeletonAbility::class, [$ability]);

            $actions = $ability->getAction($enemyCommand, $actionCommand);

            foreach ($actions as $action) {
                $action->handle();
            }
        }

        // Размер команды увеличился на 1 юнита
        self::assertCount(2, $actionCommand->getUnits());
    }
}
