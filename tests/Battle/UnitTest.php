<?php

declare(strict_types=1);

namespace Tests;

use Battle\Classes\ClassFactory;
use Battle\Classes\ClassFactoryException;
use Battle\Classes\UnitClassInterface;
use Battle\Command;
use Battle\Effect\Change\ChangeException;
use Battle\Effect\EffectException;
use Battle\Effect\EffectFactory;
use Battle\Exception\CommandException;
use Battle\Exception\DamageActionException;
use Battle\Unit;
use Battle\Action\DamageAction;
use Battle\Exception\UserException;
use PHPUnit\Framework\TestCase;
use Throwable;

class UnitTest extends TestCase
{
    private $attackName = 'attack_unit';
    private $attackDamage = 40;
    private $attackAttackSpeed = 1.00;
    private $attackLife = 100;
    private $attackMelee = true;
    private $attackClassId = UnitClassInterface::WARRIOR;

    private $defendName = 'defend_unit';
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
        $attackClass = ClassFactory::create($this->attackClassId);

        $unit = new Unit(
            $this->attackName,
            $this->attackDamage,
            $this->attackAttackSpeed,
            $this->attackLife,
            $this->attackMelee,
            $attackClass
        );

        $this->assertInstanceOf(Unit::class, $unit);
        $this->assertEquals($this->attackName, $unit->getName());
        $this->assertEquals($this->attackDamage, $unit->getDamage());
        $this->assertEquals($this->attackLife, $unit->getLife());
        $this->assertEquals($this->attackLife, $unit->getTotalLife());
        $this->assertEquals($this->attackAttackSpeed, $unit->getAttackSpeed());
        $this->assertFalse($unit->isAction());
        $this->assertTrue($unit->isAlive());
        $this->assertTrue($unit->isMelee());
        $this->assertEquals($attackClass->getId(), $unit->getClass()->getId());
    }

    public function testCreateFail(): void
    {
        $this->expectException(Throwable::class);
        new Unit();
    }

    /**
     * @throws ClassFactoryException
     * @throws CommandException
     * @throws DamageActionException
     */
    public function testApplyDamage(): void
    {
        $attackClass = ClassFactory::create($this->attackClassId);
        $defendClass = ClassFactory::create($this->defendClassId);

        try {
            $attackUnit = new Unit(
                $this->attackName,
                $this->attackDamage,
                $this->attackAttackSpeed,
                $this->attackLife,
                $this->attackMelee,
                $attackClass
            );

            $defendUnit = new Unit(
                $this->defendName,
                $this->defendDamage,
                $this->defendAttackSpeed,
                $this->defendLife,
                $this->defendMelee,
                $defendClass
            );

            $defendCommand = new Command([$defendUnit]);
            $action = new DamageAction($attackUnit, $defendCommand);

            $action->handle();

            $this->assertEquals($this->defendLife - $attackUnit->getDamage(), $defendUnit->getLife());

            $action2 = new DamageAction($attackUnit, $defendCommand);
            $action2->handle();

            $this->assertEquals(0, $defendUnit->getLife());
            $this->assertFalse($defendUnit->isAlive());
        } catch (UserException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * Проверяем корректное обновление параметра action у юнита, при начале нового раунда, и добавление концентрации
     *
     * @throws ClassFactoryException
     */
    public function testAction(): void
    {
        $attackClass = ClassFactory::create($this->attackClassId);

        $unit = new Unit(
            $this->attackName,
            $this->attackDamage,
            $this->attackAttackSpeed,
            $this->attackLife,
            $this->attackMelee,
            $attackClass
        );

        $unit->madeAction();
        $this->assertTrue($unit->isAction());
        $this->assertEquals(0, $unit->getConcentration());
        $unit->newRound();
        $this->assertFalse($unit->isAction());
        $this->assertEquals(Unit::NEW_ROUND_ADD_CONS, $unit->getConcentration());
    }

    /**
     * @throws ClassFactoryException
     * @throws ChangeException
     * @throws EffectException
     */
    public function testAddEffect(): void
    {
        $attackClass = ClassFactory::create($this->attackClassId);

        $unit = new Unit(
            $this->attackName,
            $this->attackDamage,
            $this->attackAttackSpeed,
            $this->attackLife,
            $this->attackMelee,
            $attackClass
        );

        $effect = EffectFactory::create(1, $unit);

        $unit->addEffect($effect);

        $effects = $unit->getEffects();

        foreach ($effects as $effectItem) {
            $this->assertEquals($effect, $effectItem);
        }
    }
}
