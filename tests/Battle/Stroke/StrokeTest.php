<?php

declare(strict_types=1);

namespace Tests\Battle\Stroke;

use Battle\Command\Command;
use Battle\Result\Chat\Chat;
use Battle\Result\FullLog\FullLog;
use Battle\Classes\UnitClassFactory;
use Battle\Classes\ClassFactoryException;
use Battle\Classes\UnitClassInterface;
use Battle\Command\CommandException;
use Battle\Command\CommandFactory;
use Battle\Statistic\Statistic;
use Battle\Statistic\StatisticException;
use Battle\Unit\UnitCollection;
use Battle\Unit\UnitException;
use PHPUnit\Framework\TestCase;
use Battle\Stroke\Stroke;
use Battle\Unit\UnitInterface;
use Battle\Unit\Unit;
use Tests\Battle\Factory\UnitFactory;
use Tests\Battle\Factory\UnitFactoryException;

class StrokeTest extends TestCase
{
    private $attackerId     = 'e9734617-2894-4af4-85ff-67bb30de0500';
    private $attackerName   = 'first_unit';
    private $attackerLevel  = 1;
    private $attackAvatar   = 'first_unit_ava';
    private $attackerDamage = 20;
    private $attackerAttackSpeed = 1.00;
    private $attackerLife   = 100;
    private $attackerMelee  = true;
    private $attackerClass  = UnitClassInterface::WARRIOR;

    private $defendId     = 'a385c54d-ad29-4071-b362-898b88a6d0c8';
    private $defendName   = 'second_unit';
    private $defendLevel  = 2;
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
            $this->attackerId,
            $this->attackerName,
            $this->attackerLevel,
            $this->attackAvatar,
            $this->attackerDamage,
            $this->attackerAttackSpeed,
            $this->attackerLife,
            $this->attackerLife,
            $this->attackerMelee,
            UnitClassFactory::create($this->attackerClass)
        );

        $this->defendUnit = new Unit(
            $this->defendId,
            $this->defendName,
            $this->defendLevel,
            $this->defendAvatar,
            $this->defendDamage,
            $this->defendAttackSpeed,
            $this->defendLife,
            $this->defendLife,
            $this->defendMelee,
            UnitClassFactory::create($this->defendClass)
        );
    }

    /**
     * Тест на базовую обработку одного хода
     *
     * @throws CommandException
     * @throws StatisticException
     * @throws UnitException
     */
    public function testStrokeHandle(): void
    {
        $leftCommand = CommandFactory::create([$this->attackUnit]);
        $rightCommand = CommandFactory::create([$this->defendUnit]);
        $statistics = new Statistic();
        $log = new FullLog();
        $chat = new Chat();

        $stroke = new Stroke(1, $this->attackUnit, $leftCommand, $rightCommand, $statistics, $log, $chat);
        $stroke->handle();

        self::assertEquals($this->defendLife - $this->attackUnit->getDamage(), $this->defendUnit->getLife());

        self::assertTrue($this->attackUnit->isAction());
        self::assertFalse($this->defendUnit->isAction());

        $chatResultMessages = [
            '<p class="none"><b>first_unit</b> attack <b>second_unit</b> on 20 damage</p>'
        ];
        self::assertEquals($chatResultMessages, $chat->getMessages());
    }

    /**
     * Тест на остановку внутри Stroke, например, когда юнит хочет сделать два удара, но противник умирает после первого
     *
     * @throws ClassFactoryException
     * @throws UnitFactoryException
     * @throws UnitException
     * @throws CommandException
     * @throws StatisticException
     */
    public function testStrokeBreakAction(): void
    {
        $leftUnit = UnitFactory::createByTemplate(13);
        $rightUnit = UnitFactory::createByTemplate(1);

        $leftCollection = new UnitCollection();
        $leftCollection->add($leftUnit);

        $rightCollection = new UnitCollection();
        $rightCollection->add($rightUnit);

        $leftCommand = new Command($leftCollection);
        $rightCommand = new Command($rightCollection);

        $stroke = new Stroke(1, $leftUnit, $leftCommand, $rightCommand, new Statistic(), new FullLog(), new Chat());

        // Для теста достаточно того, что выполнение хода завершилось без ошибок
        $stroke->handle();

        // Но, на всякий случай проверяем, что противник умер
        self::assertEquals(0, $rightUnit->getLife());
    }
}
