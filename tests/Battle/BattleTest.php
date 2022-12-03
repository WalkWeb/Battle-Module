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
                'id'                           => 'a2763c19-7ec5-48f3-9242-2ea6c6d80c56',
                'name'                         => 'Warrior',
                'level'                        => 1,
                'avatar'                       => '/images/avas/humans/human001.jpg',
                'life'                         => 25000,
                'total_life'                   => 25000,
                'mana'                         => 50,
                'total_mana'                   => 50,
                'melee'                        => true,
                'class'                        => 1,
                'race'                         => 1,
                'command'                      => 1,
                'add_concentration_multiplier' => 0,
                'offense'                      => [
                    'damage_type'         => 1,
                    'weapon_type'         => 1,
                    'physical_damage'     => 7,
                    'fire_damage'         => 0,
                    'water_damage'        => 0,
                    'air_damage'          => 0,
                    'earth_damage'        => 0,
                    'life_damage'         => 0,
                    'death_damage'        => 0,
                    'attack_speed'        => 1,
                    'cast_speed'          => 0,
                    'accuracy'            => 200,
                    'magic_accuracy'      => 100,
                    'block_ignoring'      => 0,
                    'critical_chance'     => 5,
                    'critical_multiplier' => 200,
                    'vampirism'           => 0,
                ],
                'defense'                      => [
                    'physical_resist' => 30,
                    'fire_resist'     => 0,
                    'water_resist'    => 0,
                    'air_resist'      => 0,
                    'earth_resist'    => 0,
                    'life_resist'     => 0,
                    'death_resist'    => 0,
                    'defense'         => 100,
                    'magic_defense'   => 50,
                    'block'           => 0,
                    'magic_block'     => 0,
                    'mental_barrier'  => 0,
                ],
            ],
            [
                'id'                           => '9dce83f3-2720-43c1-bf2b-0fb7dcacae53',
                'name'                         => 'Skeleton',
                'level'                        => 1,
                'avatar'                       => '/images/avas/monsters/005.png',
                'life'                         => 2650,
                'total_life'                   => 2650,
                'mana'                         => 50,
                'total_mana'                   => 50,
                'melee'                        => true,
                'class'                        => 1,
                'race'                         => 8,
                'command'                      => 2,
                'add_concentration_multiplier' => 0,
                'offense'                      => [
                    'damage_type'         => 1,
                    'weapon_type'         => 1,
                    'physical_damage'     => 5,
                    'fire_damage'         => 0,
                    'water_damage'        => 0,
                    'air_damage'          => 0,
                    'earth_damage'        => 0,
                    'life_damage'         => 0,
                    'death_damage'        => 0,
                    'attack_speed'        => 1,
                    'cast_speed'          => 0,
                    'accuracy'            => 200,
                    'magic_accuracy'      => 100,
                    'block_ignoring'      => 0,
                    'critical_chance'     => 5,
                    'critical_multiplier' => 200,
                    'vampirism'           => 0,
                ],
                'defense'                      => [
                    'physical_resist' => 30,
                    'fire_resist'     => 0,
                    'water_resist'    => 0,
                    'air_resist'      => 0,
                    'earth_resist'    => 0,
                    'life_resist'     => 0,
                    'death_resist'    => 0,
                    'defense'         => 100,
                    'magic_defense'   => 50,
                    'block'           => 0,
                    'magic_block'     => 0,
                    'mental_barrier'  => 0,
                ],
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
                'id'                           => 'a2763c19-7ec5-48f3-9242-2ea6c6d80c56',
                'name'                         => 'Warrior',
                'level'                        => 1,
                'avatar'                       => '/images/avas/humans/human001.jpg',
                'block_ignoring'               => 0,
                'life'                         => 1500,
                'total_life'                   => 1500,
                'mana'                         => 50,
                'total_mana'                   => 50,
                'melee'                        => true,
                'class'                        => 1,
                'race'                         => 1,
                'command'                      => 1,
                'add_concentration_multiplier' => 0,
                'offense'                      => [
                    'damage_type'         => 1,
                    'weapon_type'         => 1,
                    'physical_damage'     => 15,
                    'fire_damage'         => 0,
                    'water_damage'        => 0,
                    'air_damage'          => 0,
                    'earth_damage'        => 0,
                    'life_damage'         => 0,
                    'death_damage'        => 0,
                    'attack_speed'        => 1.2,
                    'cast_speed'          => 0,
                    'accuracy'            => 200,
                    'magic_accuracy'      => 100,
                    'block_ignoring'      => 0,
                    'critical_chance'     => 5,
                    'critical_multiplier' => 200,
                    'vampirism'           => 0,
                ],
                'defense'                      => [
                    'physical_resist' => 30,
                    'fire_resist'     => 0,
                    'water_resist'    => 0,
                    'air_resist'      => 0,
                    'earth_resist'    => 0,
                    'life_resist'     => 0,
                    'death_resist'    => 0,
                    'defense'         => 100,
                    'magic_defense'   => 50,
                    'block'           => 0,
                    'magic_block'     => 0,
                    'mental_barrier'  => 0,
                ],
            ],
            [
                'id'                           => 'a2763c19-7ec5-48f3-9242-2ea6c6d80c56',
                'name'                         => 'Skeleton',
                'level'                        => 1,
                'avatar'                       => '/images/avas/monsters/005.png',
                'life'                         => 1650,
                'total_life'                   => 1650,
                'mana'                         => 50,
                'total_mana'                   => 50,
                'melee'                        => true,
                'class'                        => 1,
                'race'                         => 8,
                'command'                      => 2,
                'add_concentration_multiplier' => 0,
                'offense'                      => [
                    'damage_type'         => 1,
                    'weapon_type'         => 1,
                    'physical_damage'     => 15,
                    'fire_damage'         => 0,
                    'water_damage'        => 0,
                    'air_damage'          => 0,
                    'earth_damage'        => 0,
                    'life_damage'         => 0,
                    'death_damage'        => 0,
                    'attack_speed'        => 1.2,
                    'cast_speed'          => 0,
                    'accuracy'            => 200,
                    'magic_accuracy'      => 100,
                    'block_ignoring'      => 0,
                    'critical_chance'     => 5,
                    'critical_multiplier' => 200,
                    'vampirism'           => 0,
                ],
                'defense'                      => [
                    'physical_resist' => 30,
                    'fire_resist'     => 0,
                    'water_resist'    => 0,
                    'air_resist'      => 0,
                    'earth_resist'    => 0,
                    'life_resist'     => 0,
                    'death_resist'    => 0,
                    'defense'         => 100,
                    'magic_defense'   => 50,
                    'block'           => 0,
                    'magic_block'     => 0,
                    'mental_barrier'  => 0,
                ],
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
     * Тест на фиксированное указание начинающей команды
     *
     * @throws Exception
     */
    public function testBattleStartActionCommand(): void
    {
        $actionUnit = UnitFactory::createByTemplate(12);
        $enemyUnit = UnitFactory::createByTemplate(17);
        $command = \Battle\Command\CommandFactory::create([$actionUnit]);
        $enemyCommand = \Battle\Command\CommandFactory::create([$enemyUnit]);
        $container = new Container(true);

        // Начинает левая команда, т.е. $actionUnit
        $actionCommand = 1;

        $battle = new Battle($command, $enemyCommand, $container, $actionCommand);
        $result = $battle->handle();

        $scenario = $result->getScenario();

        // Весь бой (и его сценарий) должен состоять из 1 удара $actionUnit
        self::assertCount(1, $scenario->getArray());
        self::assertEquals($actionUnit->getId(), $scenario->getArray()[0]['effects'][0]['user_id']);
    }

    /**
     * Тест на ситуацию, когда передан некорректный $actionCommand - получаем исключение
     *
     * @throws Exception
     */
    public function testBattleInvalidActionCommand(): void
    {
        $command = CommandFactory::createLeftCommand();
        $enemyCommand = CommandFactory::createRightCommand();

        $container = new Container();

        $this->expectException(BattleException::class);
        $this->expectExceptionMessage(BattleException::INCORRECT_START_COMMAND);
        new Battle($command, $enemyCommand, $container, 3);
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
