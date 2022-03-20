<?php

declare(strict_types=1);

namespace Tests\Battle;

use Battle\Translation\Translation;
use Exception;
use Battle\BattleException;
use Battle\BattleFactory;
use Battle\Command\Command;
use Battle\Command\CommandException;
use Battle\Container\Container;
use Battle\Unit\UnitCollection;
use Battle\Battle;
use Tests\AbstractUnitTest;
use Tests\Battle\Factory\CommandFactory;
use Tests\Battle\Factory\UnitFactory;

class BattleTest extends AbstractUnitTest
{
    /**
     * Тест на успешную обработку боя
     *
     * @throws Exception
     */
    public function testHandleBattleSuccess(): void
    {
        $command = CommandFactory::createLeftCommand();
        $enemyCommand = CommandFactory::createRightCommand();

        $container = new Container();
        $battle = new Battle($command, $enemyCommand, $container);
        $result = $battle->handle();

        self::assertEquals(2, $result->getWinner());
        self::assertInstanceOf(Battle::class, $battle);
        self::assertTrue($result->getStatistic()->getRoundNumber() > 2);
        self::assertTrue($result->getStatistic()->getStrokeNumber() > 4);
        self::assertEquals($container, $battle->getContainer());
    }

    /**
     * Тест на бой в котором сражающиеся очень толстые но с очень небольшим уроном - и бой заканчивается по лимиту
     * раундов. Победителем выбирается тот, у кого осталось больше здоровья
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
                'attack_speed' => 1,
                'block'        => 0,
                'block_ignore' => 0,
                'life'         => 25000,
                'total_life'   => 25000,
                'melee'        => true,
                'class'        => 1,
                'race'         => 1,
                'command'      => 1,
            ],
            [
                'id'           => '9dce83f3-2720-43c1-bf2b-0fb7dcacae53',
                'name'         => 'Skeleton',
                'level'        => 1,
                'avatar'       => '/images/avas/monsters/005.png',
                'damage'       => 5,
                'attack_speed' => 1,
                'block'        => 0,
                'block_ignore' => 0,
                'life'         => 2650,
                'total_life'   => 2650,
                'melee'        => true,
                'class'        => 1,
                'race'         => 8,
                'command'      => 2,
            ],
        ];

        $battle = BattleFactory::create($data);

        $result = $battle->handle();

        // Проверяем победителя
        self::assertEquals(1, $result->getWinner());

        // Проверяем, что бой закончился по лимиту (есть соответствующее сообщение в логах)
        $fullLog = '';
        foreach ($result->getFullLog()->getLog() as $log) {
            $fullLog .= $log;
        }

        self::assertIsInt(strripos($fullLog, Battle::LIMIT_ROUND_MESSAGE));
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
                'block'        => 0,
                'block_ignore' => 0,
                'life'         => 1500,
                'total_life'   => 1500,
                'melee'        => true,
                'class'        => 1,
                'race'         => 1,
                'command'      => 1,
            ],
            [
                'id'           => 'a2763c19-7ec5-48f3-9242-2ea6c6d80c56',
                'name'         => 'Skeleton',
                'level'        => 1,
                'avatar'       => '/images/avas/monsters/005.png',
                'damage'       => 5,
                'attack_speed' => 1.5,
                'block'        => 0,
                'block_ignore' => 0,
                'life'         => 1650,
                'total_life'   => 1650,
                'melee'        => true,
                'class'        => 1,
                'race'         => 8,
                'command'      => 2,
            ],
        ];

        $this->expectException(BattleException::class);
        $this->expectExceptionMessage(BattleException::DOUBLE_UNIT_ID);
        BattleFactory::create($data);
    }

    /**
     * Тест на ситуацию, когда передана некорректная коллекция юнитов - с одинаковыми ID
     *
     * @throws CommandException
     * @throws Exception
     */
    public function testBattleAgainDoubleUnitId(): void
    {
        $command = new Command($this->getUnitCollectionMock());
        $enemyCommand = CommandFactory::createLeftCommand();

        $this->expectException(BattleException::class);
        $this->expectExceptionMessage(BattleException::DOUBLE_UNIT_ID);
        new Battle($command, $enemyCommand, new Container());
    }

    /**
     * @throws Exception
     */
    public function testBattleSetTranslation(): void
    {
        $command = CommandFactory::createLeftCommand();
        $enemyCommand = CommandFactory::createRightCommand();

        $translator = new Translation('ru');
        $container = new Container();
        $container->set(Translation::class, $translator);

        $battle = new Battle($command, $enemyCommand, $container);

        self::assertEquals($translator, $battle->getContainer()->getTranslation());
    }

    /**
     * Эмулируем коллекцию, которая будет возвращать одного и того же юнита на каждый вызов метода current()
     *
     * @return UnitCollection
     * @throws Exception
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
