<?php

declare(strict_types=1);

namespace Tests\Battle\Action;

use Battle\Action\ActionCollection;
use Battle\Action\ActionException;
use Battle\Action\BuffAction;
use Battle\Action\EffectAction;
use Battle\Command\CommandException;
use Battle\Command\CommandFactory;
use Battle\Unit\Effect\Effect;
use Battle\Unit\Effect\EffectCollection;
use Battle\Unit\UnitException;
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
     */
    public function testEffectActionApply(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $onApplyActions = new ActionCollection();
        $onApplyActions->add(
            new BuffAction($unit, $enemyCommand, $command, 'use Reserve Forces', 'multiplierMaxLife', 130)
        );

        $effects = new EffectCollection();
        $effects->add(new Effect('Effect#123', 'icon.png', 8, $onApplyActions, new ActionCollection(), new ActionCollection()));

        $effectAction = new EffectAction($unit, $enemyCommand, $command, 'use Reserve Forces for self', $effects);

        $message = $effectAction->handle();

        self::assertEquals(self::MESSAGE, $message);

        self::assertEquals($effects, $unit->getEffects());

        // todo Механика применения эффекта и проверка увеличения здоровья
    }
}
