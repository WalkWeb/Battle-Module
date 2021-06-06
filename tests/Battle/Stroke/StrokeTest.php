<?php

declare(strict_types=1);

namespace Tests\Battle\Stroke;

use Battle\Result\Chat\Chat;
use Battle\Result\FullLog\FullLog;
use Battle\Classes\ClassFactoryException;
use Battle\Command\CommandException;
use Battle\Command\CommandFactory;
use Battle\Statistic\Statistic;
use Battle\Statistic\StatisticException;
use Battle\Unit\UnitException;
use PHPUnit\Framework\TestCase;
use Battle\Stroke\Stroke;
use Tests\Battle\Factory\UnitFactory;
use Tests\Battle\Factory\UnitFactoryException;

class StrokeTest extends TestCase
{
    /**
     * Тест на базовую обработку одного хода
     *
     * @throws ClassFactoryException
     * @throws CommandException
     * @throws StatisticException
     * @throws UnitException
     * @throws UnitFactoryException
     */
    public function testStrokeHandle(): void
    {
        $leftUnit = UnitFactory::createByTemplate(1);
        $rightUnit = UnitFactory::createByTemplate(2);

        $leftCommand = CommandFactory::create([$leftUnit]);
        $rightCommand = CommandFactory::create([$rightUnit]);

        $chat = new Chat();

        $stroke = new Stroke(1, $leftUnit, $leftCommand, $rightCommand, new Statistic(), new FullLog(), $chat);
        $stroke->handle();

        self::assertEquals($rightUnit->getTotalLife() - $leftUnit->getDamage(), $rightUnit->getLife());

        self::assertTrue($leftUnit->isAction());
        self::assertFalse($rightUnit->isAction());

        $chatResultMessages = [
            '<p class="none"><b>unit_1</b> attack <b>unit_2</b> on 20 damage</p>'
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

        $leftCommand = CommandFactory::create([$leftUnit]);
        $rightCommand = CommandFactory::create([$rightUnit]);

        $stroke = new Stroke(1, $leftUnit, $leftCommand, $rightCommand, new Statistic(), new FullLog(), new Chat());

        // Для теста достаточно того, что выполнение хода завершилось без ошибок
        $stroke->handle();

        // Но, на всякий случай проверяем, что противник умер
        self::assertEquals(0, $rightUnit->getLife());
    }
}
