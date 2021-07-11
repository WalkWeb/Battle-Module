<?php

declare(strict_types=1);

namespace Tests\Battle\Action\Heal;

use Battle\Command\CommandException;
use Battle\Command\CommandFactory;
use Battle\Container\Container;
use Battle\Unit\UnitException;
use Exception;
use PHPUnit\Framework\TestCase;
use Tests\Battle\Factory\UnitFactory;

class GreatHealActionTest extends TestCase
{
    private const GREAT_HEAL_MESSAGE_EN = '<span style="color: #1e72e3">unit_5</span> use Great Heal and heal <span style="color: #1e72e3">wounded_unit</span> on 45 life';
    private const GREAT_HEAL_MESSAGE_RU = '<span style="color: #1e72e3">unit_5</span> использовал Сильное Лечение и вылечил <span style="color: #1e72e3">wounded_unit</span> на 45 здоровья';

    /**
     * @throws CommandException
     * @throws UnitException
     * @throws Exception
     */
    public function testGreatHealActionMessageEn(): void
    {
        $unit = UnitFactory::createByTemplate(5);
        $woundedUnit = UnitFactory::createByTemplate(11);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $enemyCommand = CommandFactory::create([$enemyUnit]);
        $alliesCommand = CommandFactory::create([$unit, $woundedUnit]);

        // Up concentration
        for ($i = 0; $i < 10; $i++) {
            $unit->newRound();
        }

        $actionCollection = $unit->getAction($enemyCommand, $alliesCommand);

        foreach ($actionCollection as $action) {
            self::assertEquals(self::GREAT_HEAL_MESSAGE_EN, $action->handle());
        }
    }

    /**
     * @throws CommandException
     * @throws UnitException
     * @throws Exception
     */
    public function testGreatHealActionMessageRu(): void
    {
        // TODO Временное решение. Позже контейнеру будет добавлен метод set(), и можно будет передать Translator с нужным языком
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'ru';

        $container = new Container();
        $unit = UnitFactory::createByTemplate(5, $container);
        $woundedUnit = UnitFactory::createByTemplate(11, $container);
        $enemyUnit = UnitFactory::createByTemplate(2, $container);
        $enemyCommand = CommandFactory::create([$enemyUnit]);
        $alliesCommand = CommandFactory::create([$unit, $woundedUnit]);

        // Up concentration
        for ($i = 0; $i < 10; $i++) {
            $unit->newRound();
        }

        $actionCollection = $unit->getAction($enemyCommand, $alliesCommand);

        foreach ($actionCollection as $action) {
            self::assertEquals(self::GREAT_HEAL_MESSAGE_RU, $action->handle());
        }

        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en';
    }
}
