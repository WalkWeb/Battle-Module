<?php

declare(strict_types=1);

namespace Tests\Battle\Command;

use Battle\Container\Container;
use Exception;
use Battle\Battle;
use Battle\Command\Command;
use Battle\Command\CommandException;
use Battle\Command\CommandFactory;
use Battle\Result\Chat\Message;
use Battle\Unit\UnitCollection;
use Battle\Unit\UnitInterface;
use PHPUnit\Framework\TestCase;
use Battle\Action\Damage\DamageAction;
use Tests\Battle\Factory\UnitFactory;
use Tests\Battle\Factory\Mock\UnitMockFactory;
use Tests\Battle\Factory\CommandFactory as TestCommandFactory;

class CommandTest extends TestCase
{
    /**
     * Проверяем успешное создание команды
     *
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
     * @throws Exception
     */
    public function testNoUnits(): void
    {
        $this->expectException(CommandException::class);
        new Command(new UnitCollection());
    }

    /**
     * Проверяем неуспешное создание команды - передан некорректный объект
     *
     * @throws Exception
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
     * @throws Exception
     */
    public function testGetUnitForActionNothing(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $alliesUnit = UnitFactory::createByTemplate(5);
        $enemyUnit = UnitFactory::createByTemplate(12);

        $alliesCommand = CommandFactory::create([$unit, $alliesUnit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        // вначале юнит присутствует
        self::assertInstanceOf(UnitInterface::class, $alliesCommand->getUnitForAction());

        // убиваем первого юнита ($alliesCommand и $enemyCommand переставлены местами - это правильно, ходит вражеская команда)
        $action = new DamageAction($enemyUnit, $alliesCommand, $enemyCommand, new Message());
        $action->handle();

        // указываем, что второй юнит походил
        $alliesUnit->madeAction();

        self::assertNull($alliesCommand->getUnitForAction());
    }

    /**
     * Тест на необычную ситуацию, когда юниты в команде вначале сообщают, что есть готовые ходить, а при попытке
     * вернуть такого юнита - его нет
     *
     * @throws Exception
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

    /**
     * @throws Exception
     */
    public function testCommandClone(): void
    {
        $leftCommand = TestCommandFactory::createLeftCommand();
        $rightCommand = TestCommandFactory::createRightCommand();

        $battle = new Battle($leftCommand, $rightCommand, new Container());
        $result = $battle->handle();

        // Проверяем клонирование команд
        foreach ($result->getStartLeftCommand()->getUnits() as $unit) {
            self::assertEquals($unit->getLife(), $unit->getTotalLife());
        }

        foreach ($result->getStartRightCommand()->getUnits() as $unit) {
            self::assertEquals($unit->getLife(), $unit->getTotalLife());
        }
    }

    /**
     * @throws Exception
     */
    public function testCommandTotalLife(): void
    {
        $warrior = UnitFactory::createByTemplate(1);
        $priest = UnitFactory::createByTemplate(5);

        $command = CommandFactory::create([$warrior, $priest]);

        self::assertEquals($warrior->getTotalLife() + $priest->getTotalLife(), $command->getTotalLife());

        // Нанесем урон и проверим общее здоровье еще раз
        $zombie = UnitFactory::createByTemplate(1);
        $enemyCommand = CommandFactory::create([$zombie]);

        $damage = new DamageAction($zombie, $command, $enemyCommand, new Message());
        $damage->handle();

        self::assertEquals($warrior->getTotalLife() + $priest->getTotalLife() - $zombie->getDamage(), $command->getTotalLife());
    }
}
