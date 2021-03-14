<?php

declare(strict_types=1);

namespace Tests\Battle\Unit;

use Battle\Classes\ClassFactory;
use Battle\Classes\ClassFactoryException;
use Battle\Classes\UnitClassInterface;
use Battle\Command\Command;
use Battle\Effect\Change\ChangeException;
use Battle\Effect\EffectException;
use Battle\Effect\EffectFactory;
use Battle\Unit\Unit;
use Battle\Action\DamageAction;
use Battle\Unit\UnitInterface;
use Exception;
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

        self::assertInstanceOf(UnitInterface::class, $unit);
        self::assertEquals($this->attackName, $unit->getName());
        self::assertEquals($this->attackDamage, $unit->getDamage());
        self::assertEquals($this->attackLife, $unit->getLife());
        self::assertEquals($this->attackLife, $unit->getTotalLife());
        self::assertEquals($this->attackAttackSpeed, $unit->getAttackSpeed());
        self::assertFalse($unit->isAction());
        self::assertTrue($unit->isAlive());
        self::assertTrue($unit->isMelee());
        self::assertEquals($attackClass->getId(), $unit->getClass()->getId());
    }

    public function testCreateFail(): void
    {
        $this->expectException(Throwable::class);
        new Unit();
    }

    /**
     * @throws Exception
     */
    public function testApplyDamage(): void
    {
        $attackClass = ClassFactory::create($this->attackClassId);
        $defendClass = ClassFactory::create($this->defendClassId);

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

        self::assertEquals($this->defendLife - $attackUnit->getDamage(), $defendUnit->getLife());

        $action2 = new DamageAction($attackUnit, $defendCommand);
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
        self::assertTrue($unit->isAction());
        self::assertEquals(0, $unit->getConcentration());
        $unit->newRound();
        self::assertFalse($unit->isAction());
        self::assertEquals(Unit::NEW_ROUND_ADD_CONS, $unit->getConcentration());
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
            self::assertEquals($effect, $effectItem);
        }
    }
}
