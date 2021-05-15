<?php

declare(strict_types=1);

namespace Tests\Battle\Result;

use Battle\Result\Chat\FullLog;
use Battle\Classes\ClassFactoryException;
use Battle\Statistic\Statistic;
use Battle\Unit\UnitException;
use PHPUnit\Framework\TestCase;
use Battle\Result\Result;
use Tests\Battle\Factory\CommandFactory;
use Battle\Result\ResultException;
use Battle\Command\CommandException;
use Tests\Battle\Factory\UnitFactoryException;

class ResultTest extends TestCase
{
    /**
     * @throws ClassFactoryException
     * @throws CommandException
     * @throws ResultException
     * @throws UnitFactoryException
     * @throws UnitException
     */
    public function testCreateResultSuccess(): void
    {
        $leftCommand = CommandFactory::createLeftCommand();
        $rightCommand = CommandFactory::createRightCommand();

        $result = new Result($leftCommand, $rightCommand, $winner = 2, new FullLog(), new Statistic());

        self::assertInstanceOf(Result::class, $result);
        self::assertEquals($leftCommand, $result->getLeftCommand());
        self::assertEquals($rightCommand, $result->getRightCommand());
        self::assertEquals($winner, $result->getWinner());
        self::assertEquals(Result::RIGHT_COMMAND_WIN, $result->getWinnerText());
        self::assertEquals(1, $result->getStatistic()->getRoundNumber());
        self::assertEquals(1, $result->getStatistic()->getStrokeNumber());
        self::assertCount(0, $result->getChat()->getLog());
    }

    /**
     * @throws ClassFactoryException
     * @throws CommandException
     * @throws ResultException
     * @throws UnitException
     * @throws UnitFactoryException
     */
    public function testCreateResultFail(): void
    {
        $leftCommand = CommandFactory::createLeftCommand();
        $rightCommand = CommandFactory::createRightCommand();

        $this->expectException(ResultException::class);
        new Result($leftCommand, $rightCommand, $winner = 3, new FullLog(), new Statistic());
    }
}
