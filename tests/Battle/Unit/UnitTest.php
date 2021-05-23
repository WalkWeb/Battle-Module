<?php

declare(strict_types=1);

namespace Tests\Battle\Unit;

use Battle\Classes\UnitClassFactory;
use Battle\Classes\ClassFactoryException;
use Battle\Classes\UnitClassInterface;
use Battle\Command\Command;
use Battle\Command\CommandException;
use Battle\Command\CommandFactory;
use Battle\Result\Chat\Message;
use Battle\Unit\Unit;
use Battle\Action\Damage\DamageAction;
use Battle\Unit\UnitCollection;
use Battle\Unit\UnitException;
use Battle\Unit\UnitInterface;
use Exception;
use PHPUnit\Framework\TestCase;
use Tests\Battle\Factory\Mock\ActionMockFactory;
use Tests\Battle\Factory\UnitFactory;
use Tests\Battle\Factory\UnitFactoryException;

class UnitTest extends TestCase
{
    private $attackId = '3bc9b8be-8cbd-44b4-a935-cd435d905d1b';
    private $attackName = 'attack_unit';
    private $attackLevel = 1;
    private $attackAvatar = 'attack_unit_ava';
    private $attackDamage = 40;
    private $attackAttackSpeed = 1.00;
    private $attackLife = 100;
    private $attackMelee = true;
    private $attackClassId = UnitClassInterface::WARRIOR;

    private $defendId = 'de03e3b9-21d1-439d-b336-8c7f000e5f59';
    private $defendName = 'defend_unit';
    private $defendLevel = 2;
    private $defendAvatar = 'defend_unit_ava';
    private $defendDamage = 30;
    private $defendAttackSpeed = 1.00;
    private $defendLife = 60;
    private $defendMelee = true;
    private $defendClassId = UnitClassInterface::PRIEST;

    /**
     * @throws ClassFactoryException
     */
    public function testCreate(): void
    {
        $attackClass = UnitClassFactory::create($this->attackClassId);

        $unit = new Unit(
            $this->attackId,
            $this->attackName,
            $this->attackLevel,
            $this->attackAvatar,
            $this->attackDamage,
            $this->attackAttackSpeed,
            $this->attackLife,
            $this->attackLife,
            $this->attackMelee,
            $attackClass,
            new Message()
        );

        self::assertInstanceOf(UnitInterface::class, $unit);
        self::assertEquals($this->attackId, $unit->getId());
        self::assertEquals($this->attackName, $unit->getName());
        self::assertEquals($this->attackLevel, $unit->getLevel());
        self::assertEquals($this->attackAvatar, $unit->getAvatar());
        self::assertEquals($this->attackDamage, $unit->getDamage());
        self::assertEquals($this->attackLife, $unit->getLife());
        self::assertEquals($this->attackLife, $unit->getTotalLife());
        self::assertEquals($this->attackAttackSpeed, $unit->getAttackSpeed());
        self::assertFalse($unit->isAction());
        self::assertTrue($unit->isAlive());
        self::assertTrue($unit->isMelee());
        self::assertEquals($attackClass->getId(), $unit->getClass()->getId());
    }

    /**
     * @throws Exception
     */
    public function testApplyDamage(): void
    {
        $message = new Message();

        $attackClass = UnitClassFactory::create($this->attackClassId);
        $defendClass = UnitClassFactory::create($this->defendClassId);

        $attackUnit = new Unit(
            $this->attackId,
            $this->attackName,
            $this->attackLevel,
            $this->attackAvatar,
            $this->attackDamage,
            $this->attackAttackSpeed,
            $this->attackLife,
            $this->attackLife,
            $this->attackMelee,
            $attackClass,
            $message
        );

        $defendUnit = new Unit(
            $this->defendId,
            $this->defendName,
            $this->defendLevel,
            $this->defendAvatar,
            $this->defendDamage,
            $this->defendAttackSpeed,
            $this->defendLife,
            $this->defendLife,
            $this->defendMelee,
            $defendClass,
            $message
        );

        $enemyCommand = CommandFactory::create([$defendUnit], $message);
        $alliesCommand = CommandFactory::create([$attackUnit], $message);

        $action = new DamageAction($attackUnit, $enemyCommand, $alliesCommand, $message);

        $action->handle();

        self::assertEquals($this->defendLife - $attackUnit->getDamage(), $defendUnit->getLife());

        $action2 = new DamageAction($attackUnit, $enemyCommand, $alliesCommand, $message);
        $action2->handle();

        self::assertEquals(0, $defendUnit->getLife());
        self::assertFalse($defendUnit->isAlive());
    }

    /**
     * @throws ClassFactoryException
     * @throws UnitFactoryException
     */
    public function testUnitUnknownAction(): void
    {
        $factory = new ActionMockFactory();
        $action = $factory->createDamageActionMock('unknownMethod');
        $unit = UnitFactory::createByTemplate(1);

        $this->expectException(UnitException::class);
        $this->expectExceptionMessage(UnitException::UNDEFINED_ACTION_METHOD);
        $unit->applyAction($action);
    }

    /**
     * Проверяем корректное обновление параметра action у юнита, при начале нового раунда, и добавление концентрации
     *
     * @throws ClassFactoryException
     */
    public function testAction(): void
    {
        $attackClass = UnitClassFactory::create($this->attackClassId);

        $unit = new Unit(
            $this->attackId,
            $this->attackName,
            $this->attackLevel,
            $this->attackAvatar,
            $this->attackDamage,
            $this->attackAttackSpeed,
            $this->attackLife,
            $this->attackLife,
            $this->attackMelee,
            $attackClass,
            new Message()
        );

        $unit->madeAction();
        self::assertTrue($unit->isAction());
        self::assertEquals(0, $unit->getConcentration());
        $unit->newRound();
        self::assertFalse($unit->isAction());
        self::assertEquals(Unit::ADD_CON_NEW_ROUND, $unit->getConcentration());
    }

    /**
     * Проверяем корректное добавление концентрации юниту, при начале нового раунда
     *
     * @throws ClassFactoryException
     * @throws UnitFactoryException
     */
    public function testUnitNewRoundAddConcentration(): void
    {
        $unit = UnitFactory::createByTemplate(1);

        self::assertEquals(0 , $unit->getConcentration());

        $unit->newRound();

        self::assertEquals(UnitInterface::ADD_CON_NEW_ROUND, $unit->getConcentration());
    }

    /**
     * Тест на получение концентрации при совершении действия, и получения действия от другого юнита
     *
     * @throws ClassFactoryException
     * @throws UnitFactoryException
     * @throws CommandException
     * @throws UnitException
     */
    public function testUnitAddConcentration(): void
    {
        $leftUnit = UnitFactory::createByTemplate(1);
        $leftCollection = new UnitCollection();
        $leftCollection->add($leftUnit);
        $leftCommand = new Command($leftCollection);

        $rightUnit =  UnitFactory::createByTemplate(2);
        $rightCollection = new UnitCollection();
        $rightCollection->add($rightUnit);
        $rightCommand = new Command($rightCollection);

        self::assertEquals(0, $leftUnit->getConcentration());
        self::assertEquals(0, $rightUnit->getConcentration());

        $actionCollection = $leftUnit->getAction($rightCommand, $leftCommand);

        self::assertEquals(UnitInterface::ADD_CON_ACTION_UNIT, $leftUnit->getConcentration());

        foreach ($actionCollection as $action) {
            $action->handle();
        }

        self::assertEquals(UnitInterface::ADD_CON_RECEIVING_UNIT, $rightUnit->getConcentration());
    }
}
