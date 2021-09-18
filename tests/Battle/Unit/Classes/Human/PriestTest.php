<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Classes\Human;

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
        $unit = UnitFactory::createByTemplate(5);
        $enemyUnit = UnitFactory::createByTemplate(1);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $priest = $unit->getClass();

        self::assertEquals(2, $priest->getId());
        self::assertEquals('Priest', $priest->getName());
        self::assertEquals('/images/icons/small/priest.png', $priest->getSmallIcon());

        $abilities = $priest->getAbilities($unit);

        foreach ($abilities as $ability) {
            self::assertContainsOnlyInstancesOf(GreatHealAbility::class, [$ability]);

            $actions = $ability->getAction($enemyCommand, $command);

            foreach ($actions as $action) {
                self::assertEquals($unit->getDamage() * 3, $action->getPower());
            }
        }
    }
}
