<?php

declare(strict_types=1);

namespace Tests\Battle\Unit;

use Battle\Classes\UnitClassFactory;
use Battle\Classes\ClassFactoryException;
use Battle\Classes\UnitClassInterface;
use Battle\Command\CommandFactory;
use Battle\Unit\Unit;
use Battle\Action\DamageAction;
use Battle\Unit\UnitInterface;
use Exception;
use PHPUnit\Framework\TestCase;

class UnitTest extends TestCase
{
    private $attackId = '3bc9b8be-8cbd-44b4-a935-cd435d905d1b';
    private $attackName = 'attack_unit';
    private $attackAvatar = 'attack_unit_ava';
    private $attackDamage = 40;
    private $attackAttackSpeed = 1.00;
    private $attackLife = 100;
    private $attackMelee = true;
    private $attackClassId = UnitClassInterface::WARRIOR;

    private $defendId = 'de03e3b9-21d1-439d-b336-8c7f000e5f59';
    private $defendName = 'defend_unit';
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
            $this->attackAvatar,
            $this->attackDamage,
            $this->attackAttackSpeed,
            $this->attackLife,
            $this->attackMelee,
            $attackClass
        );

        self::assertInstanceOf(UnitInterface::class, $unit);
        self::assertEquals($this->attackId, $unit->getId());
        self::assertEquals($this->attackName, $unit->getName());
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

        $attackClass = UnitClassFactory::create($this->attackClassId);
        $defendClass = UnitClassFactory::create($this->defendClassId);

        $attackUnit = new Unit(
            $this->attackId,
            $this->attackName,
            $this->attackAvatar,
            $this->attackDamage,
            $this->attackAttackSpeed,
            $this->attackLife,
            $this->attackMelee,
            $attackClass
        );

        $defendUnit = new Unit(
            $this->defendId,
            $this->defendName,
            $this->defendAvatar,
            $this->defendDamage,
            $this->defendAttackSpeed,
            $this->defendLife,
            $this->defendMelee,
            $defendClass
        );

        $enemyCommand = CommandFactory::create([$defendUnit]);
        $alliesCommand = CommandFactory::create([$attackUnit]);

        $action = new DamageAction($attackUnit, $enemyCommand, $alliesCommand);

        $action->handle();

        self::assertEquals($this->defendLife - $attackUnit->getDamage(), $defendUnit->getLife());

        $action2 = new DamageAction($attackUnit, $enemyCommand, $alliesCommand);
        $action2->handle();

        self::assertEquals(0, $defendUnit->getLife());
        self::assertFalse($defendUnit->isAlive());
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
            $this->attackAvatar,
            $this->attackDamage,
            $this->attackAttackSpeed,
            $this->attackLife,
            $this->attackMelee,
            $attackClass
        );

        $unit->madeAction();
        self::assertTrue($unit->isAction());
        self::assertEquals(0, $unit->getConcentration());
        $unit->newRound();
        self::assertFalse($unit->isAction());
        self::assertEquals(Unit::NEW_ROUND_ADD_CONS, $unit->getConcentration());
    }

    // todo Реализация добавления эффекта будет переделана

//    /**
//     * @throws ClassFactoryException
//     * @throws ChangeException
//     * @throws EffectException
//     */
//    public function testAddEffect(): void
//    {
//        $attackClass = UnitClassFactory::create($this->attackClassId);
//
//        $unit = new Unit(
//            $this->attackName,
//            $this->attackAvatar,
//            $this->attackDamage,
//            $this->attackAttackSpeed,
//            $this->attackLife,
//            $this->attackMelee,
//            $attackClass
//        );
//
//        $effect = EffectFactory::create(1, $unit);
//
//        $unit->addEffect($effect);
//
//        $effects = $unit->getEffects();
//
//        foreach ($effects as $effectItem) {
//            self::assertEquals($effect, $effectItem);
//        }
//    }
}
