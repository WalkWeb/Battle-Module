<?php

declare(strict_types=1);

namespace Battle\View;

use Battle\Container\ContainerException;
use Battle\Container\ContainerInterface;

class ViewFactory
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Создает View
     *
     * Фабрика простая, и создана для того, чтобы в Stroke отвязаться от конкретной реализации
     *
     * @param string|null $templateDir
     * @param string|null $headTemplate
     * @param string|null $resultTemplate
     * @param string|null $rowTemplate
     * @param string|null $unitTemplate
     * @param string $unitFullLogTemplate
     * @param string|null $unitsStatsTemplate
     * @return ViewInterface
     * @throws ContainerException
     */
    public function create(
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
            $this->container->getTranslation(),
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
