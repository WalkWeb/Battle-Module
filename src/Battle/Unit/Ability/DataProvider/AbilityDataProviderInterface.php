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
     * TODO Инструкция по интегрированию своего провайдера в контейнер
     *
     * @param string $abilityName
     * @param int $level
     * @return mixed
     */
    public function get(string $abilityName, int $level);
}
