<?php

declare(strict_types=1);

namespace Tests\Battle\Response;

use Battle\Container\Container;
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
        $container = new Container(true);
        $leftCommand = CommandFactory::createLeftCommand();
        $rightCommand = CommandFactory::createRightCommand();
        $chat = new Chat($this->container);
        $translation = new Translation();
        $scenario = new Scenario();

        $response = new Response($leftCommand, $rightCommand, $leftCommand, $rightCommand, $winner = 2, $container);

        self::assertInstanceOf(Response::class, $response);
        self::assertEquals($leftCommand, $response->getStartLeftCommand());
        self::assertEquals($rightCommand, $response->getStartRightCommand());
        self::assertEquals($leftCommand, $response->getEndLeftCommand());
        self::assertEquals($rightCommand, $response->getEndRightCommand());
        self::assertEquals($winner, $response->getWinner());
        self::assertEquals(Response::RIGHT_COMMAND_WIN, $response->getWinnerText());
        self::assertEquals(1, $response->getStatistic()->getRoundNumber());
        self::assertEquals(1, $response->getStatistic()->getStrokeNumber());
        self::assertCount(0, $response->getFullLog()->getLog());
        self::assertEquals($chat, $response->getChat());
        self::assertEquals($translation, $response->getTranslation());
        self::assertEquals($scenario, $response->getScenario());
        self::assertEquals($container, $response->getContainer());
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
