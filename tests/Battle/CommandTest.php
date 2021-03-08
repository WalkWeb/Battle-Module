<?php

declare(strict_types=1);

namespace Tests;

use Battle\Classes\ClassFactoryException;
use Battle\Command;
use Battle\Exception\CommandException;
use Battle\Unit;
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
     */
    public function testCreate(): void
    {
        try {
            $unit = UnitFactory::create(1);
            $command = new Command([$unit]);
            $this->assertInstanceOf(Command::class, $command);
        } catch (UnitFactoryException | CommandException $e) {
            $this->fail($e->getMessage());
        }

    }

    /**
     * Проверяем соответствие переданных юнитов и тех, что возвращает команда
     *
     * @throws ClassFactoryException
     */
    public function testCommandUnits(): void
    {
        try {
            $units = [UnitFactory::create(1), UnitFactory::create(2)];
            $command = new Command($units);
            $this->assertEquals($units, $command->getUnits());
        } catch (UnitFactoryException | CommandException $e) {
            $this->fail($e->getMessage());
        }
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

        $this->assertEquals([$meleeUnit], $command->getMeleeUnits());
        $this->assertEquals([$rangeUnit], $command->getRangeUnits());
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

        $this->assertFalse($command->existMeleeUnits());
        $this->assertEquals([], $command->getMeleeUnits());
        $this->assertEquals([$rangeUnit], $command->getRangeUnits());
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
        $this->assertFalse($command->existMeleeUnits());
    }

    /**
     * Проверяем неуспешное создание команды - не передан массив юнитов
     *
     * @throws CommandException
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
     */
    public function testGetUserFromAttack(): void
    {
        try {
            $unit = UnitFactory::create(1);
            $command = new Command([$unit]);
            $defined = $command->getUnitForAttacks();

            $this->assertTrue($command->isAlive());
            $this->assertInstanceOf(Unit::class, $defined);
            $this->assertEquals($unit->getName(), $defined->getName());
            $this->assertTrue($defined->isAlive());
            $this->assertTrue($defined->getLife() > 0);
        } catch (UnitFactoryException | CommandException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * Проверяем корректное отсутствие юнитов для атаки и отсутствие живых юнитов в команде
     *
     * @throws ClassFactoryException
     */
    public function testNoUnitFromAttack(): void
    {
        try {
            $unit = UnitFactory::createDeadUnit();
            $command = new Command([$unit]);
            $this->assertEquals(null, $command->getUnitForAction());
            $this->assertEquals(null, $command->getUnitForAttacks());
            $this->assertEquals(false, $command->isAlive());
        } catch (UnitFactoryException | CommandException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * Проверяем, что все юниты походили и начало нового раунда - все юниты опять могут ходить
     *
     * @throws ClassFactoryException
     */
    public function testAllUnitAction(): void
    {
        try {
            $units = [UnitFactory::create(1), UnitFactory::create(2)];
            $command = new Command($units);

            $firstActionUnit = $command->getUnitForAction();
            $firstActionUnit->madeAction();
            $secondActionUnit = $command->getUnitForAction();
            $secondActionUnit->madeAction();

            $this->assertEquals(null, $command->getUnitForAction());

            $command->newRound();

            $firstActionUnit = $command->getUnitForAction();
            $firstActionUnit->madeAction();
            $secondActionUnit = $command->getUnitForAction();
            $secondActionUnit->madeAction();

            $this->assertEquals(null, $command->getUnitForAction());

        } catch (UnitFactoryException | CommandException $e) {
            $this->fail($e->getMessage());
        }
    }
}
