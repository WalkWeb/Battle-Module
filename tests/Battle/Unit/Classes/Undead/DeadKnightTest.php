<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Classes\Undead;

use Battle\Unit\Classes\UnitClassInterface;
use Battle\Command\CommandException;
use Battle\Command\CommandFactory;
use Battle\Unit\Ability\Damage\HeavyStrikeAbility;
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

        $deadKnight = $actionUnit->getClass();

        self::assertEquals(UnitClassInterface::DEAD_KNIGHT_ID, $deadKnight->getId());
        self::assertEquals(UnitClassInterface::DEAD_KNIGHT_NAME, $deadKnight->getName());
        self::assertEquals(UnitClassInterface::DEAD_KNIGHT_SMALL_ICON, $deadKnight->getSmallIcon());

        $abilities = $deadKnight->getAbilities($actionUnit);

        foreach ($abilities as $ability) {
            self::assertContainsOnlyInstancesOf(HeavyStrikeAbility::class, [$ability]);

            $actions = $ability->getAction($enemyCommand, $actionCommand);

            foreach ($actions as $action) {
                self::assertEquals((int)($actionUnit->getDamage() * 2.5), $action->getPower());
            }
        }
    }
}
