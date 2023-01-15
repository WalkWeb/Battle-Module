<?php

declare(strict_types=1);

namespace Tests\Battle\Action;

use Battle\Action\ParalysisAction;
use Battle\Command\CommandFactory;
use Exception;
use Tests\AbstractUnitTest;
use Tests\Factory\UnitFactory;

class ParalysisActionTest extends AbstractUnitTest
{
    /**
     * Тест на создание ParalysisAction
     *
     * @throws Exception
     */
    public function testParalysisActionCreate(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$enemyUnit]);
        $enemyCommand = CommandFactory::create([$unit]);

        $action = new ParalysisAction(
            $this->getContainer(),
            $unit,
            $enemyCommand,
            $command,
            ParalysisAction::PARALYSIS_MESSAGE_METHOD
        );

        self::assertEquals('', $action->getNameAction());
        self::assertEquals(ParalysisAction::DEFAULT_ANIMATION_METHOD, $action->getAnimationMethod());
        self::assertEquals(ParalysisAction::PARALYSIS_MESSAGE_METHOD, $action->getMessageMethod());
        self::assertEquals($unit, $action->getActionUnit());
        self::assertTrue($action->canByUsed());
    }

    /**
     * Тест на применение ParalysisAction
     *
     * @throws Exception
     */
    public function testParalysisActionApply(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$enemyUnit]);
        $enemyCommand = CommandFactory::create([$unit]);

        // По умолчанию юнит не ходил (в этом раунде)
        self::assertFalse($unit->isAction());

        $action = new ParalysisAction(
            $this->getContainer(),
            $unit,
            $enemyCommand,
            $command,
            ParalysisAction::PARALYSIS_MESSAGE_METHOD
        );

        self::assertTrue($action->canByUsed());

        $action->handle();

        // После применения ParalysisAction юнит отмечается как походивший
        self::assertTrue($unit->isAction());
    }
}
