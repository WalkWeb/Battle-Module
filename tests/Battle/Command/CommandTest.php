<?php

declare(strict_types=1);

namespace Tests\Battle\Command;

use Battle\Classes\ClassFactoryException;
use Battle\Command\Command;
use Battle\Exception\CommandException;
use Battle\Unit\UnitInterface;
use PHPUnit\Framework\TestCase;
use Tests\Battle\Factory\UnitFactory;
use Tests\Battle\Factory\UnitFactoryException;
use Throwable;

class CommandTest extends TestCase
{
    /**
     * Проверяем успешное создание команды
     *
     * @throws ClassFactoryException
     * @throws CommandException
     * @throws UnitFactoryException
     */
    public function testCreate(): void
    {
        $unit = UnitFactory::create(1);
        $command = new Command([$unit]);
        self::assertInstanceOf(Command::class, $command);
    }

    /**
     * Проверяем соответствие переданных юнитов и тех, что возвращает команда
     *
     * @throws ClassFactoryException
     * @throws CommandException
     * @throws UnitFactoryException
     */
    public function testCommandUnits(): void
    {
        $units = [UnitFactory::create(1), UnitFactory::create(2)];
        $command = new Command($units);
        self::assertEquals($units, $command->getUnits());
    }

    /**
     * Проверяем корректный возврат юнитов ближнего и дальнего боя
     *
     * @throws CommandException
     * @throws UnitFactoryException
     * @throws ClassFactoryException
     */
    public function testMeleeAndRangeUnits(): void
    {
        $meleeUnit = UnitFactory::create(1);
        $rangeUnit = UnitFactory::create(5);
        $command = new Command([$meleeUnit, $rangeUnit]);

        self::assertEquals([$meleeUnit], $command->getMeleeUnits());
        self::assertEquals([$rangeUnit], $command->getRangeUnits());
    }

    /**
     * Проверяем корректное отсутствие бойцов ближнего боя
     *
     * @throws CommandException
     * @throws UnitFactoryException
     * @throws ClassFactoryException
     */
    public function testNoMeleeUnits(): void
    {
        $rangeUnit = UnitFactory::create(5);
        $command = new Command([$rangeUnit]);

        self::assertFalse($command->existMeleeUnits());
        self::assertEquals([], $command->getMeleeUnits());
        self::assertEquals([$rangeUnit], $command->getRangeUnits());
    }

    /**
     * Проверяем корректное отсутствие живых бойцов ближнего боя
     *
     * @throws CommandException
     * @throws UnitFactoryException
     * @throws ClassFactoryException
     */
    public function testNoAliveMeleeUnits(): void
    {
        $meleeUnit = UnitFactory::create(10);
        $rangeUnit = UnitFactory::create(5);
        $command = new Command([$meleeUnit, $rangeUnit]);
        self::assertFalse($command->existMeleeUnits());
    }

    /**
     * Проверяем неуспешное создание команды - не передан массив юнитов
     */
    public function testCreateFail(): void
    {
        $this->expectException(Throwable::class);
        new Command();
    }

    /**
     * Проверяем неуспешное создание команды - не передан пустой массив
     *
     * @throws CommandException
     */
    public function testNoUnits(): void
    {
        $this->expectException(CommandException::class);
        new Command([]);
    }

    /**
     * Проверяем неуспешное создание команды - передан некорректный объект
     *
     * @throws CommandException
     */
    public function testIncorrectUnit(): void
    {
        $this->expectException(CommandException::class);
        $array = ['name' => 'unit', 'damage' => 10, 'life' => 100];
        new Command([(object)$array]);
    }

    /**
     * Проверяем корректное возвращение юнита для атаки и наличие живых юнитов в команде
     *
     * @throws ClassFactoryException
     * @throws CommandException
     * @throws UnitFactoryException
     */
    public function testGetUserFromAttack(): void
    {
        $unit = UnitFactory::create(1);
        $command = new Command([$unit]);
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
     * @throws ClassFactoryException
     * @throws CommandException
     * @throws UnitFactoryException
     */
    public function testNoUnitFromAttack(): void
    {
        $unit = UnitFactory::createDeadUnit();
        $command = new Command([$unit]);
        self::assertEquals(null, $command->getUnitForAction());
        self::assertEquals(null, $command->getUnitForAttacks());
        self::assertEquals(false, $command->isAlive());
    }

    /**
     * Проверяем, что все юниты походили и на начало нового раунда - все юниты опять могут ходить
     *
     * @throws ClassFactoryException
     * @throws CommandException
     * @throws UnitFactoryException
     */
    public function testAllUnitAction(): void
    {
        $units = [UnitFactory::create(1), UnitFactory::create(2)];
        $command = new Command($units);

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
}
