<?php

declare(strict_types=1);

namespace Battle\Unit\Race\DataProvider;

interface RaceDataProviderInterface
{
    /**
     * RaceDataProvider - это поставщик данных по расам юнитов на основе их id
     *
     * Данные могут храниться где угодно - хоть в самом поставщике данных, хоть в базе - где удобно там и делайте.
     *
     * Возвращает массив параметров расы. Сам он классы не создает - это не его задача.
     *
     * Инструкция по интегрированию своего провайдера в контейнер:
     * 1. Создаете свой класс реализующий интерфейс RaceDataProviderInterface
     * 2. Заменяете ExampleRaceDataProvider своей реализацией:
     *
     * $container = new Container();
     * $raceDataProvider = new YourRaceDataProvider($container);
     * $container->set(RaceDataProviderInterface::class, $raceDataProvider);
     * $battle = BattleFactory::create($data, $container);
     *
     * @param int $id
     * @return array
     */
    public function get(int $id): array;
}
