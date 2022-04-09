<?php

declare(strict_types=1);

namespace Tests\Battle\Action;

use Battle\Action\DamageAction;
use Battle\Action\ActionException;
use Battle\Command\CommandFactory;
use Exception;
use Tests\AbstractUnitTest;
use Tests\Battle\Factory\Mock\CommandMockFactory;
use Tests\Battle\Factory\UnitFactory;

class DamageActionTest extends AbstractUnitTest
{
    /**
     * @throws Exception
     */
    public function testCreateDamageAction(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $defendUnit = UnitFactory::createByTemplate(2);
        $defendCommand = CommandFactory::create([$defendUnit]);
        $alliesCommand = CommandFactory::create([$unit]);
        $canBeAvoided = true;

        $action = new DamageAction(
            $unit,
            $defendCommand,
            $alliesCommand,
            DamageAction::TARGET_RANDOM_ENEMY,
            $unit->getDamage(),
            $canBeAvoided
        );

        self::assertEquals($unit, $action->getActionUnit());
        self::assertEquals($unit, $action->getCreatorUnit());
        self::assertEquals($unit->getDamage(), $action->getPower());
        self::assertEquals($canBeAvoided, $action->isCanBeAvoided());
        self::assertTrue($action->canByUsed());
        self::assertEquals(DamageAction::UNIT_ANIMATION_METHOD, $action->getAnimationMethod());
        self::assertEquals('damage', $action->getMessageMethod());
        self::assertEquals('attack', $action->getNameAction());
    }

    /**
     * @throws Exception
     */
    public function testApplyDamageAction(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $defendUnit = UnitFactory::createByTemplate(2);
        $defendCommand = CommandFactory::create([$defendUnit]);
        $alliesCommand = CommandFactory::create([$unit]);

        $action = new DamageAction(
            $unit,
            $defendCommand,
            $alliesCommand,
            DamageAction::TARGET_RANDOM_ENEMY,
            $unit->getDamage(),
            true
        );

        $action->handle();
        self::assertEquals($unit->getDamage(), $action->getPower());
    }

