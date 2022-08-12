<?php

declare(strict_types=1);

namespace Battle\Unit\Ability\DataProvider;

interface AbilityDataProviderInterface
{
    /**
     * Возвращает массив данных по указанной способности и её уровню
     *
     * Данные могут храниться где угодно - хоть в самом поставщике данных, хоть в базе - где удобно там и делайте.
     *
     * Возвращает массив параметров способности. Сам он способности не создает - это не его задача.
     *
     * Инструкция по интегрированию своего провайдера в контейнер:
     * 1. Создаете свой класс реализующий интерфейс AbilityDataProviderInterface
     * 2. Заменяете ExampleAbilityDataProvider своей реализацией:
     *
     * $container = new Container();
     * $abilityDataProvider = new YourAbilityDataProvider($container);
     * $container->set(AbilityDataProviderInterface::class, $abilityDataProvider);
     * $battle = BattleFactory::create($data, $container);
     *
     * @param string $abilityName
     * @param int $level
     * @return mixed
     */
    public function get(string $abilityName, int $level);
}
