<?php

declare(strict_types=1);

namespace Tests\Battle\Action;

use Battle\Action\ActionException;
use Battle\Action\ActionInterface;
use Battle\Action\ResurrectionAction;
use Battle\Command\CommandFactory;
use Exception;
use Tests\AbstractUnitTest;
use Tests\Battle\Factory\BaseFactory;
use Tests\Battle\Factory\UnitFactory;

class ResurrectionActionTest extends AbstractUnitTest
{
    /**
     * Тест на создание ResurrectionAction
     *
     * @throws Exception
     */
    public function testResurrectionActionCreate(): void
    {
        [$unit, $command, $enemyCommand] = BaseFactory::create(10, 2);

        $name = 'Resurrection';
        $power = 50;
        $typeTarget = ResurrectionAction::TARGET_DEAD_ALLIES;

        $action = new ResurrectionAction($unit, $enemyCommand, $command, $typeTarget, $power, $name);

        self::assertEquals($name, $action->getNameAction());
        self::assertEquals($power, $action->getPower());
        self::assertEquals($typeTarget, $action->getTypeTarget());
        self::assertEquals('applyResurrectionAction', $action->getHandleMethod());
        self::assertEquals('resurrected', $action->getAnimationMethod());
        self::assertEquals('resurrected', $action->getMessageMethod());
        self::assertTrue($action->canByUsed());

        $factualPower = 123;

        $action->addFactualPower($unit, $factualPower);

        self::assertEquals($factualPower, $action->getFactualPower());
        self::assertEquals($factualPower, $action->getFactualPowerByUnit($unit));
    }

    /**
     * Тест на применение ResurrectionAction
     *
     * @throws Exception
     */
    public function testResurrectionActionApply(): void
    {
        [$unit, $command, $enemyCommand] = BaseFactory::create(10, 2);

        $name = 'Resurrection';
        $power = 50;
        $typeTarget = ResurrectionAction::TARGET_DEAD_ALLIES;

        $action = new ResurrectionAction($unit, $enemyCommand, $command, $typeTarget, $power, $name);

        self::assertFalse($unit->isAlive());
        self::assertEquals(0, $unit->getLife());

        self::assertTrue($action->canByUsed());
        $action->handle();

        self::assertTrue($unit->isAlive());
        self::assertEquals(50, $unit->getLife());
    }

    /**
     * Тест на ситуацию, когда у ResurrectionAction запрашивается фактическое лечение по юниту, по которому воскрешения
     * (и соответственно лечения) не было
     *
     * @throws Exception
     */
    public function testResurrectionActionNoPowerByUnit(): void
    {
        [$unit, $command, $enemyCommand, $enemyUnit] = BaseFactory::create(10, 2);

        $name = 'Resurrection';
        $power = 50;
        $typeTarget = ResurrectionAction::TARGET_DEAD_ALLIES;

        $action = new ResurrectionAction($unit, $enemyCommand, $command, $typeTarget, $power, $name);

        self::assertTrue($action->canByUsed());
        $action->handle();

        // Общий factualPower получаем нормально
        self::assertEquals($power, $action->getFactualPower());

        // factualPower, по юниту, по которому урон наносился - тоже
        self::assertEquals($power, $action->getFactualPowerByUnit($unit));

        // А вот factualPower по юниту, по которому урон не наносился - отсутствует
        $this->expectException(ActionException::class);
        $this->expectExceptionMessage(ActionException::NO_POWER_BY_UNIT);
        $action->getFactualPowerByUnit($enemyUnit);
    }

    /**
     * Тест на проверку следующей ситуации:
     *
     * - Есть юнит с небольшим количеством максимального здоровья, например 10 здоровья
     * - Есть ResurrectionAction с power = 1
     *
     * Необходимо проверить, что хотя бы 1 здоровье будет восстановлено - т.е. юнит перейдет в разряд живых
     *
     * Обычное же получение 1% от 10 здоровья даст 0 после округления
     *
     * @throws Exception
     */
    public function testResurrectionLowLife(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $deadUnit = UnitFactory::createByTemplate(24);
        $enemyUnit = UnitFactory::createByTemplate(2);

        $command = CommandFactory::create([$unit, $deadUnit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $power = 1;
        $typeTarget = ResurrectionAction::TARGET_DEAD_ALLIES;

        $action = new ResurrectionAction($unit, $enemyCommand, $command, $typeTarget, $power);

        // Вначале убеждаемся, что юнит мертв
        self::assertEquals(0, $deadUnit->getLife());

        // Применяем воскрешение
        self::assertTrue($action->canByUsed());
        $action->handle();

        // Проверяем, что здоровье стало = 1
        self::assertEquals(1, $deadUnit->getLife());
    }

    /**
     * Тест на различные варианты некорректной силы ResurrectionAction - меньше 1 или больше 100
     *
     * @dataProvider invalidPowerDataProvider
     * @param int $power
     * @throws ActionException
     * @throws Exception
     */
    public function testResurrectionActionInvalidPower(int $power): void
    {
        [$unit, $command, $enemyCommand] = BaseFactory::create(10, 2);

        $this->expectException(ActionException::class);
        $this->expectExceptionMessage('ResurrectionAction: invalid power: min: 1, max: 100');
        new ResurrectionAction($unit, $enemyCommand, $command, ResurrectionAction::TARGET_DEAD_ALLIES, $power);
    }

    /**
     * Тест на различные варианты некорректного типа выбора цели для воскрешения. Доступен только TARGET_DEAD_ALLIES
     *
     * @dataProvider invalidTypeTargetDataProvider
     * @param int $typeTarget
     * @throws ActionException
     * @throws Exception
     */
    public function testResurrectionActionInvalidTypeTarget(int $typeTarget): void
    {
        [$unit, $command, $enemyCommand] = BaseFactory::create(10, 2);

        $this->expectException(ActionException::class);
        $this->expectExceptionMessage(ActionException::INVALID_RESURRECTED_TARGET);
        new ResurrectionAction($unit, $enemyCommand, $command, $typeTarget, 50);
    }

    /**
     * @return array
     */
    public function invalidPowerDataProvider(): array
    {
        return [
            [
                -10
            ],
            [
                0
            ],
            [
                101
            ],
            [
                1000
            ],
        ];
    }

    /**
     * @return array
     */
    public function invalidTypeTargetDataProvider(): array
    {
        return [
            [
                ActionInterface::TARGET_SELF,
            ],
            [
                ActionInterface::TARGET_RANDOM_ENEMY,
            ],
            [
                ActionInterface::TARGET_WOUNDED_ALLIES,
            ],
            [
                ActionInterface::TARGET_EFFECT_ENEMY,
            ],
            [
                ActionInterface::TARGET_EFFECT_ALLIES,
            ],
            [
                ActionInterface::TARGET_WOUNDED_ALLIES_EFFECT,
            ],
        ];
    }
}
