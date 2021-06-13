<?php

declare(strict_types=1);

namespace Tests\Battle\Action\Damage;

use Battle\Command\CommandException;
use Battle\Command\CommandFactory;
use Battle\Result\Chat\Message;
use Battle\Translation\Translation;
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
        $message = new Message(new Translation('ru'));
        $unit = UnitFactory::createByTemplate(1, $message);
        $enemyUnit = UnitFactory::createByTemplate(2, $message);
        $enemyCommand = CommandFactory::create([$enemyUnit]);
        $alliesCommand = CommandFactory::create([$unit]);

        // Up concentration
        for ($i = 0; $i < 10; $i++) {
            $unit->newRound();
        }

        $actionCollection = $unit->getAction($enemyCommand, $alliesCommand);

        foreach ($actionCollection as $action) {
            self::assertEquals(self::HEAVY_STRIKE_MESSAGE_RU, $action->handle());
        }
    }
}
