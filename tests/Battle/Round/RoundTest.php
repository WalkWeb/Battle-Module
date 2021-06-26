<?php

declare(strict_types=1);

namespace Tests\Battle\Round;

use Exception;
use Battle\Result\Chat\Chat;
use Battle\Result\FullLog\FullLog;
use Battle\Command\CommandFactory;
use Battle\Result\Scenario\Scenario;
use Battle\Round\RoundException;
use Battle\Round\Round;
use Battle\Statistic\Statistic;
use PHPUnit\Framework\TestCase;
use Tests\Battle\Factory\CommandFactory as TestCommandFactory;
use Tests\Battle\Factory\UnitFactory;

class RoundTest extends TestCase
{
    /**
     * Проверяем корректную смену действующий команды
     *
     * @throws Exception
     */
    public function testRoundNextCommand(): void
    {
        $leftCommand = TestCommandFactory::createLeftCommand();
        $rightCommand = TestCommandFactory::createRightCommand();
        $startCommand = 1;
        // Юниты делают по ходу, и на следующий раунд атаковать будет таже команда
        $nextCommand = 1;
        // Юниты делают по одному ходу, соответственно следующий, после раунда, ход будет 3
        $nextNumberStroke = 3;

        $round = new Round($leftCommand, $rightCommand, $startCommand, new Statistic(), new FullLog(), new Chat(), new Scenario());
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
        $leftUnit = UnitFactory::createByTemplate(1);
        $rightUnit = UnitFactory::createByTemplate(2);
        $leftUnit->madeAction();
        $leftCommand = CommandFactory::create([$leftUnit]);
        $rightCommand = CommandFactory::create([$rightUnit]);

        $startCommand = 1;
        // Походит правая команда, и следующей будет ходить опять левая команда
        $nextCommand = 1;
        // В этом раунде походит только юнит из правой команды, соответственно счетчик увеличится только на 1
        $nextNumberStroke = 2;

        $round = new Round($leftCommand, $rightCommand, $startCommand, new Statistic(), new FullLog(), new Chat(), new Scenario());
        self::assertEquals($nextCommand, $round->handle());
        self::assertEquals($nextNumberStroke, $round->getStatistics()->getStrokeNumber());
    }

    /**
     * @throws Exception
     */
    public function testRoundIncorrectActionCommand(): void
    {
        $leftUnit = UnitFactory::createByTemplate(1);
        $rightUnit = UnitFactory::createByTemplate(2);
        $leftUnit->madeAction();
        $leftCommand = CommandFactory::create([$leftUnit]);
        $rightCommand = CommandFactory::create([$rightUnit]);
        $startCommand = 3;

        $this->expectException(RoundException::class);
        $this->expectExceptionMessage(RoundException::INCORRECT_START_COMMAND);
        new Round($leftCommand, $rightCommand, $startCommand, new Statistic(), new FullLog(), new Chat(), new Scenario());
    }

    /**
     * @throws Exception
     */
    public function testRoundLimitStroke(): void
    {
        $leftCommand = TestCommandFactory::createVeryBigCommand();
        $rightCommand = TestCommandFactory::createVeryBigCommand();

        $round = new Round($leftCommand, $rightCommand, 1, new Statistic(), new FullLog(), new Chat(), new Scenario());

        $this->expectException(RoundException::class);
        $this->expectExceptionMessage(RoundException::UNEXPECTED_ENDING);

        $round->handle();
    }
}
