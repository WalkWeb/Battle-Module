<?php

declare(strict_types=1);

namespace Tests\Battle\Statistics;

use Battle\Command\Command;
use Battle\Statistic\BattleStatistic;
use Exception;
use PHPUnit\Framework\TestCase;
use Tests\Battle\Factory\UnitFactory;

class BattleStatisticsTest extends TestCase
{
    public function testRoundNumber(): void
    {
        $statistics = new BattleStatistic();

        $statistics->increasedRound();
        $statistics->increasedRound();
        $statistics->increasedRound();

        self::assertEquals(4, $statistics->getRoundNumber());
    }

    public function testStrokeNumber(): void
    {
        $statistics = new BattleStatistic();

        $statistics->increasedStroke();
        $statistics->increasedStroke();
        $statistics->increasedStroke();

        self::assertEquals(4, $statistics->getStrokeNumber());
    }

    public function testRoundAndStrokeNumber(): void
    {
        $statistics = new BattleStatistic();

        $statistics->increasedRound();
        $statistics->increasedStroke();
        $statistics->increasedRound();
        $statistics->increasedStroke();
        $statistics->increasedRound();

        self::assertEquals(4, $statistics->getRoundNumber());
        self::assertEquals(3, $statistics->getStrokeNumber());
    }

    /**
     * @throws Exception
     */
    public function testUnitCausedDamage(): void
    {
        $statistics = new BattleStatistic();

        $attackUnit = UnitFactory::create(1);
        $defendUnit = UnitFactory::create(2);
        $defendCommand = new Command([$defendUnit]);

        $actionCollection = $attackUnit->getDamageAction($defendCommand);

        foreach ($actionCollection->getActions() as $action) {
            $action->handle();
            //$defendUnit->applyAction($action);
            $statistics->addUnitAction($action);
            self::assertEquals(20, $statistics->getUnitsStatistics()[$attackUnit->getName()]->getCausedDamage());
        }

        // Делаем 10 ударов
        for ($i = 0; $i < 10; $i++) {
            $actionCollection = $attackUnit->getDamageAction($defendCommand);

            foreach ($actionCollection->getActions() as $action) {

                if (!$defendCommand->isAlive()) {
                    break;
                }

                $action->handle();
                $statistics->addUnitAction($action);
            }
        }

        self::assertEquals(150, $statistics->getUnitsStatistics()[$attackUnit->getName()]->getCausedDamage());
    }
}
