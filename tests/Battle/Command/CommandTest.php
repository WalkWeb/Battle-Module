<?php

declare(strict_types=1);

namespace Tests\Battle\Command;

use Battle\Action\Damage\DamageAction;
use Battle\BattleException;
use Battle\Command\Command;
use Battle\Command\CommandException;
use Battle\Action\ActionException;
use Battle\Command\CommandFactory;
use Battle\Result\Chat\Message;
use Battle\Unit\Race\RaceException;
use Battle\Unit\Race\RaceFactory;
use Battle\Unit\Unit;
use Battle\Unit\UnitCollection;
use Battle\Unit\UnitException;
use Battle\Unit\UnitInterface;
use Exception;
use PHPUnit\Framework\TestCase;
use Tests\Battle\Factory\Mock\UnitMockFactory;
use Tests\Battle\Factory\UnitFactory;

class CommandTest extends TestCase
{
    /**
     * Проверяем успешное создание команды
     *
     * @throws CommandException
     * @throws UnitException
     * @throws Exception
     */
    public function testCreate(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $command = CommandFactory::create([$unit]);
        self::assertInstanceOf(Command::class, $command);
    }

    /**
     * Проверяем соответствие переданных юнитов и тех, что возвращает команда
     *
     * @throws CommandException
     * @throws UnitException
     * @throws Exception
     */
    public function testCommandUnits(): void
    {
        $units = [UnitFactory::createByTemplate(1), UnitFactory::createByTemplate(2)];

        $collection = new UnitCollection();
        foreach ($units as $unit) {
            $collection->add($unit);
        }

        $command = CommandFactory::create($units);
        self::assertEquals($collection, $command->getUnits());
    }

    /**
     * Проверяем корректный возврат юнитов ближнего и дальнего боя
     *
     * @throws CommandException
     * @throws UnitException
     * @throws Exception
     */
    public function testMeleeAndRangeUnits(): void
    {
        $meleeUnit = UnitFactory::createByTemplate(1);
        $rangeUnit = UnitFactory::createByTemplate(5);
        $command = CommandFactory::create([$meleeUnit, $rangeUnit]);

        $resultMeleeUnits = $command->getMeleeUnits();
        $resultRangeUnits = $command->getRangeUnits();

        self::assertCount(1, $resultMeleeUnits);
        self::assertCount(1, $resultRangeUnits);

        foreach ($resultMeleeUnits as $resultMeleeUnit) {
            self::assertEquals($meleeUnit, $resultMeleeUnit);
        }

        foreach ($resultRangeUnits as $resultRangeUnit) {
            self::assertEquals($rangeUnit, $resultRangeUnit);
        }
    }

    /**
     * Проверяем корректное отсутствие бойцов ближнего боя
     *
     * @throws CommandException
     * @throws UnitException
     * @throws Exception
     */
    public function testNoMeleeUnits(): void
    {
        $rangeUnit = UnitFactory::createByTemplate(5);
        $command = CommandFactory::create([$rangeUnit]);

        self::assertFalse($command->existMeleeUnits());
        self::assertCount(0, $command->getMeleeUnits());
    }

    /**
     * Проверяем корректное отсутствие живых бойцов ближнего боя
     *
     * @throws CommandException
     * @throws UnitException
     * @throws Exception
     */
    public function testNoAliveMeleeUnits(): void
    {
        $meleeUnit = UnitFactory::createByTemplate(10);
        $rangeUnit = UnitFactory::createByTemplate(5);
        $command = CommandFactory::create([$meleeUnit, $rangeUnit]);
        self::assertFalse($command->existMeleeUnits());
        self::assertNull($command->getMeleeUnitForAttacks());

        // Проверяем, что возвращается именно юнит дальнего боя
        self::assertEquals($rangeUnit->getId(), $command->getUnitForAttacks()->getId());
    }

    /**
     * Проверяем неуспешное создание команды - не передан пустой массив
     *
     * @throws CommandException
     */
    public function testNoUnits(): void
    {
        $this->expectException(CommandException::class);
        new Command(new UnitCollection());
    }

    /**
     * Проверяем неуспешное создание команды - передан некорректный объект
     *
     * @throws CommandException
     * @throws UnitException
     */
    public function testIncorrectUnit(): void
    {
        $this->expectException(CommandException::class);
        $array = ['name' => 'unit', 'damage' => 10, 'life' => 100];
        CommandFactory::create([(object)$array]);
    }

