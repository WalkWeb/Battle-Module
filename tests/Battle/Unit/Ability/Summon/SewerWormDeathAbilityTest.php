<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Ability\Summon;

use Battle\Action\SummonAction;
use Battle\Command\CommandFactory;
use Battle\Unit\Ability\AbilityInterface;
use Exception;
use Tests\AbstractUnitTest;
use Tests\Factory\UnitFactory;

class SewerWormDeathAbilityTest extends AbstractUnitTest
{
    /**
     * @throws Exception
     */
    public function testSummonSewerWormDeathUse(): void
    {
        $name = 'Sewer Worm Death';
        $icon = '/images/icons/ability/170.png';

        $unit = UnitFactory::createByTemplate(55);
        $enemyUnit = UnitFactory::createByTemplate(12);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = $this->getAbility($unit, $name);

        self::assertEquals($name, $ability->getName());
        self::assertEquals($icon, $ability->getIcon());
        self::assertEquals($unit, $ability->getUnit());
        self::assertFalse($ability->isReady());
        self::assertTrue($ability->canByUsed($enemyCommand, $command));
        self::assertTrue($ability->isDisposable());
        self::assertFalse($ability->isUsage());
        self::assertEquals(AbilityInterface::ACTIVATE_DEAD, $ability->getTypeActivate());

        // Убиваем юнита и активируем способность
        $damageActions = $enemyUnit->getActions($command, $enemyCommand);

        self::assertCount(1, $damageActions);

        foreach ($damageActions as $damageAction) {
            self::assertTrue($damageAction->canByUsed());
            $damageAction->handle();
        }

        self::assertEquals(0, $unit->getLife());

        $abilities = $unit->getDeadAbilities();

        self::assertCount(1, $abilities);

        foreach ($abilities as $ability) {
            self::assertEquals($name, $ability->getName());
            self::assertEquals($icon, $ability->getIcon());
            self::assertEquals($unit, $ability->getUnit());

            $actions = $ability->getActions($enemyCommand, $command);

            self::assertCount(2, $actions);

            foreach ($actions as $action) {
                self::assertInstanceOf(SummonAction::class, $action);
                self::assertEquals('Part of Worm', $action->getSummonUnit()->getName());
                self::assertEquals('/images/avas/monsters/010.png', $action->getSummonUnit()->getAvatar());
                self::assertEquals(4, $action->getSummonUnit()->getLevel());
            }
        }
    }
}
