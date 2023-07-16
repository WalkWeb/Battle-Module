<?php

declare(strict_types=1);

namespace Tests;

use Battle\Container\Container;
use Battle\Container\ContainerException;
use Battle\Container\ContainerInterface;
use Battle\Response\Chat\Chat;
use Battle\Response\Chat\ChatInterface;
use Battle\Translation\Translation;
use Battle\Unit\Ability\AbilityCollection;
use Battle\Unit\Ability\AbilityInterface;
use Battle\Unit\UnitInterface;
use Exception;
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

    /**
     * @return ContainerInterface
     */
    protected function getContainer(): ContainerInterface
    {
        return new Container(true);
    }

    /**
     * @param UnitInterface $unit
     * @param string $abilityName
     * @param int $abilityLevel
     * @return AbilityInterface
     * @throws Exception
     */
    protected function createAbilityByDataProvider(UnitInterface $unit, string $abilityName, int $abilityLevel = 1): AbilityInterface
    {
        $container = new Container();

        return $container->getAbilityFactory()->create(
            $unit,
            $container->getAbilityDataProvider()->get($abilityName, $abilityLevel)
        );
    }

    /**
     * @param AbilityInterface $ability
     * @param UnitInterface $unit
     * @throws Exception
     */
    protected function activateAbility(AbilityInterface $ability, UnitInterface $unit): void
    {
        for ($i = 0; $i < 25; $i++) {
            $unit->newRound();
        }

        $collection = new AbilityCollection();
        $collection->add($ability);

        foreach ($collection as $item) {
            self::assertEquals($ability, $item);
        }

        $collection->update($unit);
        $collection->newRound($unit);
    }
}
