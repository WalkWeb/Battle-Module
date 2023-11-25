<?php

declare(strict_types=1);

namespace Tests\Battle\Container;

use Battle\Action\ActionFactory;
use Battle\BattleFactory;
use Battle\Container\Container;
use Battle\Container\ContainerException;
use Battle\Response\Chat\Chat;
use Battle\Response\Chat\ChatInterface;
use Battle\Response\FullLog\FullLog;
use Battle\Response\FullLog\FullLogInterface;
use Battle\Response\Scenario\Scenario;
use Battle\Response\Scenario\ScenarioInterface;
use Battle\Response\Statistic\Statistic;
use Battle\Response\Statistic\StatisticInterface;
use Battle\Round\RoundFactory;
use Battle\Stroke\StrokeFactory;
use Battle\Translation\Translation;
use Battle\Translation\TranslationException;
use Battle\Translation\TranslationInterface;
use Battle\Unit\Ability\AbilityFactory;
use Battle\Unit\Ability\DataProvider\AbilityDataProviderInterface;
use Battle\Unit\Ability\DataProvider\ExampleAbilityDataProvider;
use Battle\Unit\Ability\Description\AbilityDescriptionFactory;
use Battle\Unit\Classes\DataProvider\ClassDataProviderInterface;
use Battle\Unit\Classes\DataProvider\ExampleClassDataProvider;
use Battle\Unit\Classes\UnitClassFactory;
use Battle\Unit\Effect\EffectFactory;
use Battle\Unit\Race\DataProvider\ExampleRaceDataProvider;
use Battle\Unit\Race\DataProvider\RaceDataProviderInterface;
use Battle\Unit\Race\RaceFactory;
use Battle\Unit\Unit;
use Battle\View\ViewFactory;
use stdClass;
use Tests\AbstractUnitTest;

