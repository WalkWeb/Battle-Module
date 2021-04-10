<?php

declare(strict_types=1);

namespace Tests\Battle\Round;

use Battle\Chat\Chat;
use Battle\Classes\ClassFactoryException;
use Battle\Command\CommandException;
use Battle\Command\CommandFactory;
use Battle\Round\RoundException;
use Battle\Round\Round;
use Battle\Statistic\Statistic;
use Battle\Unit\UnitException;
use PHPUnit\Framework\TestCase;
use Tests\Battle\Factory\CommandFactory as TestCommandFactory;
use Tests\Battle\Factory\UnitFactory;
use Tests\Battle\Factory\UnitFactoryException;

class RoundTest extends TestCase
{
    /**
     * Проверяем корректную смену действующий команды
     *
     * @throws ClassFactoryException
     * @throws CommandException
     * @throws RoundException
     * @throws UnitFactoryException
     * @throws UnitException
     */
    public function testNextCommand(): void
    {
        $leftCommand = TestCommandFactory::createLeftCommand();
        $rightCommand = TestCommandFactory::createRightCommand();
        $startCommand = 1;
        $nextCommand = 2;
        // Юниты делают по одному ходу, соответственно следующий, после раунда, ход будет 3
        $nextNumberStroke = 3;

        $round = new Round($leftCommand, $rightCommand, $startCommand, new Statistic(), new Chat());
        self::assertEquals($round->handle(), $nextCommand);
        self::assertEquals($nextNumberStroke, $round->getStatistics()->getStrokeNumber());
    }

    /**
     * Проверяем корректную смену действующий когда в действующей команде нет юнитов, способных совершить действие
     *
     * @throws ClassFactoryException
     * @throws CommandException
     * @throws RoundException
     * @throws UnitFactoryException
     * @throws UnitException
     */
    public function testNextCommandNoAction(): void
    {
        $leftUnit = UnitFactory::createByTemplate(1);
        $rightUnit = UnitFactory::createByTemplate(2);
        $leftUnit->madeAction();
        $leftCommand = CommandFactory::create([$leftUnit]);
        $rightCommand = CommandFactory::create([$rightUnit]);

        $startCommand = 1;
        $nextCommand = 2;
        // В этом раунде походит только юнит из правой команды, соответственно счетчик увеличится только на 1
        $nextNumberStroke = 2;

        $round = new Round($leftCommand, $rightCommand, $startCommand, new Statistic(), new Chat());
        self::assertEquals($round->handle(), $nextCommand);
        self::assertEquals($nextNumberStroke, $round->getStatistics()->getStrokeNumber());
    }
}
