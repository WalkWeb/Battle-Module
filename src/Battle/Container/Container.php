<?php

declare(strict_types=1);

namespace Battle\Container;

use Battle\BattleFactory;
use Battle\Result\Chat\Chat;
use Battle\Result\Chat\ChatInterface;
use Battle\Result\Chat\Message\Message;
use Battle\Result\Chat\Message\MessageInterface;
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
use Battle\View\ViewFactory;

class Container implements ContainerInterface
{
    private $map = [
        StatisticInterface::class   => Statistic::class,
        Statistic::class            => Statistic::class,
        'Statistic'                 => Statistic::class,
        ChatInterface::class        => Chat::class,
        Chat::class                 => Chat::class,
        'Chat'                      => Chat::class,
        TranslationInterface::class => Translation::class,
        Translation::class          => Translation::class,
        'Translation'               => Translation::class,
        ScenarioInterface::class    => Scenario::class,
        Scenario::class             => Scenario::class,
        'Scenario'                  => Scenario::class,
        FullLogInterface::class     => FullLog::class,
        FullLog::class              => FullLog::class,
        'FullLog'                   => FullLog::class,
        MessageInterface::class     => Message::class,
        Message::class              => Message::class,
        'Message'                   => Message::class,
        BattleFactory::class        => BattleFactory::class,
        'BattleFactory'             => BattleFactory::class,
        RoundFactory::class         => RoundFactory::class,
        'RoundFactory'              => RoundFactory::class,
        StrokeFactory::class        => StrokeFactory::class,
        'StrokeFactory'             => StrokeFactory::class,
        ViewFactory::class          => ViewFactory::class,
        'ViewFactory'               => ViewFactory::class,
    ];

    private $storage = [];

    /**
     * @param string $id
     * @return object
     * @throws ContainerException
     */
    public function get(string $id): object
    {
        $id = $this->normalizeIdService($id);

        if ($this->exist($id)) {
            return $this->storage[$id];
        }

        return $this->create($id);
    }

    /**
     * @param string $id
     * @param object $object
     * @throws ContainerException
     */
    public function set(string $id, object $object): void
    {
        $id = $this->normalizeIdService($id);
        $this->storage[$id] = $object;
    }

    /**
     * @param string $id
     * @return bool
     */
    public function exist(string $id): bool
    {
        try {
            $id = $this->normalizeIdService($id);
            return array_key_exists($id, $this->storage);
        } catch (ContainerException $e) {
            // Контейнер может иметь только фиксированный набор сервисов. Если указан неизвестный - значит он не может
            // быть добавлен. Если будет добавлен метод set(), то данную механику нужно будет переделывать
            return false;
        }
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
     * @return MessageInterface
     * @throws ContainerException
     */
    public function getMessage(): MessageInterface
    {
        /** @var MessageInterface $service */
        $service = $this->get(Message::class);
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
     * @param string $id
     * @return object
     */
    private function create(string $id): object
    {
        $class = $this->map[$id];

        $object = new $class;

        $this->storage[$this->map[$id]] = $object;

        return $object;
    }

    /**
     * @param string $id
     * @return string
     * @throws ContainerException
     */
    private function normalizeIdService(string $id): string
    {
        if (!array_key_exists($id, $this->map)) {
            throw new ContainerException(ContainerException::UNKNOWN_SERVICE);
        }

        return $this->map[$id];
    }
}
