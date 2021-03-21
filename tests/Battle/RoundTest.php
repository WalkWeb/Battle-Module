<?php

declare(strict_types=1);

namespace Tests\Battle;

use Battle\Chat\Chat;
use Battle\Classes\ClassFactoryException;
use Battle\Command\Command;
use Battle\Command\CommandException;
use Battle\Exception\RoundException;
use Battle\Round;
use Battle\Statistic\BattleStatistic;
use PHPUnit\Framework\TestCase;
use Tests\Battle\Factory\CommandFactory;
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
     */
    public function testNextCommand(): void
    {
        $leftCommand = CommandFactory::createLeftCommand();
        $rightCommand = CommandFactory::createRightCommand();
        $startCommand = 1;
        $nextCommand = 2;
        // Юниты делают по одному ходу, соответственно следующий, после раунда, ход будет 3
        $nextNumberStroke = 3;

        $round = new Round($leftCommand, $rightCommand, $startCommand, new BattleStatistic(), new Chat());
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
     */
    public function testNextCommandNoAction(): void
    {
        $leftUnit = UnitFactory::createByTemplate(1);
        $rightUnit = UnitFactory::createByTemplate(2);
        $leftUnit->madeAction();
        $leftCommand = new Command([$leftUnit]);
        $rightCommand = new Command([$rightUnit]);

        $startCommand = 1;
        $nextCommand = 2;
        // В этом раунде походит только юнит из правой команды, соответственно счетчик увеличится только на 1
        $nextNumberStroke = 2;

        $round = new Round($leftCommand, $rightCommand, $startCommand, new BattleStatistic(), new Chat());
        self::assertEquals($round->handle(), $nextCommand);
        self::assertEquals($nextNumberStroke, $round->getStatistics()->getStrokeNumber());
    }
}
