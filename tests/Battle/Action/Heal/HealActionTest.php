<?php

declare(strict_types=1);

namespace Tests\Battle\Action\Heal;

use Battle\Action\ActionException;
use Battle\Action\Heal\GreatHealAction;
use Battle\Action\Heal\HealAction;
use Battle\Command\CommandException;
use Battle\Command\CommandFactory;
use Battle\Result\Chat\Message;
use Battle\Unit\UnitException;
use Exception;
use PHPUnit\Framework\TestCase;
use Tests\Battle\Factory\UnitFactory;

class HealActionTest extends TestCase
{
    /**
     * @throws CommandException
     * @throws UnitException
     * @throws Exception
     */
    public function testHealActionRealistic(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $alliesUnit = UnitFactory::createByTemplate(5);
        $enemyUnit = UnitFactory::createByTemplate(3);
        $alliesCommand = CommandFactory::create([$unit, $alliesUnit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        // Наносим урон
        $damages = $enemyUnit->getAction($alliesCommand, $enemyCommand);

        foreach ($damages as $damage) {
            $damage->handle();
        }

        // Проверяем, что у одного из юнитов здоровье уменьшилось
        self::assertTrue($unit->getLife() < $unit->getTotalLife() || $alliesUnit->getLife() < $alliesUnit->getTotalLife());


        // Накапливаем концентрацию
        for ($i = 0; $i < 10; $i++) {
            $alliesUnit->newRound();
        }

        // Применяем лечение
        $heals =  $alliesUnit->getAction($enemyCommand, $alliesCommand);

        foreach ($heals as $heal) {
            // Проверяем, что лечение может быть использовано:
            self::assertTrue($heal->canByUsed());
            $heal->handle();
        }

        // Проверяем, что оба юнита стали здоровы
        self::assertTrue($unit->getLife() === $unit->getTotalLife() && $alliesUnit->getLife() === $alliesUnit->getTotalLife());
    }

    /**
     * Более простой вариант теста, без нанесения урона
     *
     * @throws Exception
     */
    public function testHealActionSimple(): void
    {
        $actionUnit = UnitFactory::createByTemplate(5);
        $woundedUnit = UnitFactory::createByTemplate(11);
        $enemyUnit = UnitFactory::createByTemplate(3);
        $actionCommand = CommandFactory::create([$actionUnit, $woundedUnit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        // Накапливаем концентрацию
        for ($i = 0; $i < 10; $i++) {
            $actionCommand->newRound();
        }

        // Применяем лечение
        $actions = $actionUnit->getAction($enemyCommand, $actionCommand);

        foreach ($actions as $action) {
            $action->handle();
        }

        // Проверяем лечение
        self::assertEquals(1 + $actionUnit->getDamage() * 3, $woundedUnit->getLife());
    }

    /**
     * @throws CommandException
     * @throws UnitException
     * @throws Exception
     */
    public function testGetPowerHealAction(): void
    {
        $message = new Message();
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(3);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $healAction = new HealAction($unit, $enemyCommand, $command, $message);
        $greatHealAction = new GreatHealAction($unit, $enemyCommand, $command, $message);

        self::assertEquals((int)($unit->getDamage() * 1.2), $healAction->getPower());
        self::assertEquals($unit->getDamage() * 3, $greatHealAction->getPower());
    }

    /**
     * Тест похож на testNoTargetHealAction(), но здесь проверяем полученное исключение
     *
     * @throws Exception
     */
    public function testHealActionNoTargetForHealException(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $alliesUnit = UnitFactory::createByTemplate(5);
        $enemyUnit = UnitFactory::createByTemplate(3);
        $alliesCommand = CommandFactory::create([$unit, $alliesUnit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        for ($i = 0; $i < 10; $i++) {
            $alliesUnit->newRound();
        }

        $actionCollection = $alliesUnit->getAction($enemyCommand, $alliesCommand);

        foreach ($actionCollection as $action) {
            self::assertContainsOnlyInstancesOf(HealAction::class, [$action]);

            $this->expectException(ActionException::class);
            $this->expectExceptionMessage(ActionException::NO_TARGET_FOR_HEAL);
            $action->handle();
        }
    }

    /**
     * В этом тесте мы просто получаем исключение по прямому вызову getTargetUnit(), который используется после
     * применения способности
     *
     * @throws Exception
     */
    public function testHealActionNoTargetException(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(3);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = new HealAction($unit, $enemyCommand, $command, new Message());

        $this->expectException(ActionException::class);
        $this->expectExceptionMessage(ActionException::NO_TARGET_UNIT);
        $action->getTargetUnit();
    }
}
