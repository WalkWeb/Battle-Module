<?php

declare(strict_types=1);

namespace Battle\Container;

use Battle\Action\ActionFactory;
use Battle\BattleFactory;
use Battle\Response\Chat\ChatInterface;
use Battle\Response\FullLog\FullLogInterface;
use Battle\Response\Scenario\ScenarioInterface;
use Battle\Response\Statistic\StatisticInterface;
use Battle\Round\RoundFactory;
use Battle\Stroke\StrokeFactory;
use Battle\Translation\TranslationInterface;
use Battle\Unit\Ability\AbilityFactory;
use Battle\Unit\Ability\DataProvider\AbilityDataProviderInterface;
use Battle\Unit\Ability\Description\AbilityDescriptionFactory;
use Battle\Unit\Classes\DataProvider\ClassDataProviderInterface;
use Battle\Unit\Classes\UnitClassFactory;
use Battle\Unit\Effect\EffectFactory;
use Battle\Unit\Race\DataProvider\RaceDataProviderInterface;
use Battle\Unit\Race\RaceFactory;
use Battle\View\ViewFactory;

interface ContainerInterface
{
    /**
     * Возвращает сервис по его id
     *
     * id может быть как название класса в виде ClassName::class так и название в виде 'ClassName'
     * Допустимые имена смотрите в Container->map
     *
     * При этом если сервис уже запрашивался и создавался ранее, то при повторном запросе будет возвращен существующий
     *
     * По сути контейнер это более правильная реализация паттерна Singleton
     *
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
     * Проверяет существование во внутреннем хранилище уже созданного объекта данного класса
     *
     * @param string $class
     * @return bool
     */
    public function exist(string $class): bool;

    /**
     * Некоторые сервисы должны работать по-разному в обычном режиме и режиме тестов (например, в тестах по-другому
     * рассчитывается шанс попадания по противнику)
     *
     * @return bool
     */
    public function isTestMode(): bool;

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

    /**
     * @return AbilityFactory
     */
    public function getAbilityFactory(): AbilityFactory;

    /**
     * @return UnitClassFactory
     */
    public function getUnitClassFactory(): UnitClassFactory;

    /**
     * @return RaceFactory
     */
    public function getRaceFactory(): RaceFactory;

    /**
     * @return ActionFactory
     */
    public function getActionFactory(): ActionFactory;

    /**
     * @return EffectFactory
     */
    public function getEffectFactory(): EffectFactory;

    /**
     * @return AbilityDescriptionFactory
     */
    public function getAbilityDescriptionFactory(): AbilityDescriptionFactory;
    
    /**
     * Поставщик данных классов юнитов
     *
     * @return ClassDataProviderInterface
     * @throws ContainerException
     */
    public function getClassDataProvider(): ClassDataProviderInterface;

    /**
     * Поставщик данных способностей юнитов
     *
     * @return AbilityDataProviderInterface
     */
    public function getAbilityDataProvider(): AbilityDataProviderInterface;

    /**
     * Поставщик данных по расам юнитов
     *
     * @return RaceDataProviderInterface
     */
    public function getRaceDataProvider(): RaceDataProviderInterface;
}
