<?php

declare(strict_types=1);

namespace Tests\Battle;

use Battle\Chat\Chat;
use Battle\Statistic\BattleStatistic;
use PHPUnit\Framework\TestCase;
use Battle\Battle;
use Battle\Command;
use Tests\Battle\Factory\UnitFactory;
use Exception;

class BattleTest extends TestCase
{
    /**
     * Тест на успешную обработку боя
     *
     * @throws Exception
     */
    public function testHandleBattleSuccess(): void
    {
        $unit1 = UnitFactory::create(1);
        $unit2 = UnitFactory::create(2);
        $command1 = new Command([$unit1]);
        $command2 = new Command([$unit2]);

        $battle = new Battle($command1, $command2, new BattleStatistic(), new Chat());
        $result = $battle->handle();

        self::assertEquals(2, $result->getWinner());
        self::assertInstanceOf(Battle::class, $battle);
        self::assertTrue($battle->getStatistics()->getRoundNumber() > 2);
        self::assertTrue($battle->getStatistics()->getStrokeNumber() > 4);
    }
}
