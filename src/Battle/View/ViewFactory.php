<?php

declare(strict_types=1);

namespace Battle\View;

use Battle\Translation\Translation;
use Battle\Translation\TranslationInterface;

class ViewFactory
{
    /**
     * Создает View
     *
     * Фабрика простая, и создана для того, чтобы в Stroke отвязаться от конкретной реализации
     *
     * @param TranslationInterface|null $translation
     * @param string|null $templateDir
     * @param string|null $headTemplate
     * @param string|null $resultTemplate
     * @param string|null $rowTemplate
     * @param string|null $unitTemplate
     * @param string $unitFullLogTemplate
     * @param string|null $unitsStatsTemplate
     * @return ViewInterface
     */
    public function create(
        ?TranslationInterface $translation = null,
        string $templateDir = __DIR__ . '/../../../templates/',
        string $headTemplate = 'battle/head.template.php',
        string $resultTemplate = 'battle/result.template.php',
        string $rowTemplate = 'battle/row.template.php',
        string $unitTemplate = 'battle/unit/unit.template.php',
        string $unitFullLogTemplate = 'battle/unit/unit_full_log.template.php',
        string $unitsStatsTemplate = 'battle/unit/units_stats.template.php'

    ): ViewInterface
    {
        return new View(
            $translation ?? new Translation(),
            $templateDir,
            $headTemplate,
            $resultTemplate,
            $rowTemplate,
            $unitTemplate,
            $unitFullLogTemplate,
            $unitsStatsTemplate
        );
    }
}
