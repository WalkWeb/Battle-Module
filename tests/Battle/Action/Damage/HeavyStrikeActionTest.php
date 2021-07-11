<?php

declare(strict_types=1);

namespace Tests\Battle\Action\Damage;

use Battle\Action\Damage\DamageAction;
use Battle\Command\CommandException;
use Battle\Command\CommandFactory;
use Battle\Container\Container;
use Battle\Unit\UnitException;
use Exception;
use PHPUnit\Framework\TestCase;
use Tests\Battle\Factory\UnitFactory;

class HeavyStrikeActionTest extends TestCase
{
    private const HEAVY_STRIKE_MESSAGE_EN = '<span style="color: #1e72e3">unit_1</span> use Heavy Strike at <span style="color: #1e72e3">unit_2</span> on 50 damage';
    private const HEAVY_STRIKE_MESSAGE_RU = '<span style="color: #1e72e3">unit_1</span> использовал Тяжелый Удар по <span style="color: #1e72e3">unit_2</span> на 50 урона';

    /**
     * @throws CommandException
     * @throws UnitException
     * @throws Exception
     */
    public function testHeavyStrikeActionMessageEn(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $enemyCommand = CommandFactory::create([$enemyUnit]);
        $alliesCommand = CommandFactory::create([$unit]);

        // Up concentration
        for ($i = 0; $i < 10; $i++) {
            $unit->newRound();
        }

        $actionCollection = $unit->getAction($enemyCommand, $alliesCommand);

        foreach ($actionCollection as $action) {
            self::assertEquals(self::HEAVY_STRIKE_MESSAGE_EN, $action->handle());
        }
    }

    /**
     * @throws CommandException
     * @throws UnitException
     * @throws Exception
     */
    public function testHeavyStrikeActionMessageRu(): void
    {
        // TODO Временное решение. Позже контейнеру будет добавлен метод set(), и можно будет передать Translator с нужным языком
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'ru';

        $container = new Container();
        $unit = UnitFactory::createByTemplate(1, $container);
        $enemyUnit = UnitFactory::createByTemplate(2, $container);
        $enemyCommand = CommandFactory::create([$enemyUnit]);
        $alliesCommand = CommandFactory::create([$unit]);

        // Up concentration
        for ($i = 0; $i < 10; $i++) {
            $unit->newRound();
        }

        $actionCollection = $unit->getAction($enemyCommand, $alliesCommand);

        foreach ($actionCollection as $action) {
            self::assertInstanceOf(DamageAction::class, $action);
            self::assertEquals((int)($unit->getDamage() * 2.5), $action->getPower());
            self::assertEquals(self::HEAVY_STRIKE_MESSAGE_RU, $action->handle());
        }

        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en';
    }
}
