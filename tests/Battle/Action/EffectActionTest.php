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
    private const MESSAGE_SELF = '<span style="color: #1e72e3">unit_1</span> use Reserve Forces';
    private const MESSAGE_TO   = '<span style="color: #1e72e3">unit_1</span> use Reserve Forces on <span style="color: #1e72e3">unit_2</span>';

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

        $action = new EffectAction($unit, $enemyCommand, $command, EffectAction::TARGET_SELF, $name, $effects);

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

        $effectAction = $this->getReserveForcesAction($unit, $enemyCommand, $command, EffectAction::TARGET_SELF);

        // Пока эффекта на юните нет - событие может примениться
        self::assertTrue($effectAction->canByUsed());

        $message = $effectAction->handle();

        // А вот когда эффект наложен - уже нет
        self::assertFalse($effectAction->canByUsed());

        self::assertEquals(self::MESSAGE_SELF, $message);

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

        // Проверяем, что эффект опять готов примениться
        self::assertTrue($effectAction->canByUsed());
    }

    /**
     * @throws CommandException
     * @throws UnitException
     * @throws UnitFactoryException
     */
    public function testEffectActionNoTargetForEffect(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(10);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = $this->getReserveForcesAction($unit, $enemyCommand, $command, EffectAction::TARGET_RANDOM_ENEMY);

        // При вызове canByUsed() происходит поиск цели
        self::assertFalse($action->canByUsed());

        $this->expectException(ActionException::class);
        $this->expectExceptionMessage(ActionException::NO_TARGET_FOR_EFFECT);
        $action->handle();
    }

    /**
     * @throws CommandException
     * @throws UnitException
     * @throws UnitFactoryException
     */
    public function testEffectActionMessageTo(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = $this->getReserveForcesAction($unit, $enemyCommand, $command, EffectAction::TARGET_RANDOM_ENEMY);

        // При вызове canByUsed() происходит поиск цели
        self::assertTrue($action->canByUsed());

        self::assertEquals(self::MESSAGE_TO, $action->handle());
    }

    /**
     * Создает и возвращает EffectAction
     *
     * TODO Возможно в будущем нужно сделать фабрику для более быстрого и удобного создания эффектов в тестах
     *
     * @param UnitInterface $unit
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $command
     * @param int $typeTarget
     * @return ActionInterface
     */
    private function getReserveForcesAction(
        UnitInterface $unit,
        CommandInterface $enemyCommand,
        CommandInterface $command,
        int $typeTarget
    ): ActionInterface
    {
        // Создаем коллекцию событий с одним событием - добавлением эффекта на увеличение здоровья
        $onApplyActions = new ActionCollection();
        $onApplyActions->add(
            new BuffAction(
                $unit,
                $enemyCommand,
                $command,
                EffectAction::TARGET_SELF,
                'use Reserve Forces',
                'multiplierMaxLife',
                130
            )
        );

        // Создаем коллекцию эффектов, которые будут применяться на юнита при добавлении ему эффекта
        $effects = new EffectCollection();
        $effects->add(new Effect('Effect#123', 'icon.png', 8, $onApplyActions, new ActionCollection(), new ActionCollection()));

        // Создаем и возвращаем EffectAction
        return new EffectAction(
            $unit,
            $enemyCommand,
            $command,
            $typeTarget,
            'use Reserve Forces',
            $effects
        );
    }
}
