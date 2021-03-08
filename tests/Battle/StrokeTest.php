<?php

declare(strict_types=1);

namespace Tests;

use Battle\Chat\Chat;
use Battle\Classes\ClassFactory;
use Battle\Classes\ClassFactoryException;
use Battle\Classes\UnitClassInterface;
use Battle\Command;
use Battle\Exception\ActionCollectionException;
use Battle\Exception\CommandException;
use Battle\Statistic\BattleStatistic;
use PHPUnit\Framework\TestCase;
use Battle\Stroke;
use Battle\Unit;
use Throwable;

class StrokeTest extends TestCase
{
    private $attackerName   = 'first_unit';
    private $attackerDamage = 20;
    private $attackerAttackSpeed = 1.00;
    private $attackerLife   = 100;
    private $attackerMelee  = true;
    private $attackerClass = UnitClassInterface::WARRIOR;

    private $defendName   = 'second_unit';
    private $defendDamage = 25;
    private $defendAttackSpeed = 1.00;
    private $defendLife   = 70;
    private $defendMelee  = true;
    private $defendClass = UnitClassInterface::PRIEST;

    /** @var Unit */
    private $attackUnit;

    /** @var Unit */
    private $defendUnit;

    /**
     * @throws ClassFactoryException
     */
    public function setUp(): void
    {
        $this->attackUnit = new Unit(
            $this->attackerName,
            $this->attackerDamage,
            $this->attackerAttackSpeed,
            $this->attackerLife,
            $this->attackerMelee,
            ClassFactory::create($this->attackerClass)
        );

        $this->defendUnit = new Unit(
            $this->defendName,
            $this->defendDamage,
            $this->defendAttackSpeed,
            $this->defendLife,
            $this->defendMelee,
            ClassFactory::create($this->defendClass)
        );
    }

    public function testCreate(): void
    {
        try {
            $leftCommand = new Command([$this->attackUnit]);
            $rightCommand = new Command([$this->defendUnit]);

            $stroke = new Stroke(1, $this->attackUnit, $leftCommand, $rightCommand, new BattleStatistic(), new Chat());

            $this->assertInstanceOf(Stroke::class, $stroke);
        } catch (CommandException $e) {
            $this->fail();
        }
    }

    public function testCreateFail(): void
    {
        $this->expectException(Throwable::class);
        new Stroke();
    }

    /**
     * @throws ActionCollectionException
     */
    public function testAction(): void
    {
        try {
            $leftCommand = new Command([$this->attackUnit]);
            $rightCommand = new Command([$this->defendUnit]);

            $stroke = new Stroke(1, $this->attackUnit, $leftCommand, $rightCommand, new BattleStatistic(), new Chat());
            $stroke->handle();

            $this->assertEquals($this->defendLife - $this->attackUnit->getDamage(), $this->defendUnit->getLife());
        } catch (CommandException $e) {
            $this->fail($e->getMessage());
        }
    }
}
