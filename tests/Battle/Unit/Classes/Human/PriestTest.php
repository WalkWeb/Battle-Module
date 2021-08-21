<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Classes\Human;

use Battle\Unit\Classes\UnitClassInterface;
use Battle\Command\CommandException;
use Battle\Command\CommandFactory;
use Battle\Unit\Ability\Heal\GreatHealAbility;
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

        $abilities = $priest->getAbilities($actionUnit);

        foreach ($abilities as $ability) {
            self::assertContainsOnlyInstancesOf(GreatHealAbility::class, [$ability]);

            $actions = $ability->getAction($enemyCommand, $actionCommand);

            foreach ($actions as $action) {
                self::assertEquals($actionUnit->getDamage() * 3, $action->getPower());
            }
        }
    }
}
