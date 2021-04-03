<?php

declare(strict_types=1);

namespace Battle\View;

class ViewFactory
{
    /**
     * Создает View
     *
     * Фабрика простая, и создана для того, чтобы в Stroke отвязаться от конкретной реализации
     *
     * @param string|null $templateDir
     * @return ViewInterface
     */
    public function create(?string $templateDir = __DIR__ . '/../../../templates/'): ViewInterface
    {
        return new View($templateDir);
    }
}
