<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Effect;

use Battle\Action\ActionCollection;
use Battle\Action\ActionException;
use Battle\Action\HealAction;
use Battle\Command\CommandException;
use Battle\Command\CommandFactory;
use Battle\Container\Container;
use Battle\Unit\Ability\AbilityInterface;
use Battle\Unit\Effect\Effect;
use Battle\Unit\Effect\EffectCollection;
use Battle\Unit\UnitException;
use Battle\Unit\UnitInterface;
use Exception;
use Tests\AbstractUnitTest;
use Tests\Factory\UnitFactory;
use Tests\Factory\UnitFactoryException;

class EffectCollectionTest extends AbstractUnitTest
{
    /**
     * @throws UnitFactoryException
     */
    public function testEffectCollectionCreate(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $collection = new EffectCollection($unit);

        $collection->add(new Effect(
            'Effect#1',
            'icon',
            10,
            new ActionCollection(),
            new ActionCollection(),
            new ActionCollection()
        ));

        $collection->add(new Effect(
            'Effect#2',
            'icon',
            10,
            new ActionCollection(),
            new ActionCollection(),
            new ActionCollection()
        ));

        self::assertCount(2, $collection);

        $namesExpected = ['Effect#1', 'Effect#2'];

        $i = 0;
        foreach ($collection as $key => $effect) {
            self::assertEquals($namesExpected[$i], $effect->getName());
            $i++;
        }
    }

    /**
     * @throws UnitFactoryException
     */
    public function testEffectCollectionExist(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $collection = new EffectCollection($unit);

        $effect1 = new Effect(
            'Effect#1',
            'icon',
            10,
            new ActionCollection(),
            new ActionCollection(),
            new ActionCollection()
        );

        $effect2 = new Effect(
            'Effect#2',
            'icon',
            10,
            new ActionCollection(),
            new ActionCollection(),
            new ActionCollection()
        );

        $collection->add($effect1);

        self::assertTrue($collection->exist($effect1));
        self::assertFalse($collection->exist($effect2));
    }

    /**
     * Тест на ситуацию, когда добавленный аналогичный эффект обновляет длительность уже существующего
     *
     * @throws ActionException
     * @throws UnitFactoryException
     */
    public function testEffectCollectionAddDouble(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $collection = new EffectCollection($unit);

        $duration = 10;

        $effect = new Effect(
            'Effect#1',
            'icon',
            $duration,
            new ActionCollection(),
            new ActionCollection(),
            new ActionCollection()
        );

        $collection->add($effect);

        $collection->nextRound();
        $collection->nextRound();
        $collection->nextRound();

        foreach ($collection as $effect) {
            self::assertEquals($duration - 3, $effect->getDuration());
        }

        $collection->add($effect);

        self::assertCount(1, $collection);

        foreach ($collection as $effect) {
            self::assertEquals($duration, $effect->getDuration());
        }
    }

    /**
     * Тест на получение лечения у коллекции эффектов при новом раунде
     *
     * @throws CommandException
     * @throws UnitException
     * @throws ActionException
     * @throws UnitFactoryException
     */
    public function testEffectCollectionActionsOnNextRound(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $collection = new EffectCollection($unit);

        $actions = new ActionCollection();

        $actions->add(new HealAction(
            $this->getContainer(),
            $unit,
            $enemyCommand,
            $command,
            HealAction::TARGET_WOUNDED_ALLIES,
            20,
            '',
            HealAction::UNIT_ANIMATION_METHOD,
            HealAction::DEFAULT_MESSAGE_METHOD
        ));

        $collection->add(new Effect(
            'Effect#1',
            'icon',
            5,
            new ActionCollection(),
            $actions,
            new ActionCollection()
        ));

        // 5 раз получаем ActionCollection с HealAction внутри
        for ($i = 0; $i < 5; $i++) {
            self::assertEquals($actions, $collection->newRound());
            $collection->nextRound();
        }

        // Затем коллекция эффектов становится пустой - эффект удалился
        self::assertCount(0, $collection);
    }

    /**
     * @throws ActionException
     * @throws CommandException
     * @throws UnitException
     * @throws UnitFactoryException
     */
    public function testEffectCollectionActionsOnDisableActions(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $collection = new EffectCollection($unit);

        $actions = new ActionCollection();

        $actions->add(new HealAction(
            $this->getContainer(),
            $unit,
            $enemyCommand,
            $command,
            HealAction::TARGET_WOUNDED_ALLIES,
            20,
            '',
            HealAction::UNIT_ANIMATION_METHOD,
            HealAction::DEFAULT_MESSAGE_METHOD
        ));

        $collection->add(new Effect(
            'Effect#1',
            'icon',
            5,
            new ActionCollection(),
            new ActionCollection(),
            $actions
        ));

        // 4 раз получаем пустую ActionCollection
        for ($i = 0; $i < 4; $i++) {
            self::assertEquals(new ActionCollection(), $collection->nextRound());
        }

        // А на 5 раз получаем ActionCollection с HealAction
        self::assertEquals($actions, $collection->nextRound());

        // Затем коллекция эффектов становится пустой - эффект удалился
        self::assertCount(0, $collection);
    }

    /**
     * Тест на отсутствие эффектов паралича в коллекции
     *
     * @throws UnitFactoryException
     */
    public function testEffectCollectionExistParalysisFalse(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $collection = new EffectCollection($unit);
        self::assertFalse($collection->existParalysis());
    }

    /**
     * Тест на присутствие эффекта паралича в коллекции
     *
     * @throws Exception
     */
    public function testEffectCollectionExistNoParalysisTrue(): void
    {
        $unit = UnitFactory::createByTemplate(21);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = $this->createAbilityByDataProvider($unit, 'Paralysis');

        foreach ($ability->getActions($enemyCommand, $command) as $action) {
            self::assertTrue($action->canByUsed());
            $action->handle();
        }

        self::assertTrue($enemyUnit->getEffects()->existParalysis());
    }

    /**
     * @param UnitInterface $unit
     * @param string $abilityName
     * @param int $abilityLevel
     * @return AbilityInterface
     * @throws Exception
     */
    private function createAbilityByDataProvider(UnitInterface $unit, string $abilityName, int $abilityLevel = 1): AbilityInterface
    {
        $container = new Container();

        return $container->getAbilityFactory()->create(
            $unit,
            $container->getAbilityDataProvider()->get($abilityName, $abilityLevel)
        );
    }
}
