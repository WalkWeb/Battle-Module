<?php

declare(strict_types=1);

namespace Tests\Battle\Action;

use Battle\Action\ActionException;
use Battle\Action\SummonAction;
use Battle\Command\CommandException;
use Battle\Command\CommandFactory;
use Battle\Result\Chat\Message\Message;
use Battle\Unit\UnitException;
use Exception;
use PHPUnit\Framework\TestCase;
use Tests\Battle\Factory\UnitFactory;

class SummonActionTest extends TestCase
{
    private const MESSAGE = '<span style="color: #1e72e3">unit_7</span> summon Skeleton';

    /**
     * @throws CommandException
     * @throws UnitException
     * @throws Exception
     */
    public function testCreateSummonAction(): void
    {
        $message = '';
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
            $message .= $action->handle();
        }

        self::assertEquals(self::MESSAGE, $message);
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

        $action = new SummonAction($actionUnit, $alliesCommand, $enemyCommand, new Message(), $name, $summon);

        self::assertEquals(0, $action->getFactualPower());

        $action->setFactualPower(100);

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

        $action = new SummonAction($actionUnit, $alliesCommand, $enemyCommand, new Message(), $name, $summon);

        $this->expectException(ActionException::class);
        $this->expectExceptionMessage('No method: Battle\Action\AbstractAction::Battle\Action\AbstractAction::getPower');
        $action->getPower();
    }
}
