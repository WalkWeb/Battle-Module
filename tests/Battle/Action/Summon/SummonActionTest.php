<?php

declare(strict_types=1);

namespace Tests\Battle\Action\Summon;

use Battle\Action\Summon\SummonImpAction;
use Battle\Classes\ClassFactoryException;
use Battle\Command\CommandException;
use Battle\Command\CommandFactory;
use Battle\Result\Chat\Message;
use Battle\Unit\UnitException;
use PHPUnit\Framework\TestCase;
use Tests\Battle\Factory\UnitFactory;
use Tests\Battle\Factory\UnitFactoryException;

class SummonActionTest extends TestCase
{
    private const MESSAGE = '<b>unit_7</b> summon Imp';

    /**
     * @throws ClassFactoryException
     * @throws CommandException
     * @throws UnitException
     * @throws UnitFactoryException
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
            self::assertContainsOnlyInstancesOf(SummonImpAction::class, [$action]);
            $message = $action->handle();
        }

        self::assertEquals(self::MESSAGE, $message);
        self::assertCount(2, $alliesCommand->getUnits());
    }

    /**
     * Данный функционал не важен для SummonAction, но для полноценного покрытия кода тестами - делаем
     *
     * @throws ClassFactoryException
     * @throws CommandException
     * @throws UnitException
     * @throws UnitFactoryException
     */
    public function testSummonActionSetFactualPower(): void
    {
        $actionUnit = UnitFactory::createByTemplate(7);
        $enemyUnit = UnitFactory::createByTemplate(3);
        $alliesCommand = CommandFactory::create([$actionUnit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = new SummonImpAction($actionUnit, $alliesCommand, $enemyCommand, new Message());

        self::assertEquals(0, $action->getFactualPower());

        $action->setFactualPower(100);

        // В любом случае будет 0
        self::assertEquals(0, $action->getFactualPower());
    }
}
