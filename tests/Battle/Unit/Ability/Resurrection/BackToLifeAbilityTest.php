<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Ability\Resurrection;

use Battle\Action\ResurrectionAction;
use Battle\Command\CommandFactory;
use Battle\Container\Container;
use Battle\Container\ContainerException;
use Battle\Container\ContainerInterface;
use Battle\Translation\Translation;
use Battle\Unit\Ability\Resurrection\BackToLifeAbility;
use Exception;
use PHPUnit\Framework\TestCase;
use Tests\Battle\Factory\UnitFactory;

class BackToLifeAbilityTest extends TestCase
{
    private const MESSAGE_EN = '<span style="color: #1e72e3">unit_1</span> use <img src="/images/icons/ability/053.png" alt="" /> Back to Life and resurrected <span style="color: #1e72e3">dead_unit</span>';
    private const MESSAGE_RU = '<span style="color: #1e72e3">unit_1</span> использовал <img src="/images/icons/ability/053.png" alt="" /> Возвращение к жизни и воскресил <span style="color: #1e72e3">dead_unit</span>';

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
            self::assertEquals('Back to Life', $action->getNameAction());
            self::assertEquals(30, $action->getPower());
            self::assertTrue($action->canByUsed());
            self::assertEquals(self::MESSAGE_EN, $action->handle());
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

    /**
     * Тест на формирование сообщение для чата на русском
     *
     * @throws Exception
     */
    public function testBackToLifeAbilityMessageRu(): void
    {
        $container = $this->getContainerWithRuLanguage();

        $unit = UnitFactory::createByTemplate(1, $container);
        $deadUnit = UnitFactory::createByTemplate(10, $container);
        $enemyUnit = UnitFactory::createByTemplate(2, $container);

        $command = CommandFactory::create([$unit, $deadUnit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = new BackToLifeAbility($unit);

        // Увеличиваем ярость у юнита до максимальной
        for ($i = 0; $i < 20; $i++) {
            $unit->newRound();
        }

        $ability->update($unit);

        $actions = $ability->getAction($enemyCommand, $command);

        foreach ($actions as $action) {
            self::assertTrue($action->canByUsed());
            self::assertEquals(self::MESSAGE_RU, $action->handle());
        }
    }

    /**
     * TODO Дублируется в нескольких тестах, можно вынести в TestCase
     *
     * @return ContainerInterface
     * @throws ContainerException
     */
    private function getContainerWithRuLanguage(): ContainerInterface
    {
        $translation = new Translation('ru');
        $container = new Container();
        $container->set(Translation::class, $translation);
        return $container;
    }
}
