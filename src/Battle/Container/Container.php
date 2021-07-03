<?php

declare(strict_types=1);

namespace Battle\Container;

use Battle\BattleFactory;
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

class Container implements ContainerInterface
{
    private $map = [
        StatisticInterface::class   => Statistic::class,
        Statistic::class            => Statistic::class,
        'Statistic'                 => Statistic::class,
        Chat::class                 => Chat::class,
        'Chat'                      => Chat::class,
        TranslationInterface::class => Translation::class,
        Translation::class          => Translation::class,
        'Translation'               => Translation::class,
        ScenarioInterface::class    => Scenario::class,
        Scenario::class             => Scenario::class,
        'Scenario'                  => Scenario::class,
        FullLog::class              => FullLog::class,
        'FullLog'                   => FullLog::class,
        BattleFactory::class        => BattleFactory::class,
        'BattleFactory'             => BattleFactory::class,
        RoundFactory::class         => RoundFactory::class,
        'RoundFactory'              => RoundFactory::class,
        StrokeFactory::class        => StrokeFactory::class,
        'StrokeFactory'             => StrokeFactory::class,
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
