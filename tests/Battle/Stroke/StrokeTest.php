<?php

declare(strict_types=1);

namespace Tests\Battle\Stroke;

use Battle\Result\Chat\Chat;
use Battle\Result\FullLog\FullLog;
use Battle\Command\CommandFactory;
use Battle\Result\Scenario\Scenario;
use Battle\Statistic\Statistic;
use Exception;
use PHPUnit\Framework\TestCase;
use Battle\Stroke\Stroke;
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

        $chat = new Chat();

        $stroke = new Stroke(1, $leftUnit, $leftCommand, $rightCommand, new Statistic(), new FullLog(), $chat, new Scenario());
        $stroke->handle();

        self::assertEquals($rightUnit->getTotalLife() - $leftUnit->getDamage(), $rightUnit->getLife());

        self::assertTrue($leftUnit->isAction());
        self::assertFalse($rightUnit->isAction());

        $chatResultMessages = [
            self::MESSAGE,
        ];

        self::assertEquals($chatResultMessages, $chat->getMessages());
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

        $stroke = new Stroke(1, $leftUnit, $leftCommand, $rightCommand, new Statistic(), new FullLog(), new Chat(), new Scenario());

        // Для теста достаточно того, что выполнение хода завершилось без ошибок
        $stroke->handle();

        // Но, на всякий случай проверяем, что противник умер
        self::assertEquals(0, $rightUnit->getLife());
    }

    /**
     * @throws Exception
     */
    public function testStrokeActionNoHandle(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $alliesUnit = UnitFactory::createByTemplate(5);
        $enemyUnit = UnitFactory::createByTemplate(3);
        $alliesCommand = CommandFactory::create([$unit, $alliesUnit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        // Накапливаем концентрацию
        for ($i = 0; $i < 10; $i++) {
            $alliesUnit->newRound();
        }

        $stroke = new Stroke(1, $alliesUnit, $alliesCommand, $enemyCommand, new Statistic(), new FullLog(), new Chat(), new Scenario());

        $stroke->handle();

        // Не смотря на то, что должно было примениться лечение - будет использована атака, т.к. лечить некого
        // Проверяем уменьшившееся здоровье
        self::assertEquals($enemyUnit->getTotalLife() - $alliesUnit->getDamage(), $enemyUnit->getLife());
    }
}
