<?php

namespace Battle\Unit\Classes\DataProvider;

use Battle\Unit\Classes\UnitClassException;

interface ClassDataProviderInterface
{
    /**
     * ClassDataProvider - это поставщик данных по классам юнитов на основе их id
     *
     * Данные могут храниться где угодно - хоть в самом поставщике данных, хоть в базе - где удобно там и делайте.
     *
     * Возвращает массив параметров класса. Сам он классы не создает - это не его задача.
     *
     * Инструкция по интегрированию своего провайдера в контейнер:
     * 1. Создаете свой класс реализующий интерфейс ClassDataProviderInterface
     * 2. Заменяете ExampleClassDataProvider своей реализацией:
     *
     * $container = new Container();
     * $classDataProvider = new YourClassDataProvider($container);
     * $container->set(ClassDataProviderInterface::class, $classDataProvider);
     * $battle = BattleFactory::create($data, $container);
     *
     * @param int $id - Unit Class id
     * @return array - Data for UnitClassFactory
     * @throws UnitClassException
     */
    public function get(int $id): array;
}
