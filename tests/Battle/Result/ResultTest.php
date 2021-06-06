<?php

declare(strict_types=1);

namespace Tests\Battle\Result;

use Battle\Result\Chat\Chat;
use Battle\Result\FullLog\FullLog;
use Battle\Statistic\Statistic;
use Battle\Translation\Translation;
use Exception;
use PHPUnit\Framework\TestCase;
use Battle\Result\Result;
use Tests\Battle\Factory\CommandFactory;
use Battle\Result\ResultException;

class ResultTest extends TestCase
{
    /**
     * @throws ResultException
     * @throws Exception
     */
    public function testCreateResultSuccess(): void
    {
        $leftCommand = CommandFactory::createLeftCommand();
        $rightCommand = CommandFactory::createRightCommand();

        $result = new Result($leftCommand, $rightCommand, $winner = 2, new FullLog(), new Chat(), new Statistic(), new Translation());

        self::assertInstanceOf(Result::class, $result);
        self::assertEquals($leftCommand, $result->getLeftCommand());
        self::assertEquals($rightCommand, $result->getRightCommand());
        self::assertEquals($winner, $result->getWinner());
        self::assertEquals(Result::RIGHT_COMMAND_WIN, $result->getWinnerText());
        self::assertEquals(1, $result->getStatistic()->getRoundNumber());
        self::assertEquals(1, $result->getStatistic()->getStrokeNumber());
        self::assertCount(0, $result->getFullLog()->getLog());
        self::assertEquals(new Chat(), $result->getChat());
        self::assertEquals(new Translation(), $result->getTranslation());
    }

    /**
     * @throws ResultException
     * @throws Exception
     */
    public function testCreateResultFail(): void
    {
        $leftCommand = CommandFactory::createLeftCommand();
        $rightCommand = CommandFactory::createRightCommand();

        $this->expectException(ResultException::class);
        new Result($leftCommand, $rightCommand, $winner = 3, new FullLog(), new Chat(), new Statistic(), new Translation());
    }
}
