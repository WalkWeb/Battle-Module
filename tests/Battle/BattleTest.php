<?php

declare(strict_types=1);

namespace Tests\Battle;

use Battle\BattleException;
use Battle\BattleFactory;
use Battle\Chat\Chat;
use Battle\Statistic\Statistic;
use PHPUnit\Framework\TestCase;
use Battle\Battle;
use Battle\Command\Command;
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
        $unit1 = UnitFactory::createByTemplate(1);
        $unit2 = UnitFactory::createByTemplate(2);
        $command1 = new Command([$unit1]);
        $command2 = new Command([$unit2]);

        $battle = new Battle($command1, $command2, new Statistic(), new Chat());
        $result = $battle->handle();

        self::assertEquals(2, $result->getWinner());
        self::assertInstanceOf(Battle::class, $battle);
        self::assertTrue($result->getStatistic()->getRoundNumber() > 2);
        self::assertTrue($result->getStatistic()->getStrokeNumber() > 4);
    }

    /**
     * Тест на бой в котором сражающиеся очень толстые но с очень небольшим уроном - и бой заканчивается по лимиту
     * раундов
     *
     * @throws Exception
     */
    public function testHandleBattleLimitRound(): void
    {
        $data = [
            [
                'name'         => 'Warrior',
                'avatar'       => '/images/avas/humans/human001.jpg',
                'damage'       => 7,
                'attack_speed' => 1.0,
                'life'         => 1500,
                'melee'        => true,
                'class'        => 1,
                'command'      => 'left',
            ],
            [
                'name'         => 'Skeleton',
                'avatar'       => '/images/avas/monsters/005.png',
                'damage'       => 5,
                'attack_speed' => 1.5,
                'life'         => 1650,
                'melee'        => true,
                'class'        => 1,
                'command'      => 'right',
            ],
        ];

        $battle = BattleFactory::create($data);

        $this->expectException(BattleException::class);
        $this->expectExceptionMessage(BattleException::UNEXPECTED_ENDING_BATTLE);

        $battle->handle();
    }
}
