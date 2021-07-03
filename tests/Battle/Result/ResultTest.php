<?php

declare(strict_types=1);

namespace Tests\Battle\Result;

use Exception;
use Battle\Container\Container;
use Battle\Result\Chat\Chat;
use Battle\Result\Scenario\Scenario;
use Battle\Translation\Translation;
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
        $chat = new Chat();
        $translation = new Translation();
        $scenario = new Scenario();

        $result = new Result($leftCommand, $rightCommand, $leftCommand, $rightCommand, $winner = 2, new Container());

        self::assertInstanceOf(Result::class, $result);
        self::assertEquals($leftCommand, $result->getStartLeftCommand());
        self::assertEquals($rightCommand, $result->getStartRightCommand());
        self::assertEquals($leftCommand, $result->getEndLeftCommand());
        self::assertEquals($rightCommand, $result->getEndRightCommand());
        self::assertEquals($winner, $result->getWinner());
        self::assertEquals(Result::RIGHT_COMMAND_WIN, $result->getWinnerText());
        self::assertEquals(1, $result->getStatistic()->getRoundNumber());
        self::assertEquals(1, $result->getStatistic()->getStrokeNumber());
        self::assertCount(0, $result->getFullLog()->getLog());
        self::assertEquals($chat, $result->getChat());
        self::assertEquals($translation, $result->getTranslation());
        self::assertEquals($scenario, $result->getScenario());
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
        new Result($leftCommand, $rightCommand, $leftCommand, $rightCommand, $winner = 3, new Container());
    }
}
