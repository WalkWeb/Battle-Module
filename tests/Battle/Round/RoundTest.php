<?php

declare(strict_types=1);

namespace Tests\Battle\Round;

use Exception;
use Battle\Round\Round;
use Battle\Round\RoundException;
use Battle\Command\CommandFactory;
use Tests\AbstractUnitTest;
use Tests\Factory\CommandFactory as TestCommandFactory;
use Tests\Factory\UnitFactory;

class RoundTest extends AbstractUnitTest
{
    /**
     * Проверяем корректную смену действующий команды
     *
     * @throws Exception
     */
    public function testRoundNextCommand(): void
    {
        $command = TestCommandFactory::createLeftCommand();
        $enemyCommand = TestCommandFactory::createRightCommand();
        $startCommand = 1;
        // Юниты делают по ходу, и на следующий раунд атаковать будет та же команда
        $nextCommand = 1;
        // Юниты делают по одному ходу, соответственно следующий, после раунда, ход будет 3
        $nextNumberStroke = 3;

        $round = new Round($command, $enemyCommand, $startCommand, $this->container);
        self::assertEquals($nextCommand, $round->handle());
        self::assertEquals($nextNumberStroke, $round->getStatistics()->getStrokeNumber());
    }

    /**
     * Проверяем корректную смену действующий когда в действующей команде нет юнитов, способных совершить действие
     *
     * @throws Exception
     */
    public function testRoundNextCommandNoAction(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $unit->madeAction();
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $startCommand = 1;
        // Походит правая команда, и следующей будет ходить опять левая команда
        $nextCommand = 1;
        // В этом раунде походит только юнит из правой команды, соответственно счетчик увеличится только на 1
        $nextNumberStroke = 2;

        $round = new Round($command, $enemyCommand, $startCommand, $this->container);
        self::assertEquals($nextCommand, $round->handle());
        self::assertEquals($nextNumberStroke, $round->getStatistics()->getStrokeNumber());
    }

    /**
     * @throws Exception
     */
    public function testRoundIncorrectActionCommand(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $unit->madeAction();
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);
        $startCommand = 3;

        $this->expectException(RoundException::class);
        $this->expectExceptionMessage(RoundException::INCORRECT_START_COMMAND);
        new Round($command, $enemyCommand, $startCommand, $this->container);
    }

    /**
     * @throws Exception
     */
    public function testRoundLimitStroke(): void
    {
        $command = TestCommandFactory::createVeryBigCommand();
        $enemyCommand = TestCommandFactory::createVeryBigCommand();

        $round = new Round($command, $enemyCommand, 1, $this->container);

        $this->expectException(RoundException::class);
        $this->expectExceptionMessage(RoundException::UNEXPECTED_ENDING);

        $round->handle();
    }
}