    /**
     * @throws Exception
     */
    public function testDamageActionApplyUnit(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$enemyUnit]);
        $enemyCommand = CommandFactory::create([$unit]);

        $action = new DamageAction(
            $unit,
            $command,
            $enemyCommand,
            DamageAction::TARGET_RANDOM_ENEMY,
            $unit->getDamage(),
            true
        );

        $action->handle();

        self::assertEquals(20, $action->getPower());
        self::assertEquals(20, $action->getFactualPower());
        self::assertEquals(20, $action->getFactualPowerByUnit($enemyUnit));
        self::assertEquals($unit->getId(), $action->getActionUnit()->getId());
        self::assertCount(1, $action->getTargetUnits());

        foreach ($action->getTargetUnits() as $targetUnit) {
            self::assertEquals($enemyUnit->getId(), $targetUnit->getId());
        }
    }

    /**
     * @throws Exception
     */
    public function testDamageActionFactualDamage(): void
    {
        $attackerUnit = UnitFactory::createByTemplate(2);
        $enemyUnit = UnitFactory::createByTemplate(4);
        $enemyCommand = CommandFactory::create([$enemyUnit]);
        $alliesCommand = CommandFactory::create([$attackerUnit]);

        $actionCollection = $attackerUnit->getAction($enemyCommand, $alliesCommand);

        foreach ($actionCollection as $action) {
            $action->handle();
            self::assertEquals(30, $action->getPower());
            self::assertEquals(20, $action->getFactualPower());
        }
    }

    /**
     * @throws Exception
     */
    public function testDamageActionDeadCommand(): void
    {
        $attackerUnit = UnitFactory::createByTemplate(2);

        // dead unit
        $enemyUnit = UnitFactory::createByTemplate(10);
        $enemyCommand = CommandFactory::create([$enemyUnit]);
        $alliesCommand = CommandFactory::create([$attackerUnit]);

        $actionCollection = $attackerUnit->getAction($enemyCommand, $alliesCommand);

        foreach ($actionCollection as $action) {
            $this->expectException(ActionException::class);
            $this->expectExceptionMessage(ActionException::NO_DEFINED);
            $action->handle();
        }
    }

    /**
     * Тест на исключительную ситуацию со сломанным объектом команды, который на запрос isAlive() вернет true, т.е.
     * команда жива, но на запрос getDefinedUnit(), т.е. на запрос юнита для атаки вернет null (т.е. живых нет)
     *
     * @throws Exception
     */
    public function testDamageActionNoTarget(): void
    {
        $attackerUnit = UnitFactory::createByTemplate(2);
        $alliesCommand = CommandFactory::create([$attackerUnit]);
        $enemyCommand = (new CommandMockFactory())->createAliveAndNoDefinedUnit();

        $actionCollection = $attackerUnit->getAction($enemyCommand, $alliesCommand);

        foreach ($actionCollection as $action) {
            $this->expectException(ActionException::class);
            $this->expectExceptionMessage(ActionException::NO_DEFINED_AGAIN);
            $action->handle();
        }
    }

    /**
     * @throws Exception
     */
    public function testDamageActionUnknownTypeTarget(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $defendUnit = UnitFactory::createByTemplate(2);
        $defendCommand = CommandFactory::create([$defendUnit]);
        $alliesCommand = CommandFactory::create([$unit]);

        $typeTarget = 10;

        $action = new DamageAction(
            $unit,
            $defendCommand,
            $alliesCommand,
            $typeTarget,
            $unit->getDamage(),
            true
        );

        self::assertEquals($typeTarget, $action->getTypeTarget());

        $this->expectException(ActionException::class);
        $this->expectExceptionMessage(ActionException::UNKNOWN_TYPE_TARGET . ': ' . $typeTarget);
        $action->handle();
    }

    /**
     * Тест на ситуацию, когда у DamageAction запрашивается фактический урон по юниту, по которому урон не наносился
     *
     * @throws Exception
     */
    public function testDamageActionNoPowerByUnit(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $defendUnit = UnitFactory::createByTemplate(2);
        $defendCommand = CommandFactory::create([$defendUnit]);
        $alliesCommand = CommandFactory::create([$unit]);

        $action = new DamageAction(
            $unit,
            $defendCommand,
            $alliesCommand,
            DamageAction::TARGET_RANDOM_ENEMY,
            $unit->getDamage(),
            true
        );

        $action->handle();

        // Общий factualPower получаем нормально
        self::assertEquals($unit->getDamage(), $action->getFactualPower());

        // factualPower, по юниту, по которому урон наносился - тоже
        self::assertEquals($unit->getDamage(), $action->getFactualPowerByUnit($defendUnit));

        // А вот factualPower по юниту, по которому урон не наносился - отсутствует
        $this->expectException(ActionException::class);
        $this->expectExceptionMessage(ActionException::NO_POWER_BY_UNIT);
        $action->getFactualPowerByUnit($unit);
    }

    /**
     * Тест на нанесение урона сразу всей вражеской команде
     *
     * @throws Exception
     */
    public function testDamageActionTargetAllAliveEnemy(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $firstEnemyUnit = UnitFactory::createByTemplate(2);
        $secondaryEnemyUnit = UnitFactory::createByTemplate(3);

        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$firstEnemyUnit, $secondaryEnemyUnit]);

        $action = new DamageAction(
            $unit,
            $enemyCommand,
            $command,
            DamageAction::TARGET_ALL_ENEMY,
            $unit->getDamage(),
            true
        );

        $action->handle();

        // Проверяем, что урон нанесен по обоим юнитам
        self::assertEquals($firstEnemyUnit->getTotalLife() - $unit->getDamage(), $firstEnemyUnit->getLife());
        self::assertEquals($secondaryEnemyUnit->getTotalLife() - $unit->getDamage(), $secondaryEnemyUnit->getLife());
    }

    /**
     * Тест на ручное указывание, что событие (урон) было заблокировано
     *
     * @throws Exception
     */
    public function testDamageActionManualBlocked(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = new DamageAction(
            $unit,
            $enemyCommand,
            $command,
            DamageAction::TARGET_RANDOM_ENEMY,
            $unit->getDamage(),
            true
        );

        // По-умолчанию isBlocked возвращает false
        self::assertFalse($action->isBlocked($enemyUnit));

        // Указываем, что урон был заблокирован
        $action->blocked($enemyUnit);

        // И получаем true
        self::assertTrue($action->isBlocked($enemyUnit));
    }

    /**
     * Тест на блокирование урона
     *
     * @throws Exception
     */
    public function testDamageActionBlocked(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(28);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = new DamageAction(
            $unit,
            $enemyCommand,
            $command,
            DamageAction::TARGET_RANDOM_ENEMY,
            $unit->getDamage(),
            true
        );

        $action->handle();

        self::assertTrue($action->isBlocked($enemyUnit));
        self::assertEquals(0, $action->getFactualPowerByUnit($enemyUnit));
    }

    /**
     * TODO Когда будет реализована механика уклонения dodge в Unit нужно будет переписать тест по аналогии с тестом выше
     *
     * @throws Exception
     */
    public function testDamageActionDodged(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(28);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = new DamageAction(
            $unit,
            $enemyCommand,
            $command,
            DamageAction::TARGET_RANDOM_ENEMY,
            $unit->getDamage(),
            true
        );

        self::assertFalse($action->isDodged($enemyUnit));

        $action->dodged($enemyUnit);

        self::assertTrue($action->isDodged($enemyUnit));
    }

    /**
     * Тест на ситуацию, когда юнит со 100 игнорированием блока атакует цель со 100% блоком - урон проходит
     *
     * @throws Exception
     */
    public function testDamageActionIgnoreBlock(): void
    {
        $unit = UnitFactory::createByTemplate(29);
        $enemyUnit = UnitFactory::createByTemplate(28);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = new DamageAction(
            $unit,
            $enemyCommand,
            $command,
            DamageAction::TARGET_RANDOM_ENEMY,
            $unit->getDamage(),
            true
        );

        $action->handle();

        self::assertTrue(!$action->isBlocked($enemyUnit));
        self::assertEquals($unit->getDamage(), $action->getFactualPowerByUnit($enemyUnit));
        self::assertEquals($enemyUnit->getTotalLife() - $unit->getDamage(), $enemyUnit->getLife());
    }

    /**
     * Тест на ситуацию, когда юнит со 100% блока получает урон от DamageAction с canBeAvoided=false - урон проходит
     *
     * @throws Exception
     */
    public function testDamageActionCantBeAvoid(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(28);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = new DamageAction(
            $unit,
            $enemyCommand,
            $command,
            DamageAction::TARGET_RANDOM_ENEMY,
            $unit->getDamage(),
            false
        );

        $action->handle();

        self::assertTrue(!$action->isBlocked($enemyUnit));
        self::assertEquals($unit->getDamage(), $action->getFactualPowerByUnit($enemyUnit));
        self::assertEquals($enemyUnit->getTotalLife() - $unit->getDamage(), $enemyUnit->getLife());
    }

    /**
     * Тест на ситуацию, когда шанс попадания по юниту выше максимального (UnitInterface::MIN_HIT_CHANCE), и
     * приравнивается к максимальному
     *
     * В текущем варианте кода, единственный вариант проверить, что происходит именно то, что ожидается - это заменить
     * покрытие кода тестами (php vendor/bin/phpunit --coverage-html html) и увидеть, что код
     *
     * if ($chanceOfHit > self::MAX_HIT_CHANCE) {
     *   return self::MAX_HIT_CHANCE;
     * }
     *
     * тестами покрыт
     *
     * TODO В будущем, когда будет добавлен Calculator тест будет переписан, и он станет более очевидным - будет
     * TODO простой публичный метод, который будет возвращать шанс попадания, на основании меткости/защиты сражающихся
     *
     * @throws Exception
     */
    public function testDamageActionMaxChanceOfHit(): void
    {
        $unit = UnitFactory::createByTemplate(30);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = new DamageAction(
            $unit,
            $enemyCommand,
            $command,
            DamageAction::TARGET_RANDOM_ENEMY,
            $unit->getDamage(),
            true
        );

        $action->handle();

        // Максимальный шанс попадания 95%, т.е. все равно может промахнуться. Соответственно мы не можем проверить
        // конкретное попадание или конкретное уклонение, по этому делается простая условная проверка
        self::assertIsInt($action->getPower());
    }

    /**
     * TODO Аналогично testDamageActionMaxChanceOfHit() только для минимального шанса попадания
     *
     * @throws Exception
     */
    public function testDamageActionMinChanceOfHit(): void
    {
        $unit = UnitFactory::createByTemplate(2);
        $enemyUnit = UnitFactory::createByTemplate(30);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = new DamageAction(
            $unit,
            $enemyCommand,
            $command,
            DamageAction::TARGET_RANDOM_ENEMY,
            $unit->getDamage(),
            true
        );

        $action->handle();

        // Минимальный шанс попадания 5%, т.е. все равно может попасть. Соответственно мы не можем проверить
        // конкретное попадание или конкретное уклонение, по этому делается простая условная проверка
        self::assertIsInt($action->getPower());
    }
}
