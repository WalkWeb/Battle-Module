<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Ability\Resurrection;

use Battle\Action\ResurrectionAction;
use Battle\Command\CommandFactory;
use Battle\Unit\Ability\Resurrection\BackToLifeAbility;
use Exception;
use PHPUnit\Framework\TestCase;
use Tests\Battle\Factory\UnitFactory;

class BackToLifeAbilityTest extends TestCase
{
    /**
     * Создание и применение способности BackToLifeAbility
     *
     * @throws Exception
     */
    public function testBackToLifeAbilityCreateAndApply(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $deadUnit = UnitFactory::createByTemplate(10);
        $enemyUnit = UnitFactory::createByTemplate(2);

        $command = CommandFactory::create([$unit, $deadUnit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = new BackToLifeAbility($unit);

        // Сверяем базовые параметры
        self::assertEquals('Back to Life', $ability->getName());
        self::assertEquals('/images/icons/ability/053.png', $ability->getIcon());
        self::assertFalse($ability->isReady());

        // Увеличиваем ярость у юнита до максимальной
        for ($i = 0; $i < 20; $i++) {
            $unit->newRound();
        }

        $ability->update($unit);

        // Способность перешла в статус готовой для использования
        self::assertTrue($ability->isReady());
        self::assertTrue($ability->canByUsed($enemyCommand, $command));

        $actions = $ability->getAction($enemyCommand, $command);

        // До применения способности юнит мертв
        self::assertEquals(0, $deadUnit->getLife());

        foreach ($actions as $action) {
            self::assertInstanceOf(ResurrectionAction::class, $action);
            self::assertEquals('use Back to Life and resurrected', $action->getNameAction());
            self::assertEquals(30, $action->getPower());
            self::assertTrue($action->canByUsed());
            $action->handle();
        }

        // После применения способности юнит восстановил 30% здоровья (30 здоровья от 100 максимальных)
        self::assertEquals(30, $deadUnit->getLife());

        $ability->usage();

        // Проверяем, что ярость у юнита = 0, а способность вновь не готова к использованию
        self::assertEquals(0, $unit->getRage());
        self::assertFalse($ability->isReady());
    }

    /**
     * Тест ситуации, когда мертвых юнитов нет, и способность BackToLifeAbility не может быть применена
     *
     * @throws Exception
     */
    public function testBackToLifeAbilityCantBeUsed(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $woundedUnit = UnitFactory::createByTemplate(11);
        $enemyUnit = UnitFactory::createByTemplate(2);

        $command = CommandFactory::create([$unit, $woundedUnit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = new BackToLifeAbility($unit);

        self::assertFalse($ability->canByUsed($enemyCommand, $command));
    }
}
