<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Classes\Bosses;

use Battle\Command\CommandFactory;
use Battle\Unit\Ability\Damage\HellfireAbility;
use Exception;
use Tests\AbstractUnitTest;
use Tests\Battle\Factory\UnitFactory;

class WardenTest extends AbstractUnitTest
{
    /**
     * @throws Exception
     */
    public function testCreateWardenClass(): void
    {
        $unit = UnitFactory::createByTemplate(27);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $actionCommand = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $warden = $unit->getClass();

        self::assertEquals(9, $warden->getId());
        self::assertEquals('Warden', $warden->getName());
        self::assertEquals('/images/icons/small/base-inferno.png', $warden->getSmallIcon());

        $abilities = $warden->getAbilities($unit);

        foreach ($abilities as $ability) {
            self::assertContainsOnlyInstancesOf(HellfireAbility::class, [$ability]);

            $actions = $ability->getAction($enemyCommand, $actionCommand);

            foreach ($actions as $action) {
                self::assertEquals((int)($unit->getDamage() * 1.5), $action->getPower());
            }
        }
    }


    /**
     * @throws Exception
     */
    public function testWardenReadyAbility(): void
    {
        $unit = UnitFactory::createByTemplate(27);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        for ($i = 0; $i < 30; $i++) {
            $unit->newRound();
        }

        foreach ($unit->getAbilities() as $ability) {
            self::assertTrue($ability->isReady());
            self::assertTrue($ability->canByUsed($enemyCommand, $command));
        }
    }
}