class ContainerTest extends AbstractUnitTest
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

        $chat = $container->get(ChatInterface::class);
        self::assertInstanceOf(Chat::class, $chat);

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

        $fullLog = $container->get(FullLogInterface::class);
        self::assertInstanceOf(FullLog::class, $fullLog);

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
    public function testContainerGetAbilityFactory(): void
    {
        $container = new Container();

        $abilityFactory = $container->get(AbilityFactory::class);
        self::assertInstanceOf(AbilityFactory::class, $abilityFactory);

        $abilityFactory = $container->get('AbilityFactory');
        self::assertInstanceOf(AbilityFactory::class, $abilityFactory);

        $abilityFactory = $container->getAbilityFactory();
        self::assertInstanceOf(AbilityFactory::class, $abilityFactory);
    }

    /**
     * @throws ContainerException
     */
    public function testContainerGetUnitClassFactory(): void
    {
        $container = new Container();

        $unitClassFactory = $container->get(UnitClassFactory::class);
        self::assertInstanceOf(UnitClassFactory::class, $unitClassFactory);

        $unitClassFactory = $container->get('UnitClassFactory');
        self::assertInstanceOf(UnitClassFactory::class, $unitClassFactory);

        $unitClassFactory = $container->getUnitClassFactory();
        self::assertInstanceOf(UnitClassFactory::class, $unitClassFactory);
    }

    /**
     * @throws ContainerException
     */
    public function testContainerGetRaceFactory(): void
    {
        $container = new Container();

        $raceFactory = $container->get(RaceFactory::class);
        self::assertInstanceOf(RaceFactory::class, $raceFactory);

        $raceFactory = $container->get('RaceFactory');
        self::assertInstanceOf(RaceFactory::class, $raceFactory);

        $raceFactory = $container->getRaceFactory();
        self::assertInstanceOf(RaceFactory::class, $raceFactory);
    }

    /**
     * @throws ContainerException
     */
    public function testContainerGetActionFactory(): void
    {
        $container = new Container();

        $actionFactory = $container->get(ActionFactory::class);
        self::assertInstanceOf(ActionFactory::class, $actionFactory);

        $actionFactory = $container->get('ActionFactory');
        self::assertInstanceOf(ActionFactory::class, $actionFactory);

        $actionFactory = $container->getActionFactory();
        self::assertInstanceOf(ActionFactory::class, $actionFactory);
    }

    /**
     * @throws ContainerException
     */
    public function testContainerGetEffectFactory(): void
    {
        $container = new Container();

        $effectFactory = $container->get(EffectFactory::class);
        self::assertInstanceOf(EffectFactory::class, $effectFactory);

        $effectFactory = $container->get('EffectFactory');
        self::assertInstanceOf(EffectFactory::class, $effectFactory);

        $effectFactory = $container->getEffectFactory();
        self::assertInstanceOf(EffectFactory::class, $effectFactory);
    }

    /**
     * @throws ContainerException
     */
    public function testContainerGetAbilityDescriptionFactory(): void
    {
        $container = new Container();

        $abilityDescriptionFactory = $container->get(AbilityDescriptionFactory::class);
        self::assertInstanceOf(AbilityDescriptionFactory::class, $abilityDescriptionFactory);

        $abilityDescriptionFactory = $container->get('AbilityDescriptionFactory');
        self::assertInstanceOf(AbilityDescriptionFactory::class, $abilityDescriptionFactory);

        $abilityDescriptionFactory = $container->getAbilityDescriptionFactory();
        self::assertInstanceOf(AbilityDescriptionFactory::class, $abilityDescriptionFactory);
    }

    /**
     * @throws ContainerException
     */
    public function testContainerGetClassDataProvider(): void
    {
        $container = new Container();

        $dataProvider = $container->get(ClassDataProviderInterface::class);
        self::assertInstanceOf(ExampleClassDataProvider::class, $dataProvider);

        $dataProvider = $container->get('ClassDataProvider');
        self::assertInstanceOf(ExampleClassDataProvider::class, $dataProvider);

        $dataProvider = $container->getClassDataProvider();
        self::assertInstanceOf(ExampleClassDataProvider::class, $dataProvider);
    }

    /**
     * @throws ContainerException
     */
    public function testContainerGetAbilityDataProvider(): void
    {
        $container = new Container();

        $dataProvider = $container->get(AbilityDataProviderInterface::class);
        self::assertInstanceOf(ExampleAbilityDataProvider::class, $dataProvider);

        $dataProvider = $container->get('AbilityDataProvider');
        self::assertInstanceOf(ExampleAbilityDataProvider::class, $dataProvider);

        $dataProvider = $container->getAbilityDataProvider();
        self::assertInstanceOf(ExampleAbilityDataProvider::class, $dataProvider);
    }

    /**
     * @throws ContainerException
     */
    public function testContainerGetRaceDataProvider(): void
    {
        $container = new Container();

        $dataProvider = $container->get(RaceDataProviderInterface::class);
        self::assertInstanceOf(ExampleRaceDataProvider::class, $dataProvider);

        $dataProvider = $container->get(ExampleRaceDataProvider::class);
        self::assertInstanceOf(ExampleRaceDataProvider::class, $dataProvider);

        $dataProvider = $container->get('RaceDataProvider');
        self::assertInstanceOf(ExampleRaceDataProvider::class, $dataProvider);

        $dataProvider = $container->getRaceDataProvider();
        self::assertInstanceOf(ExampleRaceDataProvider::class, $dataProvider);
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

    /**
     * @throws ContainerException
     * @throws TranslationException
     */
    public function testContainerSetSuccess(): void
    {
        $language = 'ru';
        $translator = new Translation($language);

        $container = new Container();
        $container->set(Translation::class, $translator);
        self::assertEquals($translator, $container->getTranslation());

        $container = new Container();
        $container->set('Translation', $translator);
        self::assertEquals($translator, $container->getTranslation());

        $container = new Container();
        $container->set(TranslationInterface::class, $translator);
        self::assertEquals($translator, $container->getTranslation());
    }

    /**
     * @throws ContainerException
     */
    public function testContainerSetFail(): void
    {
        // Создаем какой-то объект
        $invalidService = new StdClass();
        $container = new Container();

        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage(ContainerException::UNKNOWN_SERVICE);

        // Пытаемся добавить его, под именем класса, который отсутствует в Container->map - получаем исключение
        $container->set(Unit::class, $invalidService);
    }

    public function testContainerIsTestMode(): void
    {
        $container = new Container();

        self::assertFalse($container->isTestMode());

        $container = new Container(true);

        self::assertTrue($container->isTestMode());
    }

    /**
     * @throws ContainerException
     */
    public function testContainerGetDamageMultiplier(): void
    {
        $container = new Container();

        self::assertEquals(1, $container->getDamageMultiplier());

        for ($i = 0; $i < 30; $i++) {
            $container->getStatistic()->increasedRound();
        }

        self::assertEquals(2, $container->getDamageMultiplier());

        for ($i = 0; $i < 10; $i++) {
            $container->getStatistic()->increasedRound();
        }

        self::assertEquals(4, $container->getDamageMultiplier());
    }
}
