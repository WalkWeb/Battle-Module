<?php

declare(strict_types=1);

namespace Tests\Battle\Result\Statistics;

use Exception;
use Battle\Action\DamageAction;
use Battle\Action\SummonAction;
use Battle\Command\CommandFactory;
use Battle\Result\Statistic\Statistic;
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

        foreach ($actionCollection as $action) {
            $action->handle();
            $statistics->addUnitAction($action);
            self::assertEquals(20, $statistics->getUnitsStatistics()->get($attackUnit->getId())->getCausedDamage());
        }

        // Делаем 10 ударов
        for ($i = 0; $i < 10; $i++) {
            $actionCollection = $attackUnit->getAction($enemyCommand, $alliesCommand);

            foreach ($actionCollection as $action) {

                if (!$enemyCommand->isAlive()) {
                    break;
                }

                $action->handle();
                $statistics->addUnitAction($action);
            }
        }

        $defendUnitData = UnitFactory::getData(2);

        self::assertEquals($defendUnitData['total_life'], $statistics->getUnitsStatistics()->get($attackUnit->getId())->getCausedDamage());
    }

    /**
     * @throws Exception
     */
    public function testStatisticsUnitCausedHeal(): void
    {
        $statistics = new Statistic();

        $dead = UnitFactory::createByTemplate(11);
        $priest = UnitFactory::createByTemplate(5);
        $enemy = UnitFactory::createByTemplate(1);

        $alliesCommand = CommandFactory::create([$priest, $dead]);
        $enemyCommand = CommandFactory::create([$enemy]);

        // Применяем лечение
        for ($i = 0; $i < 10; $i++) {
            $priest->newRound();
        }

        $actionCollection = $priest->getAction($enemyCommand, $alliesCommand);

        foreach ($actionCollection as $action) {
            $action->handle();
            $statistics->addUnitAction($action);
        }

        self::assertEquals($priest->getDamage() * 3, $statistics->getUnitsStatistics()->get($priest->getId())->getHeal());

        // И еще раз
        for ($i = 0; $i < 10; $i++) {
            $priest->newRound();
        }

        $actionCollection = $priest->getAction($enemyCommand, $alliesCommand);

        foreach ($actionCollection as $action) {
            $action->handle();
            $statistics->addUnitAction($action);
        }

        self::assertEquals($priest->getDamage() * 6, $statistics->getUnitsStatistics()->get($priest->getId())->getHeal());
    }

    /**
     * @throws Exception
     */
    public function testStatisticsUnitCountingSummons(): void
    {
        $statistics = new Statistic();

        $darkMage = UnitFactory::createByTemplate(7);
        $enemy = UnitFactory::createByTemplate(1);

        $command = CommandFactory::create([$darkMage]);
        $enemyCommand = CommandFactory::create([$enemy]);

        // Max concentration
        for ($i = 0; $i < 10; $i++) {
            $darkMage->newRound();
        }

        $actions = $darkMage->getAction($enemyCommand, $command);

        foreach ($actions as $action) {
            self::assertInstanceOf(SummonAction::class, $action);
            $action->handle();
            $statistics->addUnitAction($action);
        }

        self::assertEquals(1, $statistics->getUnitsStatistics()->get($darkMage->getId())->getSummons());
        self::assertCount(2, $command->getUnits());

        // Again
        for ($i = 0; $i < 10; $i++) {
            $darkMage->newRound();
        }

        $actions = $darkMage->getAction($enemyCommand, $command);

        foreach ($actions as $action) {
            self::assertInstanceOf(SummonAction::class, $action);
            $action->handle();
            $statistics->addUnitAction($action);
        }

        self::assertEquals(2, $statistics->getUnitsStatistics()->get($darkMage->getId())->getSummons());
        self::assertCount(3, $command->getUnits());
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
     *
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

    /**
     * Тест на подсчет убийств
     *
     * @throws Exception
     */
    public function testStatisticsKills(): void
    {
        $statistics = new Statistic();
        $unit = UnitFactory::createByTemplate(12);
        $defendUnit = UnitFactory::createByTemplate(1);
        $defendCommand = CommandFactory::create([$defendUnit]);
        $alliesCommand = CommandFactory::create([$unit]);

        $action = new DamageAction($unit, $defendCommand, $alliesCommand);
        $action->handle();
        $statistics->addUnitAction($action);

        self::assertFalse($defendUnit->isAlive());
        self::assertEquals(1, $statistics->getUnitsStatistics()->get($unit->getId())->getKilling());
    }
}
