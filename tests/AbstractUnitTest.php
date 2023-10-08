<?php

declare(strict_types=1);

namespace Tests;

use Battle\Action\BuffAction;
use Battle\Action\EffectAction;
use Battle\Command\CommandFactory;
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
use Tests\Factory\UnitFactory;

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

    /**
     * Проверка создания эффекта (способности с каким-то одним простым эффектом)
     *
     * @param int $unitId
     * @param string $name
     * @param string $icon
     * @param int $typeActivate
     * @param array $allowedWeaponTypes
     * @param string $effectClass
     * @param bool $disposable
     * @return AbilityInterface
     * @throws Exception
     */
    protected function assertCreateEffectAbility(
        int $unitId,
        string $name,
        string $icon,
        int $typeActivate,
        array $allowedWeaponTypes = [],
        string $effectClass = BuffAction::class,
        bool $disposable = false
    ): AbilityInterface
    {
        $unit = UnitFactory::createByTemplate($unitId);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = $this->createAbilityByDataProvider($unit, $name, 1);

        self::assertEquals($name, $ability->getName());
        self::assertEquals($icon, $ability->getIcon());
        self::assertEquals($unit, $ability->getUnit());
        self::assertFalse($ability->isReady());
        self::assertTrue($ability->canByUsed($enemyCommand, $command));
        self::assertEquals($disposable, $ability->isDisposable());
        self::assertFalse($ability->isUsage());
        self::assertEquals($typeActivate, $ability->getTypeActivate());
        self::assertEquals($allowedWeaponTypes, $ability->getAllowedWeaponTypes());

        $actions = $ability->getActions($enemyCommand, $command);

        self::assertCount(1, $actions);

        foreach ($ability->getActions($enemyCommand, $command) as $i => $action) {
            self::assertInstanceOf(EffectAction::class, $action);
            self::assertEquals($name, $action->getNameAction());
            self::assertEquals($icon, $action->getIcon());

            foreach ($action->getEffect()->getOnNextRoundActions() as $effect) {
                self::assertInstanceOf($effectClass, $effect);
                self::assertEquals($name, $effect->getNameAction());
                self::assertEquals($icon, $effect->getIcon());
            }
        }

        return $ability;
    }
}
