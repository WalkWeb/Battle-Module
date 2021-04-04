<?php

declare(strict_types=1);

namespace Tests\Battle\Statistics;

use Battle\Command\CommandFactory;
use Battle\Statistic\Statistic;
use Exception;
use PHPUnit\Framework\TestCase;
use Tests\Battle\Factory\UnitFactory;

class StatisticsTest extends TestCase
{
    public function testStatisticsRoundNumber(): void
    {
        $statistics = new Statistic();

        $statistics->increasedRound();
        $statistics->increasedRound();
        $statistics->increasedRound();

        self::assertEquals(4, $statistics->getRoundNumber());
    }

    public function testStatisticsStrokeNumber(): void
    {
        $statistics = new Statistic();

        $statistics->increasedStroke();
        $statistics->increasedStroke();
        $statistics->increasedStroke();

        self::assertEquals(4, $statistics->getStrokeNumber());
    }

    public function testStatisticsRoundAndStrokeNumber(): void
    {
        $statistics = new Statistic();

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
    public function testStatisticsUnitCausedDamage(): void
    {
        $statistics = new Statistic();

        $attackUnit = UnitFactory::createByTemplate(1);
        $defendUnit = UnitFactory::createByTemplate(2);
        $enemyCommand = CommandFactory::create([$defendUnit]);
        $alliesCommand = CommandFactory::create([$attackUnit]);

        $actionCollection = $attackUnit->getAction($enemyCommand, $alliesCommand);

        foreach ($actionCollection->getActions() as $action) {
            $action->handle();
            $statistics->addUnitAction($action);
            self::assertEquals(20, $statistics->getUnitsStatistics()->getUnitByName($attackUnit->getName())->getCausedDamage());
        }

        // Делаем 10 ударов
        for ($i = 0; $i < 10; $i++) {
            $actionCollection = $attackUnit->getAction($enemyCommand, $alliesCommand);

            foreach ($actionCollection->getActions() as $action) {

                if (!$enemyCommand->isAlive()) {
                    break;
                }

                $action->handle();
                $statistics->addUnitAction($action);
            }
        }

        self::assertEquals(150, $statistics->getUnitsStatistics()->getUnitByName($attackUnit->getName())->getCausedDamage());
    }

    /**
     * Тест на время обработки боя
     */
    public function testStatisticsRuntime(): void
    {
        $statistic = new Statistic();

        // 5 миллисекунд
        usleep(5000);

        self::assertIsFloat($statistic->getRuntime());

        // Время выполнения больше 5 миллисекунд и меньше 6 (реально уходит 5.10 - 5.30 миллисекунд)
        self::assertTrue($statistic->getRuntime() > 5 && $statistic->getRuntime() < 6);
    }

    /**
     * Тест на затраченную память
     * @throws Exception
     */
    public function testStatisticsMemoryCost(): void
    {
        $statistic = new Statistic();

        random_bytes(1000000);

        // Расход памяти будет разным в зависимости от контекста выполнения теста
        self::assertIsInt($statistic->getMemoryCost());
        self::assertIsString($statistic->getMemoryCostClipped());
    }
}
