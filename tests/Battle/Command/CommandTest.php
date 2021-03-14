<?php

declare(strict_types=1);

namespace Tests\Battle\Command;

use Battle\Action\DamageAction;
use Battle\Classes\ClassFactory;
use Battle\Classes\ClassFactoryException;
use Battle\Command\Command;
use Battle\Exception\CommandException;
use Battle\Exception\DamageActionException;
use Battle\Unit\Unit;
use Battle\Unit\UnitCollection;
use Battle\Unit\UnitInterface;
use PHPUnit\Framework\TestCase;
use Tests\Battle\Factory\UnitFactory;
use Tests\Battle\Factory\UnitFactoryException;

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

        $collection = new UnitCollection();
        foreach ($units as $unit) {
            $collection->add($unit);
        }

        $command = new Command($units);
        self::assertEquals($collection, $command->getUnits());
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
     * Проверяем корректное возвращение юнита получения удара
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

    /**
     * Проверяем корректное возвращение юнита для совершения хода
     *
     * @throws ClassFactoryException
     * @throws CommandException
     * @throws UnitFactoryException
     */
    public function testGetUnitForActionOne(): void
    {
        $unit = UnitFactory::create(1);
        $command = new Command([$unit]);

        self::assertEquals($unit, $command->getUnitForAction());
    }

    /**
     * Проверяем корректное отсутствие юнитов для хода, когда один может ходить но мертвый, другой - живой но уже ходил
     *
     * @throws ClassFactoryException
     * @throws CommandException
     * @throws DamageActionException
     */
    public function testGetUnitForActionNothing(): void
    {
        $unit1 = new Unit('User 1', 15, 1, 110, true, ClassFactory::create(1));
        $unit2 = new Unit('User 2', 12, 1, 95, false, ClassFactory::create(2));
        $unit3 = new Unit('User 3', 120, 1, 300, true, ClassFactory::create(1));

        $command = new Command([$unit1, $unit2]);

        // вначале юнит присутствует
        self::assertInstanceOf(UnitInterface::class, $command->getUnitForAction());

        // убиваем первого юнита
        $action = new DamageAction($unit3, $command);
        $action->handle();

        // указываем, что второй юнит походил
        $unit2->madeAction();

        self::assertNull($command->getUnitForAction());
    }
}
