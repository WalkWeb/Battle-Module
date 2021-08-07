<?php

declare(strict_types=1);

namespace Battle\Container;

use Battle\BattleFactory;
use Battle\Result\Chat\ChatInterface;
use Battle\Result\Chat\Message\MessageInterface;
use Battle\Result\FullLog\FullLogInterface;
use Battle\Result\Scenario\ScenarioInterface;
use Battle\Result\Statistic\StatisticInterface;
use Battle\Round\RoundFactory;
use Battle\Stroke\StrokeFactory;
use Battle\Translation\TranslationInterface;
use Battle\View\ViewFactory;

interface ContainerInterface
{
    /**
     * @param string $id
     * @return object
     */
    public function get(string $id): object;

    /**
     * Добавляет сервис
     *
     * Можно добавить только сервис из списка доступных (с.м. Container->map)
     *
     * @param string $id
     * @param object $object
     */
    public function set(string $id, object $object): void;

    /**
     * @param string $id
     * @return bool
     */
    public function exist(string $id): bool;

    /**
     * @return StatisticInterface
     * @throws ContainerException
     */
    public function getStatistic(): StatisticInterface;

    /**
     * @return ChatInterface
     * @throws ContainerException
     */
    public function getChat(): ChatInterface;

    /**
     * @return TranslationInterface
     * @throws ContainerException
     */
    public function getTranslation(): TranslationInterface;

    /**
     * @return ScenarioInterface
     * @throws ContainerException
     */
    public function getScenario(): ScenarioInterface;

    /**
     * @return FullLogInterface
     * @throws ContainerException
     */
    public function getFullLog(): FullLogInterface;

    /**
     * @return MessageInterface
     * @throws ContainerException
     */
    public function getMessage(): MessageInterface;

    /**
     * @return BattleFactory
     * @throws ContainerException
     */
    public function getBattleFactory(): BattleFactory;

    /**
     * @return RoundFactory
     * @throws ContainerException
     */
    public function getRoundFactory(): RoundFactory;

    /**
     * @return StrokeFactory
     * @throws ContainerException
     */
    public function getStrokeFactory(): StrokeFactory;

    /**
     * @return ViewFactory
     * @throws ContainerException
     */
    public function getViewFactory(): ViewFactory;
}
