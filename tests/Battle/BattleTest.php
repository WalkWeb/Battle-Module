<?php

declare(strict_types=1);

namespace Tests\Battle;

use Battle\BattleException;
use Battle\BattleFactory;
use Battle\Classes\ClassFactoryException;
use Battle\Command\Command;
use Battle\Command\CommandException;
use Battle\Result\Chat\Chat;
use Battle\Result\FullLog\FullLog;
use Battle\Statistic\Statistic;
use Battle\Unit\UnitCollection;
use Battle\Unit\UnitException;
use PHPUnit\Framework\TestCase;
use Battle\Battle;
use Tests\Battle\Factory\CommandFactory;
use Exception;
use Tests\Battle\Factory\UnitFactory;

class BattleTest extends TestCase
{
    /**
     * Тест на успешную обработку боя
     *
     * @throws Exception
     */
    public function testHandleBattleSuccess(): void
    {
        $leftCommand = CommandFactory::createLeftCommand();
        $rightCommand = CommandFactory::createRightCommand();

        $battle = new Battle($leftCommand, $rightCommand, new Statistic(), new FullLog(), new Chat());
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
                'id'           => 'a2763c19-7ec5-48f3-9242-2ea6c6d80c56',
                'name'         => 'Warrior',
                'level'        => 1,
                'avatar'       => '/images/avas/humans/human001.jpg',
                'damage'       => 7,
                'attack_speed' => 1.0,
                'life'         => 1500,
                'total_life'   => 1500,
                'melee'        => true,
                'class'        => 1,
                'command'      => 'left',
            ],
            [
                'id'           => '9dce83f3-2720-43c1-bf2b-0fb7dcacae53',
                'name'         => 'Skeleton',
                'level'        => 1,
                'avatar'       => '/images/avas/monsters/005.png',
                'damage'       => 5,
                'attack_speed' => 1.5,
                'life'         => 1650,
                'total_life'   => 1650,
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

    /**
     * Тест на ситуацию, когда передан массив юнитов с повторяющимися ID в разных командах
     *
     * @throws Exception
     */
    public function testBattleDoubleUnitId(): void
    {
        $data = [
            [
                'id'           => 'a2763c19-7ec5-48f3-9242-2ea6c6d80c56',
                'name'         => 'Warrior',
                'level'        => 1,
                'avatar'       => '/images/avas/humans/human001.jpg',
                'damage'       => 7,
                'attack_speed' => 1.0,
                'life'         => 1500,
                'total_life'   => 1500,
                'melee'        => true,
                'class'        => 1,
                'command'      => 'left',
            ],
            [
                'id'           => 'a2763c19-7ec5-48f3-9242-2ea6c6d80c56',
                'name'         => 'Skeleton',
                'level'        => 1,
                'avatar'       => '/images/avas/monsters/005.png',
                'damage'       => 5,
                'attack_speed' => 1.5,
                'life'         => 1650,
                'total_life'   => 1650,
                'melee'        => true,
                'class'        => 1,
                'command'      => 'right',
            ],
        ];

        $this->expectException(BattleException::class);
        $this->expectExceptionMessage(BattleException::DOUBLE_UNIT_ID);
        BattleFactory::create($data);
    }

    /**
     * Тест на ситуацию, когда передана некорректная коллекция юнитов - с одинаковыми ID
     *
     * @throws Factory\UnitFactoryException
     * @throws ClassFactoryException
     * @throws CommandException
     * @throws UnitException
     * @throws Exception
     */
    public function testBattleAgainDoubleUnitId(): void
    {
        $leftCommand = new Command($this->getUnitCollectionMock());
        $rightCommand = CommandFactory::createLeftCommand();

        $this->expectException(BattleException::class);
        $this->expectExceptionMessage(BattleException::DOUBLE_UNIT_ID);
        new Battle($leftCommand, $rightCommand, new Statistic(), new FullLog(), new Chat());
    }

    /**
     * Эмулируем коллекцию, которая будет возвращать одного и того же юнита на каждый вызов метода current()
     *
     * @return UnitCollection
     * @throws ClassFactoryException
     * @throws Factory\UnitFactoryException
     */
    private function getUnitCollectionMock(): UnitCollection
    {
        $stub = $this->createMock(UnitCollection::class);

        $unit = UnitFactory::createByTemplate(1);

        $stub->method('count')
            ->willReturn(2);

        $stub->method('current')
            ->willReturn($unit);

        $stub->method('valid')
            ->willReturn(true);

        return $stub;
    }
}
