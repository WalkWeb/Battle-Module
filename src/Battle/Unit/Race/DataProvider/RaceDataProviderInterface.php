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
     * TODO Инструкция по интегрированию своего провайдера в контейнер
     *
     * @param int $id
     * @return array
     */
    public function get(int $id): array;
}
