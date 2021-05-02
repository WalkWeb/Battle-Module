<?php

declare(strict_types=1);

namespace Tests\Battle\Action\Heal;

use Battle\Action\Damage\DamageAction;
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
        self::assertEquals('<b>unit_5</b> [80/80] normal attack <b>unit_3</b> [105/120] on 15 damage', $message);
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
        self::assertEquals('<b>unit_5</b> [80/80] heal to <b>unit_1</b> [100/100] on 15 life', $message);
        self::assertEquals($unit->getLife(), $unit->getTotalLife());
        self::assertEquals($alliesUnit->getLife(), $alliesUnit->getTotalLife());
    }
}
