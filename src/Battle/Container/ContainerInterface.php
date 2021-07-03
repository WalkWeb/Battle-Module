<?php

declare(strict_types=1);

namespace Battle\Container;

use Battle\BattleFactory;
use Battle\Result\Chat\Chat;
use Battle\Result\Chat\Message;
use Battle\Result\FullLog\FullLog;
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
     * @return Chat
     * @throws ContainerException
     */
    public function getChat(): Chat;

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
     * @return FullLog
     * @throws ContainerException
     */
    public function getFullLog(): FullLog;

    /**
     * @return Message
     * @throws ContainerException
     */
    public function getMessage(): Message;

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