    /**
     * Проверяем корректное возвращение юнита для получения удара
     *
     * @throws CommandException
     * @throws UnitException
     * @throws Exception
     */
    public function testGetUserFromAttack(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $command = CommandFactory::create([$unit]);
        $defined = $command->getUnitForAttacks();

        self::assertTrue($command->isAlive());
        self::assertInstanceOf(UnitInterface::class, $defined);
        self::assertEquals($unit->getName(), $defined->getName());
        self::assertTrue($defined->isAlive());
        self::assertTrue($defined->getLife() > 0);
    }

    /**
     * Проверяем корректное отсутствие юнитов для атаки и отсутствие живых юнитов в команде
     *
     * @throws CommandException
     * @throws UnitException
     * @throws Exception
     */
    public function testNoUnitFromAttack(): void
    {
        $unit = UnitFactory::createByTemplate(10);
        $command = CommandFactory::create([$unit]);
        self::assertEquals(null, $command->getUnitForAction());
        self::assertEquals(null, $command->getUnitForAttacks());
        self::assertEquals(false, $command->isAlive());
    }

    /**
     * Проверяем, что все юниты походили и на начало нового раунда - все юниты опять могут ходить
     *
     * @throws CommandException
     * @throws UnitException
     * @throws Exception
     */
    public function testAllUnitAction(): void
    {
        $units = [UnitFactory::createByTemplate(1), UnitFactory::createByTemplate(2)];
        $command = CommandFactory::create($units);

        $firstActionUnit = $command->getUnitForAction();
        $firstActionUnit->madeAction();
        $secondActionUnit = $command->getUnitForAction();
        $secondActionUnit->madeAction();

        self::assertEquals(null, $command->getUnitForAction());

        $command->newRound();

        $firstActionUnit = $command->getUnitForAction();
        $firstActionUnit->madeAction();
        $secondActionUnit = $command->getUnitForAction();
        $secondActionUnit->madeAction();

        self::assertEquals(null, $command->getUnitForAction());
    }

    /**
     * Проверяем корректное возвращение юнита для совершения хода
     *
     * @throws CommandException
     * @throws UnitException
     * @throws Exception
     */
    public function testGetUnitForActionOne(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $command = CommandFactory::create([$unit]);

        self::assertEquals($unit, $command->getUnitForAction());
    }

    /**
     * Проверяем корректное отсутствие юнитов для хода, когда один может ходить но мертвый, другой - живой но уже ходил
     *
     * TODO Подумать над рефакторингом теста
     *
     * @throws ActionException
     * @throws CommandException
     * @throws UnitException
     * @throws BattleException
     * @throws RaceException
     */
    public function testGetUnitForActionNothing(): void
    {
        $message = new Message();

        $unit1 = new Unit(
            '19b871cd-f9e0-408c-aea0-2d903fd23806',
            'User 1',
            1,
            'ava 1',
            15,
            1,
            110,
            110,
            true,
            RaceFactory::create(1),
            $message
        );

        $unit2 = new Unit(
            'ac96be6b-4bb4-4636-8742-14001a7e2333',
            'User 2',
            1,
            'ava 2',
            12,
            1,
            95,
            95,
            false,
            RaceFactory::create(1),
            $message
        );

        $unit3 = new Unit(
            'baab87e7-4670-4ac3-a4cf-1fe0111935e8',
            'User 3',
            1,
            'ava 3',
            120,
            1,
            300,
            300,
            true,
            RaceFactory::create(1),
            $message
        );

        $alliesCommand = CommandFactory::create([$unit1, $unit2]);
        $enemyCommand = CommandFactory::create([$unit3]);

        // вначале юнит присутствует
        self::assertInstanceOf(UnitInterface::class, $alliesCommand->getUnitForAction());

        // убиваем первого юнита ($alliesCommand и $enemyCommand переставлены местами - это правильно, ходит вражеская команда)
        $action = new DamageAction($unit3, $alliesCommand, $enemyCommand, $message);
        $action->handle();

        // указываем, что второй юнит походил
        $unit2->madeAction();

        self::assertNull($alliesCommand->getUnitForAction());
    }

    /**
     * Тест на необычную ситуацию, когда юниты в команде вначале сообщают, что есть готовые ходить, а при попытке
     * вернуть такого юнита - его нет
     *
     * @throws CommandException
     * @throws UnitException
     */
    public function testCommandGetUnitForActionBroken(): void
    {
        $factory = new UnitMockFactory();
        $unit = $factory->create();
        $command = CommandFactory::create([$unit]);

        $this->expectException(CommandException::class);
        $this->expectExceptionMessage(CommandException::UNEXPECTED_EVENT_NO_ACTION_UNIT);
        $command->getUnitForAction();
    }
}
