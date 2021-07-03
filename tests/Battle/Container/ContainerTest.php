<?php

declare(strict_types=1);

namespace Tests\Battle\Container;

use Battle\BattleFactory;
use Battle\Container\Container;
use Battle\Container\ContainerException;
use Battle\Result\Chat\Chat;
use Battle\Result\FullLog\FullLog;
use Battle\Result\Scenario\Scenario;
use Battle\Result\Scenario\ScenarioInterface;
use Battle\Result\Statistic\Statistic;
use Battle\Result\Statistic\StatisticInterface;
use Battle\Round\RoundFactory;
use Battle\Stroke\StrokeFactory;
use Battle\Translation\Translation;
use Battle\Translation\TranslationInterface;
use Battle\View\ViewFactory;
use PHPUnit\Framework\TestCase;

class ContainerTest extends TestCase
{
    /**
     * @throws ContainerException
     */
    public function testContainerExistService(): void
    {
        $container = new Container();

        $container->get(StatisticInterface::class);

        self::assertTrue($container->exist(StatisticInterface::class));
        self::assertTrue($container->exist(Statistic::class));
        self::assertTrue($container->exist('Statistic'));
    }

    public function testContainerNoExistService(): void
    {
        $container = new Container();

        self::assertFalse($container->exist(StatisticInterface::class));
        self::assertFalse($container->exist(Statistic::class));
        self::assertFalse($container->exist('Statistic'));

        // no exist unknown service
        self::assertFalse($container->exist('UnknownService'));
    }

    /**
     * @throws ContainerException
     */
    public function testContainerGetStatistic(): void
    {
        $container = new Container();

        $statistics = $container->get(StatisticInterface::class);
        self::assertInstanceOf(StatisticInterface::class, $statistics);

        $statistics = $container->get(Statistic::class);
        self::assertInstanceOf(StatisticInterface::class, $statistics);

        $statistics = $container->get('Statistic');
        self::assertInstanceOf(StatisticInterface::class, $statistics);
    }

    /**
     * @throws ContainerException
     */
    public function testContainerGetChat(): void
    {
        $container = new Container();

        $chat = $container->get(Chat::class);
        self::assertInstanceOf(Chat::class, $chat);

        $chat = $container->get('Chat');
        self::assertInstanceOf(Chat::class, $chat);
    }

    /**
     * @throws ContainerException
     */
    public function testContainerGetTranslation(): void
    {
        $container = new Container();

        $translation = $container->get(TranslationInterface::class);
        self::assertInstanceOf(TranslationInterface::class, $translation);

        $translation = $container->get(Translation::class);
        self::assertInstanceOf(TranslationInterface::class, $translation);

        $translation = $container->get('Translation');
        self::assertInstanceOf(TranslationInterface::class, $translation);
    }

    /**
     * @throws ContainerException
     */
    public function testContainerGetScenario(): void
    {
        $container = new Container();

        $scenario = $container->get(ScenarioInterface::class);
        self::assertInstanceOf(ScenarioInterface::class, $scenario);

        $scenario = $container->get(Scenario::class);
        self::assertInstanceOf(ScenarioInterface::class, $scenario);

        $scenario = $container->get('Scenario');
        self::assertInstanceOf(ScenarioInterface::class, $scenario);
    }

    /**
     * @throws ContainerException
     */
    public function testContainerGetFullLog(): void
    {
        $container = new Container();

        $fullLog = $container->get(FullLog::class);
        self::assertInstanceOf(FullLog::class, $fullLog);

        $fullLog = $container->get('FullLog');
        self::assertInstanceOf(FullLog::class, $fullLog);
    }

    /**
     * @throws ContainerException
     */
    public function testContainerGetBattleFactory(): void
    {
        $container = new Container();

        $battleFactory = $container->get(BattleFactory::class);
        self::assertInstanceOf(BattleFactory::class, $battleFactory);

        $battleFactory = $container->get('BattleFactory');
        self::assertInstanceOf(BattleFactory::class, $battleFactory);

        $battleFactory = $container->getBattleFactory();
        self::assertInstanceOf(BattleFactory::class, $battleFactory);
    }

    /**
     * @throws ContainerException
     */
    public function testContainerGetRoundFactory(): void
    {
        $container = new Container();

        $roundFactory = $container->get(RoundFactory::class);
        self::assertInstanceOf(RoundFactory::class, $roundFactory);

        $roundFactory = $container->get('RoundFactory');
        self::assertInstanceOf(RoundFactory::class, $roundFactory);
    }

    /**
     * @throws ContainerException
     */
    public function testContainerGetStrokeFactory(): void
    {
        $container = new Container();

        $strokeFactory = $container->get(StrokeFactory::class);
        self::assertInstanceOf(StrokeFactory::class, $strokeFactory);

        $strokeFactory = $container->get('StrokeFactory');
        self::assertInstanceOf(StrokeFactory::class, $strokeFactory);
    }

    /**
     * @throws ContainerException
     */
    public function testContainerGetViewFactory(): void
    {
        $container = new Container();

        $viewFactory = $container->get(ViewFactory::class);
        self::assertInstanceOf(ViewFactory::class, $viewFactory);

        $viewFactory = $container->get('ViewFactory');
        self::assertInstanceOf(ViewFactory::class, $viewFactory);
    }

    /**
     * @throws ContainerException
     */
    public function testContainerUnknownService(): void
    {
        $container = new Container();

        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage(ContainerException::UNKNOWN_SERVICE);
        $container->get('UnknownService');
    }
}
