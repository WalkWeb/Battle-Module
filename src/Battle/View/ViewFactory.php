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
     *
     * @param string|null $templateDir
     * @param string|null $resultTemplate
     * @param string|null $rowTemplate
     * @param string|null $unitTemplate
     * @return ViewInterface
     */
    public function create(
        ?string $templateDir = __DIR__ . '/../../../templates/',
        ?string $resultTemplate = 'battle/result.template.php',
        ?string $rowTemplate = 'battle/row.template.php',
        ?string $unitTemplate = 'battle/unit/unit.template.php'

    ): ViewInterface
    {
        return new View($templateDir, $resultTemplate, $rowTemplate, $unitTemplate);
    }
}
