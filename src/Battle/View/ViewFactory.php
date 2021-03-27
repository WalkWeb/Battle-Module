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
     * @return ViewInterface
     */
    public function create(): ViewInterface
    {
        return new View();
    }
}
