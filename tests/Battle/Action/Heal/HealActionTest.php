<?php

declare(strict_types=1);

namespace Tests\Battle\Action\Heal;

use Battle\Action\Damage\DamageAction;
use Battle\Action\Heal\GreatHealAction;
use Battle\Action\Heal\HealAction;
use Battle\Classes\ClassFactoryException;
use Battle\Command\CommandException;
use Battle\Command\CommandFactory;
use Battle\Unit\UnitException;
use Battle\Unit\UnitInterface;
use PHPUnit\Framework\TestCase;
use Tests\Battle\Factory\UnitFactory;
use Tests\Battle\Factory\UnitFactoryException;

class HealActionTest extends TestCase
{
    private const MESSAGE           = '<b>unit_5</b> heal to <b>unit_1</b> on 15 life';
    private const NO_TARGET_MESSAGE = '<b>unit_5</b> attack <b>unit_3</b> on 15 damage';

    /**
     * @throws ClassFactoryException
     * @throws CommandException
     * @throws UnitException
     * @throws UnitFactoryException
     */
    public function testHealAction(): void
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
     * @throws ClassFactoryException
     * @throws CommandException
     * @throws UnitFactoryException
     * @throws UnitException
     */
    public function testNoTargetHealAction(): void
    {
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

        // Лечить некого - получаем базовую атаку
        $actionCollection = $alliesUnit->getAction($enemyCommand, $alliesCommand);

        // Проверяем, что получили лечение
        foreach ($actionCollection as $action) {
            self::assertContainsOnlyInstancesOf(HealAction::class, [$action]);
            $message = $action->handle();
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
        }

        // Получаем действие еще раз
        $actionCollection = $alliesUnit->getAction($enemyCommand, $alliesCommand);

        // Проверяем, что на этот раз получили лечение
        foreach ($actionCollection as $action) {
            self::assertContainsOnlyInstancesOf(HealAction::class, [$action]);
            $message = $action->handle();
        }

        // Проверяем обнуленную концентрацию
        self::assertEquals(0, $alliesUnit->getConcentration());

        // Проверяем восстановленное здоровье и сообщение
        self::assertEquals(self::MESSAGE, $message);
        self::assertEquals($unit->getLife(), $unit->getTotalLife());
        self::assertEquals($alliesUnit->getLife(), $alliesUnit->getTotalLife());
    }

    /**
     * @throws ClassFactoryException
     * @throws CommandException
     * @throws UnitException
     * @throws UnitFactoryException
     */
    public function testGetPowerHealAction(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(3);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $healAction = new HealAction($unit, $enemyCommand, $command);
        $greatHealAction = new GreatHealAction($unit, $enemyCommand, $command);

        self::assertEquals((int)($unit->getDamage() * 1.2), $healAction->getPower());
        self::assertEquals($unit->getDamage() * 3, $greatHealAction->getPower());
    }
}