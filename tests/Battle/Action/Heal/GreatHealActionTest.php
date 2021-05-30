<?php

declare(strict_types=1);

namespace Tests\Battle\Action\Heal;

use Battle\Classes\ClassFactoryException;
use Battle\Command\CommandException;
use Battle\Command\CommandFactory;
use Battle\Result\Chat\Message;
use Battle\Translation\Translation;
use Battle\Unit\UnitException;
use PHPUnit\Framework\TestCase;
use Tests\Battle\Factory\UnitFactory;
use Tests\Battle\Factory\UnitFactoryException;

class GreatHealActionTest extends TestCase
{
    private const GREAT_HEAL_MESSAGE_EN = '<b>unit_5</b> use Great Heal and heal <b>wounded_unit</b> on 45 life';
    private const GREAT_HEAL_MESSAGE_RU = '<b>unit_5</b> использовал Сильное Лечение и вылечил <b>wounded_unit</b> на 45 здоровья';

    /**
     * @throws ClassFactoryException
     * @throws CommandException
     * @throws UnitException
     * @throws UnitFactoryException
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
     * @throws ClassFactoryException
     * @throws CommandException
     * @throws UnitException
     * @throws UnitFactoryException
     */
    public function testGreatHealActionMessageRu(): void
    {
        $message = new Message(new Translation('ru'));
        $unit = UnitFactory::createByTemplate(5, $message);
        $woundedUnit = UnitFactory::createByTemplate(11, $message);
        $enemyUnit = UnitFactory::createByTemplate(2, $message);
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
    }
}
