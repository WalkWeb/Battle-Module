<?php

declare(strict_types=1);

namespace Tests\Battle\Action\Heal;

use Battle\Action\ActionException;
use Battle\Action\ActionInterface;
use Battle\Action\Damage\DamageAction;
use Battle\Action\Heal\GreatHealAction;
use Battle\Action\Heal\HealAction;
use Battle\Command\CommandException;
use Battle\Command\CommandFactory;
use Battle\Result\Chat\Message;
use Battle\Result\Scenario\Scenario;
use Battle\Statistic\Statistic;
use Battle\Unit\UnitException;
use Battle\Unit\UnitInterface;
use Exception;
use PHPUnit\Framework\TestCase;
use Tests\Battle\Factory\UnitFactory;

class HealActionTest extends TestCase
{
    private const MESSAGE           = '<span style="color: #1e72e3">unit_5</span> use Great Heal and heal <span style="color: #1e72e3">unit_1</span> on 15 life';
    private const NO_TARGET_MESSAGE = '<span style="color: #1e72e3">unit_5</span> attack <span style="color: #1e72e3">unit_3</span> on 15 damage';

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
    public function testNoTargetHealAction(): void
    {
        $scenario = new Scenario();
        $statistic = new Statistic();
        $message = '';
        $unit = UnitFactory::createByTemplate(1);
        $alliesUnit = UnitFactory::createByTemplate(5);
        $enemyUnit = UnitFactory::createByTemplate(3);
        $alliesCommand = CommandFactory::create([$unit, $alliesUnit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        for ($i = 0; $i < 10; $i++) {
            $alliesUnit->newRound();
        }

        self::assertEquals(UnitInterface::MAX_CONS, $alliesUnit->getConcentration());

        // Получаем лечение, но лечить некого
        $actionCollection = $alliesUnit->getAction($enemyCommand, $alliesCommand);

        // Проверяем, что получили лечение
        foreach ($actionCollection as $action) {
            self::assertContainsOnlyInstancesOf(HealAction::class, [$action]);
            $message = $action->handle();

            // Проверяем, что лечение не применилось
            self::assertFalse($action->isSuccessHandle());
            self::assertEquals(ActionInterface::NO_HANDLE_MESSAGE, $message);

            // В этом случае Stroke возьмет базовую атаку у юнита, и выполнит её
            $action = $alliesUnit->getBaseAttack($enemyCommand, $alliesCommand);
            $message = $action->handle();

            // Проверяем, что $action успешно обрабатывается сценарием
            $scenario->addAction($action, $statistic);
        }

        // Но так как все живы - применится урон, проверяем
        self::assertEquals(self::NO_TARGET_MESSAGE, $message);
        self::assertTrue($enemyUnit->getLife() < $enemyUnit->getTotalLife());

        // Проверяем, что концентрация осталась максимальной
        self::assertEquals(UnitInterface::MAX_CONS, $alliesUnit->getConcentration());

        // Наносим урон юниту из команды
        $actionCollection = $enemyUnit->getAction($alliesCommand, $enemyCommand);

        foreach ($actionCollection as $action) {
            self::assertContainsOnlyInstancesOf(DamageAction::class, [$action]);
            $action->handle();
            $scenario->addAction($action, $statistic);
        }

        // Получаем действие еще раз
        $actionCollection = $alliesUnit->getAction($enemyCommand, $alliesCommand);

        // Проверяем, что на этот раз получили лечение
        foreach ($actionCollection as $action) {
            self::assertContainsOnlyInstancesOf(HealAction::class, [$action]);
            $message = $action->handle();
            $scenario->addAction($action, $statistic);
        }

        // Проверяем обнуленную концентрацию
        self::assertEquals(0, $alliesUnit->getConcentration());

        // Проверяем восстановленное здоровье и сообщение
        self::assertEquals(self::MESSAGE, $message);
        self::assertEquals($unit->getLife(), $unit->getTotalLife());
        self::assertEquals($alliesUnit->getLife(), $alliesUnit->getTotalLife());
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
     * @throws ActionException
     * @throws CommandException
     * @throws UnitException
     */
    public function testHealActionNoTargetException(): void
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
            $action->handle();

            $this->expectException(ActionException::class);
            $this->expectExceptionMessage(ActionException::NO_TARGET_UNIT);
            $action->getTargetUnit();
        }
    }
}
