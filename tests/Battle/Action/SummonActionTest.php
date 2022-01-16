<?php

declare(strict_types=1);

namespace Tests\Battle\Action;

use Battle\Action\ActionException;
use Battle\Action\SummonAction;
use Battle\Command\CommandException;
use Battle\Command\CommandFactory;
use Battle\Unit\UnitException;
use Exception;
use Tests\AbstractUnitTest;
use Tests\Battle\Factory\UnitFactory;

class SummonActionTest extends AbstractUnitTest
{
    /**
     * @throws CommandException
     * @throws UnitException
     * @throws Exception
     */
    public function testCreateSummonAction(): void
    {
        $alliesUnit = UnitFactory::createByTemplate(7);
        $enemyUnit = UnitFactory::createByTemplate(3);
        $alliesCommand = CommandFactory::create([$alliesUnit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        for ($i = 0; $i < 10; $i++) {
            $alliesUnit->newRound();
        }

        $actionCollection = $alliesUnit->getAction($enemyCommand, $alliesCommand);

        foreach ($actionCollection as $action) {
            self::assertContainsOnlyInstancesOf(SummonAction::class, [$action]);
            self::assertTrue($action->canByUsed());
            self::assertEquals('summon', $action->getAnimationMethod());
            self::assertEquals('summon', $action->getMessageMethod());
            $action->handle();
        }

        self::assertCount(2, $alliesCommand->getUnits());
    }

    /**
     * Данный функционал не важен для SummonAction, но для полноценного покрытия кода тестами - делаем
     *
     * @throws CommandException
     * @throws UnitException
     * @throws Exception
     */
    public function testSummonActionSetFactualPower(): void
    {
        $name = 'Summon Zombie';
        $summon = UnitFactory::createByTemplate(17);

        $actionUnit = UnitFactory::createByTemplate(7);
        $enemyUnit = UnitFactory::createByTemplate(3);
        $alliesCommand = CommandFactory::create([$actionUnit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = new SummonAction($actionUnit, $alliesCommand, $enemyCommand, $name, $summon);

        self::assertEquals(0, $action->getFactualPower());

        $action->addFactualPower($actionUnit->getId(), 100);

        // В любом случае будет 0
        self::assertEquals(0, $action->getFactualPower());
    }

    /**
     * @throws Exception
     */
    public function testSummonActionNoPower(): void
    {
        $name = 'Summon Zombie';
        $summon = UnitFactory::createByTemplate(17);

        $actionUnit = UnitFactory::createByTemplate(7);
        $enemyUnit = UnitFactory::createByTemplate(3);
        $alliesCommand = CommandFactory::create([$actionUnit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = new SummonAction($actionUnit, $alliesCommand, $enemyCommand, $name, $summon);

        $this->expectException(ActionException::class);
        $this->expectExceptionMessage('No method: Battle\Action\AbstractAction::Battle\Action\AbstractAction::getPower');
        $action->getPower();
    }
}
