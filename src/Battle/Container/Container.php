<?php

declare(strict_types=1);

namespace Battle\Container;

use Battle\BattleFactory;
use Battle\Result\Chat\Chat;
use Battle\Result\Chat\ChatInterface;
use Battle\Result\FullLog\FullLog;
use Battle\Result\FullLog\FullLogInterface;
use Battle\Result\Scenario\Scenario;
use Battle\Result\Scenario\ScenarioInterface;
use Battle\Result\Statistic\Statistic;
use Battle\Result\Statistic\StatisticInterface;
use Battle\Round\RoundFactory;
use Battle\Stroke\StrokeFactory;
use Battle\Translation\Translation;
use Battle\Translation\TranslationInterface;
use Battle\Unit\Ability\DataProvider\AbilityDataProviderInterface;
use Battle\Unit\Ability\DataProvider\ExampleAbilityDataProvider;
use Battle\Unit\Classes\DataProvider\ClassDataProviderInterface;
use Battle\Unit\Classes\DataProvider\ExampleClassDataProvider;
use Battle\View\ViewFactory;

class Container implements ContainerInterface
{
    private $map = [
        StatisticInterface::class           => Statistic::class,
        Statistic::class                    => Statistic::class,
        'Statistic'                         => Statistic::class,

        ChatInterface::class                => Chat::class,
        Chat::class                         => Chat::class,
        'Chat'                              => Chat::class,

        TranslationInterface::class         => Translation::class,
        Translation::class                  => Translation::class,
        'Translation'                       => Translation::class,

        ScenarioInterface::class            => Scenario::class,
        Scenario::class                     => Scenario::class,
        'Scenario'                          => Scenario::class,

        FullLogInterface::class             => FullLog::class,
        FullLog::class                      => FullLog::class,
        'FullLog'                           => FullLog::class,

        BattleFactory::class                => BattleFactory::class,
        'BattleFactory'                     => BattleFactory::class,

        RoundFactory::class                 => RoundFactory::class,
        'RoundFactory'                      => RoundFactory::class,

        StrokeFactory::class                => StrokeFactory::class,
        'StrokeFactory'                     => StrokeFactory::class,

        ViewFactory::class                  => ViewFactory::class,
        'ViewFactory'                       => ViewFactory::class,

        // Исключительно как примеры поставщиков. Подразумевается, что в реальном проекте эти поставщики будут подменены
        // через метод в контейнере set()
        ExampleClassDataProvider::class     => ExampleClassDataProvider::class,
        ClassDataProviderInterface::class   => ExampleClassDataProvider::class,
        'ClassDataProvider'                 => ExampleClassDataProvider::class,

        ExampleAbilityDataProvider::class   => ExampleAbilityDataProvider::class,
        AbilityDataProviderInterface::class => ExampleAbilityDataProvider::class,
        'AbilityDataProvider'               => ExampleAbilityDataProvider::class,
    ];

    private $storage = [];

    private $testMode;

    public function __construct(bool $testMode = false)
    {
        $this->testMode = $testMode;
    }

    /**
     * @param string $id
     * @return object
     * @throws ContainerException
     */
    public function get(string $id): object
    {
        $class = $this->getNameService($id);

        if ($this->exist($class)) {
            return $this->storage[$class];
        }

        return $this->create($class);
    }

    /**
     * @param string $id
     * @param object $object
     * @throws ContainerException
     */
    public function set(string $id, object $object): void
    {
        $id = $this->getNameService($id);
        $this->storage[$id] = $object;
    }

    /**
     * @param string $class
     * @return bool
     */
    public function exist(string $class): bool
    {
        try {
            $class = $this->getNameService($class);
            return array_key_exists($class, $this->storage);
        } catch (ContainerException $e) {
            // Контейнер может иметь только фиксированный набор сервисов. Если указан неизвестный - значит он не может
            // быть добавлен.
            return false;
        }
    }

    /**
     * @return bool
     */
    public function isTestMode(): bool
    {
        return $this->testMode;
    }

    /**
     * @return StatisticInterface
     * @throws ContainerException
     */
    public function getStatistic(): StatisticInterface
    {
        /** @var StatisticInterface $service */
        $service = $this->get(StatisticInterface::class);
        return $service;
    }

    /**
     * @return ChatInterface
     * @throws ContainerException
     */
    public function getChat(): ChatInterface
    {
        /** @var ChatInterface $service */
        $service = $this->get(Chat::class);
        return $service;
    }

    /**
     * @return TranslationInterface
     * @throws ContainerException
     */
    public function getTranslation(): TranslationInterface
    {
        /** @var TranslationInterface $service */
        $service = $this->get(TranslationInterface::class);
        return $service;
    }

    /**
     * @return ScenarioInterface
     * @throws ContainerException
     */
    public function getScenario(): ScenarioInterface
    {
        /** @var ScenarioInterface $service */
        $service = $this->get(ScenarioInterface::class);
        return $service;
    }

    /**
     * @return FullLogInterface
     * @throws ContainerException
     */
    public function getFullLog(): FullLogInterface
    {
        /** @var FullLogInterface $service */
        $service = $this->get(FullLogInterface::class);
        return $service;
    }

    /**
     * @return BattleFactory
     * @throws ContainerException
     */
    public function getBattleFactory(): BattleFactory
    {
        /** @var BattleFactory $service */
        $service = $this->get(BattleFactory::class);
        return $service;
    }

    /**
     * @return RoundFactory
     * @throws ContainerException
     */
    public function getRoundFactory(): RoundFactory
    {
        /** @var RoundFactory $service */
        $service = $this->get(RoundFactory::class);
        return $service;
    }

    /**
     * @return StrokeFactory
     * @throws ContainerException
     */
    public function getStrokeFactory(): StrokeFactory
    {
        /** @var StrokeFactory $service */
        $service = $this->get(StrokeFactory::class);
        return $service;
    }

    /**
     * @return ViewFactory
     * @throws ContainerException
     */
    public function getViewFactory(): ViewFactory
    {
        /** @var ViewFactory $service */
        $service = $this->get(ViewFactory::class);
        return $service;
    }

    /**
     * @return ClassDataProviderInterface
     * @throws ContainerException
     */
    public function getClassDataProvider(): ClassDataProviderInterface
    {
        /** @var ClassDataProviderInterface $service */
        $service = $this->get(ClassDataProviderInterface::class);
        return $service;
    }

    /**
     * @return AbilityDataProviderInterface
     * @throws ContainerException
     */
    public function getAbilityDataProvider(): AbilityDataProviderInterface
    {
        /** @var AbilityDataProviderInterface $service */
        $service = $this->get(AbilityDataProviderInterface::class);
        return $service;
    }

    /**
     * @param string $class
     * @return object
     */
    private function create(string $class): object
    {
        // Некоторые сервисы требуют передачу контейнера в конструктор
        if ($class === Chat::class || $class === ExampleClassDataProvider::class) {
            $object = new $class($this);
            $this->storage[$this->map[$class]] = $object;
            return $object;
        }

        $object = new $class;
        $this->storage[$this->map[$class]] = $object;

        return $object;
    }

    /**
     * @param string $id
     * @return string
     * @throws ContainerException
     */
    private function getNameService(string $id): string
    {
        if (!array_key_exists($id, $this->map)) {
            throw new ContainerException(ContainerException::UNKNOWN_SERVICE);
        }

        return $this->map[$id];
    }
}
