<?php

declare(strict_types=1);

namespace Tests;

use Battle\Container\Container;
use Battle\Container\ContainerException;
use Battle\Container\ContainerInterface;
use Battle\Translation\Translation;
use PHPUnit\Framework\TestCase;

abstract class AbstractUnitTest extends TestCase
{
    /**
     * @return ContainerInterface
     * @throws ContainerException
     */
    protected function getContainerWithRuLanguage(): ContainerInterface
    {
        $translation = new Translation('ru');
        $container = new Container();
        $container->set(Translation::class, $translation);
        return $container;
    }
}
