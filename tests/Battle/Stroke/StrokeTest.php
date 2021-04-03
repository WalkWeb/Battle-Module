<?php

declare(strict_types=1);

namespace Tests\Battle\Stroke;

use Battle\Chat\Chat;
use Battle\Classes\UnitClassFactory;
use Battle\Classes\ClassFactoryException;
use Battle\Classes\UnitClassInterface;
use Battle\Command\CommandException;
use Battle\Command\CommandFactory;
use Battle\Statistic\Statistic;
use Battle\Statistic\StatisticException;
use PHPUnit\Framework\TestCase;
use Battle\Stroke\Stroke;
use Battle\Unit\UnitInterface;
use Battle\Unit\Unit;

class StrokeTest extends TestCase
{
    private $attackerName   = 'first_unit';
    private $attackAvatar   = 'first_unit_ava';
    private $attackerDamage = 20;
    private $attackerAttackSpeed = 1.00;
    private $attackerLife   = 100;
    private $attackerMelee  = true;
    private $attackerClass  = UnitClassInterface::WARRIOR;

    private $defendName   = 'second_unit';
    private $defendAvatar = 'second_unit_ava';
    private $defendDamage = 25;
    private $defendAttackSpeed = 1.00;
    private $defendLife   = 70;
    private $defendMelee  = true;
    private $defendClass  = UnitClassInterface::PRIEST;

    /** @var UnitInterface */
    private $attackUnit;

    /** @var UnitInterface */
    private $defendUnit;

    /**
     * @throws ClassFactoryException
     */
    public function setUp(): void
    {
        $this->attackUnit = new Unit(
            $this->attackerName,
            $this->attackAvatar,
            $this->attackerDamage,
            $this->attackerAttackSpeed,
            $this->attackerLife,
            $this->attackerMelee,
            UnitClassFactory::create($this->attackerClass)
        );

        $this->defendUnit = new Unit(
            $this->defendName,
            $this->defendAvatar,
            $this->defendDamage,
            $this->defendAttackSpeed,
            $this->defendLife,
            $this->defendMelee,
            UnitClassFactory::create($this->defendClass)
        );
    }

    /**
     * @throws CommandException
     */
    public function testCreate(): void
    {
        $leftCommand = CommandFactory::create([$this->attackUnit]);
        $rightCommand = CommandFactory::create([$this->defendUnit]);

        $stroke = new Stroke(1, $this->attackUnit, $leftCommand, $rightCommand, new Statistic(), new Chat());

        self::assertInstanceOf(Stroke::class, $stroke);
    }

    /**
     * @throws CommandException
     * @throws StatisticException
     */
    public function testAction(): void
    {
        $leftCommand = CommandFactory::create([$this->attackUnit]);
        $rightCommand = CommandFactory::create([$this->defendUnit]);

        $stroke = new Stroke(1, $this->attackUnit, $leftCommand, $rightCommand, new Statistic(), new Chat());
        $stroke->handle();

        self::assertEquals($this->defendLife - $this->attackUnit->getDamage(), $this->defendUnit->getLife());
    }
}
