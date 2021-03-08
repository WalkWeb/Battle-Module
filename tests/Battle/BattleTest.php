<?php

declare(strict_types=1);

namespace Tests;

use Battle\Chat\Chat;
use Battle\Statistic\BattleStatistic;
use PHPUnit\Framework\TestCase;
use Battle\Battle;
use Battle\Command;
use Tests\Battle\Factory\UnitFactory;
use Tests\Battle\Factory\UnitFactoryException;
use Battle\Exception\CommandException;
use Battle\Exception\RoundException;
use Exception;

class BattleTest extends TestCase
{
    public function testSuccess(): void
    {
        try {
            $unit1 = UnitFactory::create(1);
            $unit2 = UnitFactory::create(2);
            $command1 = new Command([$unit1]);
            $command2 = new Command([$unit2]);

            $battle = new Battle($command1, $command2, new BattleStatistic(), new Chat());
            $result = $battle->handle();

            $this->assertEquals(2, $result->getWinner());
            $this->assertInstanceOf(Battle::class, $battle);
            $this->assertTrue($battle->getStatistics()->getRoundNumber() > 2);
            $this->assertTrue($battle->getStatistics()->getStrokeNumber() > 4);

        } catch (UnitFactoryException | CommandException | RoundException | Exception $e) {
            die($e->getMessage());
        }
    }
}
