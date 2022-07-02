<?php

declare(strict_types=1);

namespace Tests;

use Battle\Container\Container;
use Battle\Container\ContainerException;
use Battle\Container\ContainerInterface;
use Battle\Result\Chat\Chat;
use Battle\Result\Chat\ChatInterface;
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
        $container = new Container(true);
        $container->set(Translation::class, $translation);
        return $container;
    }

    /**
     * @return ChatInterface
     * @throws ContainerException
     */
    protected function getChat(): ChatInterface
    {
        return new Chat(new Container());
    }

    /**
     * @return ChatInterface
     * @throws ContainerException
     */
    protected function getChatRu(): ChatInterface
    {
        $container = new Container();
        $container->set(Translation::class, new Translation('ru'));
        return new Chat($container);
    }
}
