<?php

declare(strict_types=1);

namespace Tests\Battle\Stroke;

use Battle\Stroke\StrokeException;
use Battle\Unit\Race\RaceFactory;
use Exception;
use Battle\Container\Container;
use Battle\Command\CommandFactory;
use PHPUnit\Framework\TestCase;
use Battle\Stroke\Stroke;
use Tests\Battle\Factory\Mock\BrokenPriestUnit;
use Tests\Battle\Factory\UnitFactory;

class StrokeTest extends TestCase
{
    private const MESSAGE = '<span style="color: #1e72e3">unit_1</span> attack <span style="color: #1e72e3">unit_2</span> on 20 damage';

    /**
     * Тест на базовую обработку одного хода
     *
     * @throws Exception
     */
    public function testStrokeHandle(): void
    {
        $leftUnit = UnitFactory::createByTemplate(1);
        $rightUnit = UnitFactory::createByTemplate(2);

        $leftCommand = CommandFactory::create([$leftUnit]);
        $rightCommand = CommandFactory::create([$rightUnit]);

        $container = new Container();

        $stroke = new Stroke(1, $leftUnit, $leftCommand, $rightCommand, $container);
        $stroke->handle();

        self::assertEquals($rightUnit->getTotalLife() - $leftUnit->getDamage(), $rightUnit->getLife());

        self::assertTrue($leftUnit->isAction());
        self::assertFalse($rightUnit->isAction());

        $chatResultMessages = [
            self::MESSAGE,
        ];

        self::assertEquals($chatResultMessages, $container->getChat()->getMessages());
    }

    /**
     * Тест на остановку внутри Stroke, например, когда юнит хочет сделать два удара, но противник умирает после первого
     *
     * @throws Exception
     */
    public function testStrokeBreakAction(): void
    {
        $leftUnit = UnitFactory::createByTemplate(13);
        $rightUnit = UnitFactory::createByTemplate(1);

        $leftCommand = CommandFactory::create([$leftUnit]);
        $rightCommand = CommandFactory::create([$rightUnit]);

        $stroke = new Stroke(1, $leftUnit, $leftCommand, $rightCommand, new Container());

        // Для теста достаточно того, что выполнение хода завершилось без ошибок
        $stroke->handle();

        // Но, на всякий случай проверяем, что противник умер
        self::assertEquals(0, $rightUnit->getLife());
    }

    /**
     * Сложный тест на эмуляцию ситуации, когда Stroke не может выполнить полученный Action
     *
     * Сложный тем, что юнит проверяет событие на возможность использование. Значит, делаем мок юнита, который вернет
     * именно лечение, хотя и лечить некого
     *
     * @throws Exception
     */
    public function testStrokeCantBeUsedActionException(): void
    {
        $enemyUnit = UnitFactory::createByTemplate(3);

        $brokenPriest = new BrokenPriestUnit(
            'id',
            'Broken Priest',
            1,
            'avatar',
            20,
            1,
            100,
            100,
            false,
            1,
            RaceFactory::create(1),
            $enemyUnit->getContainer()
        );

        $alliesCommand = CommandFactory::create([$brokenPriest]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $stroke = new Stroke(1, $brokenPriest, $alliesCommand, $enemyCommand, new Container());

        $this->expectException(StrokeException::class);
        $this->expectExceptionMessage(StrokeException::CANT_BE_USED_ACTION);
        $stroke->handle();

        self::assertEquals(1, 1);
    }
}
