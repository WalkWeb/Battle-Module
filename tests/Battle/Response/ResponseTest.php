<?php

declare(strict_types=1);

namespace Tests\Battle\Response;

use Exception;
use Battle\Response\Chat\Chat;
use Battle\Response\Scenario\Scenario;
use Battle\Translation\Translation;
use Battle\Response\Response;
use Tests\AbstractUnitTest;
use Tests\Factory\CommandFactory;
use Battle\Response\ResponseException;

class ResponseTest extends AbstractUnitTest
{
    /**
     * @throws ResponseException
     * @throws Exception
     */
    public function testCreateResultSuccess(): void
    {
        $leftCommand = CommandFactory::createLeftCommand();
        $rightCommand = CommandFactory::createRightCommand();
        $chat = new Chat($this->container);
        $translation = new Translation();
        $scenario = new Scenario();

        $result = new Response($leftCommand, $rightCommand, $leftCommand, $rightCommand, $winner = 2, $this->container);

        self::assertInstanceOf(Response::class, $result);
        self::assertEquals($leftCommand, $result->getStartLeftCommand());
        self::assertEquals($rightCommand, $result->getStartRightCommand());
        self::assertEquals($leftCommand, $result->getEndLeftCommand());
        self::assertEquals($rightCommand, $result->getEndRightCommand());
        self::assertEquals($winner, $result->getWinner());
        self::assertEquals(Response::RIGHT_COMMAND_WIN, $result->getWinnerText());
        self::assertEquals(1, $result->getStatistic()->getRoundNumber());
        self::assertEquals(1, $result->getStatistic()->getStrokeNumber());
        self::assertCount(0, $result->getFullLog()->getLog());
        self::assertEquals($chat, $result->getChat());
        self::assertEquals($translation, $result->getTranslation());
        self::assertEquals($scenario, $result->getScenario());
    }

    /**
     * @throws ResponseException
     * @throws Exception
     */
    public function testCreateResultFail(): void
    {
        $leftCommand = CommandFactory::createLeftCommand();
        $rightCommand = CommandFactory::createRightCommand();

        $this->expectException(ResponseException::class);
        new Response($leftCommand, $rightCommand, $leftCommand, $rightCommand, $winner = 3, $this->container);
    }
}
