<?php

declare(strict_types=1);

namespace Battle\Response\FullLog;

interface FullLogInterface
{
    /**
     * Добавляет запись в лог
     *
     * @param string $log
     */
    public function add(string $log): void;

    /**
     * Добавляет текстовую запись в лог (будут добавлены теги <p>...</p>)
     *
     * @param string $text
     */
    public function addText(string $text): void;

    /**
     * Добавляет разделительную линию
     */
    public function addLine(): void;

    /**
     * Возвращает массив всех записей в лог
     *
     * @return array
     */
    public function getLog(): array;
}
