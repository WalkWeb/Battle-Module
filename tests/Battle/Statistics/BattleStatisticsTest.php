<?php

declare(strict_types=1);

namespace Tests;

use Battle\Classes\ClassFactoryException;
use Battle\Command;
use Battle\Exception\ActionCollectionException;
use Battle\Exception\CommandException;
use Battle\Statistic\BattleStatistic;
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

        $this->assertEquals(4, $statistics->getRoundNumber());
    }

    public function testStrokeNumber(): void
    {
        $statistics = new BattleStatistic();

        $statistics->increasedStroke();
        $statistics->increasedStroke();
        $statistics->increasedStroke();

        $this->assertEquals(4, $statistics->getStrokeNumber());
    }

    public function testRoundAndStrokeNumber(): void
    {
        $statistics = new BattleStatistic();

        $statistics->increasedRound();
        $statistics->increasedStroke();
        $statistics->increasedRound();
        $statistics->increasedStroke();
        $statistics->increasedRound();

        $this->assertEquals(4, $statistics->getRoundNumber());
        $this->assertEquals(3, $statistics->getStrokeNumber());
    }

    /**
     * @throws ActionCollectionException
     * @throws Factory\UnitFactoryException
     * @throws CommandException
     * @throws ClassFactoryException
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
            $this->assertEquals(20, $statistics->getUnitsStatistics()[$attackUnit->getName()]->getCausedDamage());
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

        $this->assertEquals(150, $statistics->getUnitsStatistics()[$attackUnit->getName()]->getCausedDamage());
    }
}
