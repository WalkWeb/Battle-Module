<?php

declare(strict_types=1);

namespace Tests\Battle\Action;

use Battle\Action\ActionException;
use Battle\Action\BuffAction;
use Battle\Command\CommandException;
use Battle\Command\CommandFactory;
use Battle\Result\Chat\Message\Message;
use Battle\Unit\UnitException;
use PHPUnit\Framework\TestCase;
use Tests\Battle\Factory\UnitFactory;
use Tests\Battle\Factory\UnitFactoryException;

class BuffActionTest extends TestCase
{
    private const MESSAGE = '<span style="color: #1e72e3">unit_1</span> use Reserve Forces';

    /**
     * Тест на баф, который увеличит здоровье юнита на 30%, а потом откат изменения
     *
     * @throws CommandException
     * @throws UnitException
     * @throws UnitFactoryException
     * @throws ActionException
     */
    public function testBuffActionSuccess(): void
    {
        $name = 'use Reserve Forces';
        $modifyMethod = 'multiplierMaxLife';
        $power = 130;

        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $oldLife = $unit->getTotalLife();

        $action = new BuffAction($unit, $enemyCommand, $command, new Message(), $name, $modifyMethod, $power);

        $multiplier = $action->getPower() / 100;
        $newLife = (int)($unit->getTotalLife() * $multiplier);

        // Применяем баф
        $message = $action->handle();

        self::assertEquals(self::MESSAGE, $message);
        self::assertEquals($newLife, $unit->getTotalLife());
        self::assertEquals($newLife, $unit->getLife());

        // Откат изменения
        // TODO В будущем нужно будет сделать так, чтобы BuffAction сам создавал Action для отката своих изменений

        $rollbackAction = new BuffAction($unit, $enemyCommand, $command, new Message(), $name, $modifyMethod . 'Revert', $power);
        $rollbackAction->setRevertValue($action->getRevertValue());

        $rollbackAction->handle();

        self::assertEquals($oldLife, $unit->getTotalLife());
        self::assertEquals($oldLife, $unit->getLife());
    }

    /**
     * Тест на попытку уменьшения здоровья - пока такой вариант не допустим (нужно проработать отдельно)
     *
     * @throws CommandException
     * @throws UnitException
     * @throws UnitFactoryException
     */
    public function testBuffActionReducedLife(): void
    {
        $name = 'use Reserve Forces';
        $modifyMethod = 'multiplierMaxLife';
        $power = 50;

        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = new BuffAction($unit, $enemyCommand, $command, new Message(), $name, $modifyMethod, $power);

        $this->expectException(UnitException::class);
        $this->expectErrorMessage(UnitException::NO_REDUCED_LIFE_MULTIPLIER);
        $action->handle();
    }

    /**
     * Тест на ситуацию, когда указан неизвестный метод модификации характеристики
     *
     * @throws CommandException
     * @throws UnitException
     * @throws UnitFactoryException
     */
    public function testBuffActionUndefinedModifyMethod(): void
    {
        $name = 'use Reserve Forces';
        $modifyMethod = 'undefinedModifyMethod';
        $power = 200;

        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = new BuffAction($unit, $enemyCommand, $command, new Message(), $name, $modifyMethod, $power);

        $this->expectException(UnitException::class);
        $this->expectErrorMessage(UnitException::UNDEFINED_MODIFY_METHOD . ': ' . $modifyMethod);
        $action->handle();
    }

    // todo Тест на русский вариант сообщения об использовании способности
}
