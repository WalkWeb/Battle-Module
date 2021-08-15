<?php

declare(strict_types=1);

namespace Tests\Battle\Action;

use Battle\Action\ActionCollection;
use Battle\Action\ActionException;
use Battle\Action\ActionInterface;
use Battle\Action\BuffAction;
use Battle\Action\EffectAction;
use Battle\Command\CommandException;
use Battle\Command\CommandFactory;
use Battle\Command\CommandInterface;
use Battle\Unit\Effect\Effect;
use Battle\Unit\Effect\EffectCollection;
use Battle\Unit\UnitException;
use Battle\Unit\UnitInterface;
use PHPUnit\Framework\TestCase;
use Tests\Battle\Factory\UnitFactory;
use Tests\Battle\Factory\UnitFactoryException;

class EffectActionTest extends TestCase
{
    private const MESSAGE = '<span style="color: #1e72e3">unit_1</span> use Reserve Forces for self';

    /**
     * @throws CommandException
     * @throws UnitException
     * @throws UnitFactoryException
     * @throws ActionException
     */
    public function testEffectActionCreate(): void
    {
        $name = 'Effect#123';
        $effects = new EffectCollection();

        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = new EffectAction($unit, $enemyCommand, $command, $name, $effects);

        self::assertEquals('applyEffectAction', $action->getHandleMethod());
        self::assertEquals($effects, $action->getEffects());
        self::assertEquals($name, $action->getNameAction());
    }

    /**
     * @throws CommandException
     * @throws UnitException
     * @throws UnitFactoryException
     * @throws ActionException
     */
    public function testEffectActionApply(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $unitBaseLife = $unit->getTotalLife();

        $effectAction = $this->getReserveForcesAction($unit, $enemyCommand, $command);

        $message = $effectAction->handle();

        self::assertEquals(self::MESSAGE, $message);

        self::assertEquals($effectAction->getEffects(), $unit->getEffects());

        // Проверяем увеличившееся здоровье
        self::assertEquals((int)($unitBaseLife * 1.3), $unit->getTotalLife());
        self::assertEquals((int)($unitBaseLife * 1.3), $unit->getLife());

        // Применяем эффект еще раз, и проверяем, что здоровье еще раз не увеличилось
        $effectAction->handle();

        self::assertEquals((int)($unitBaseLife * 1.3), $unit->getTotalLife());
        self::assertEquals((int)($unitBaseLife * 1.3), $unit->getLife());

        // Пропускаем ходы и проверяем, что здоровье вернулось к исходному
        for ($i = 0; $i < 10; $i++) {
            $unit->newRound();
        }

        self::assertEquals($unitBaseLife, $unit->getTotalLife());
        self::assertEquals($unitBaseLife, $unit->getLife());
        self::assertCount(0, $unit->getEffects());
    }

    /**
     * Создает и возвращает EffectAction
     *
     * TODO Возможно в будущем нужно сделать фабрику для более быстрого и удобного создания эффектов в тестах
     *
     * @param UnitInterface $unit
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $command
     * @return ActionInterface
     */
    private function getReserveForcesAction(
        UnitInterface $unit,
        CommandInterface $enemyCommand,
        CommandInterface $command
    ): ActionInterface
    {
        // Создаем коллекцию событий с одним событием - добавлением эффекта на увеличение здоровья
        $onApplyActions = new ActionCollection();
        $onApplyActions->add(
            new BuffAction($unit, $enemyCommand, $command, 'use Reserve Forces', 'multiplierMaxLife', 130)
        );

        // Создаем коллекцию эффектов, которые будут применяться на юнита при добавлении ему эффекта
        $effects = new EffectCollection();
        $effects->add(new Effect('Effect#123', 'icon.png', 8, $onApplyActions, new ActionCollection(), new ActionCollection()));

        // Создаем и возвращаем EffectAction
        return new EffectAction($unit, $enemyCommand, $command, 'use Reserve Forces for self', $effects);
    }
}
