<?php

declare(strict_types=1);

namespace Battle\Result\FullLog;

interface FullLogInterface
{
    /**
     * Добавляет запись в лог
     *
     * @param string $log
     */
    public function add(string $log): void;

    /**
     * Возвращает массив всех записей в лог
     *
     * @return array
     */
    public function getLog(): array;
}
