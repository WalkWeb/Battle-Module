<?php

declare(strict_types=1);

namespace Battle\Translation;

interface TranslationInterface
{
    /**
     * Возвращает перевод указанного сообщения.
     *
     * В случае отсутствия перевода - возвращает это же сообщение
     *
     * @param string $message
     * @return string
     */
    public function trans(string $message): string;

    /**
     * Возвращает язык, который был определен у пользователя
     *
     * @return string
     */
    public function getLanguage(): string;
}
