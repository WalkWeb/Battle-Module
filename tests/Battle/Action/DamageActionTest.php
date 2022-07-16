<?php

declare(strict_types=1);

namespace Tests\Battle\Action;

use Battle\Action\DamageAction;
use Battle\Action\ActionException;
use Battle\Command\CommandFactory;
use Battle\Command\CommandInterface;
use Battle\Unit\Ability\AbilityFactory;
use Battle\Unit\Ability\AbilityInterface;
use Battle\Unit\Ability\DataProvider\AbilityDataProviderInterface;
use Battle\Unit\Ability\DataProvider\ExampleAbilityDataProvider;
use Battle\Unit\UnitInterface;
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
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);
        $canBeAvoided = true;

        $action = $this->createDamageAction($unit, $enemyCommand, $command, DamageAction::TARGET_RANDOM_ENEMY);

        self::assertEquals($unit, $action->getActionUnit());
        self::assertEquals($unit, $action->getCreatorUnit());
        self::assertEquals($unit->getOffense()->getDamage(), $action->getPower());
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
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = $this->createDamageAction($unit, $enemyCommand, $command, DamageAction::TARGET_RANDOM_ENEMY);

        $action->handle();
        self::assertEquals($unit->getOffense()->getDamage(), $action->getPower());
    }

    /**
     * @throws Exception
     */
    public function testDamageActionApplyUnit(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = $this->createDamageAction($unit, $enemyCommand, $command, DamageAction::TARGET_RANDOM_ENEMY);

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

        $actionCollection = $attackerUnit->getActions($enemyCommand, $alliesCommand);

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

        $actionCollection = $attackerUnit->getActions($enemyCommand, $alliesCommand);

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

        $actionCollection = $attackerUnit->getActions($enemyCommand, $alliesCommand);

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
            $unit->getOffense()->getDamage(),
            true,
            DamageAction::DEFAULT_NAME,
            DamageAction::UNIT_ANIMATION_METHOD,
            DamageAction::DEFAULT_MESSAGE_METHOD
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
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = $this->createDamageAction($unit, $enemyCommand, $command, DamageAction::TARGET_RANDOM_ENEMY);

        $action->handle();

        // Общий factualPower получаем нормально
        self::assertEquals($unit->getOffense()->getDamage(), $action->getFactualPower());

        // factualPower, по юниту, по которому урон наносился - тоже
        self::assertEquals($unit->getOffense()->getDamage(), $action->getFactualPowerByUnit($enemyUnit));

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
        $enemyUnit = UnitFactory::createByTemplate(2);
        $secondaryEnemyUnit = UnitFactory::createByTemplate(3);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit, $secondaryEnemyUnit]);

        $action = $this->createDamageAction($unit, $enemyCommand, $command, DamageAction::TARGET_ALL_ENEMY);

        $action->handle();

        // Проверяем, что урон нанесен по обоим юнитам
        self::assertEquals($enemyUnit->getTotalLife() - $unit->getOffense()->getDamage(), $enemyUnit->getLife());
        self::assertEquals($secondaryEnemyUnit->getTotalLife() - $unit->getOffense()->getDamage(), $secondaryEnemyUnit->getLife());
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

        $action = $this->createDamageAction($unit, $enemyCommand, $command, DamageAction::TARGET_RANDOM_ENEMY);

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

        $action = $this->createDamageAction($unit, $enemyCommand, $command, DamageAction::TARGET_RANDOM_ENEMY);

        $action->handle();

        self::assertTrue($action->isBlocked($enemyUnit));
        self::assertEquals(0, $action->getFactualPowerByUnit($enemyUnit));
    }

    /**
     * Тест на ситуацию, когда юнит со 100% блоком с эффектом паралича получает урон и не может его заблокировать
     *
     * @throws Exception
     */
    public function testDamageActionParalysisNoBlocked(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(28); // Юнит со 100% блоком
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        // Накладываем паралич на $enemyUnit
        $ability = $this->createAbilityByDataProvider($unit, 'Paralysis');

        foreach ($ability->getActions($enemyCommand, $command) as $action) {
            self::assertTrue($action->canByUsed());
            $action->handle();
        }

        // Наносим удар $enemyUnit
        $action = $this->createDamageAction($unit, $enemyCommand, $command, DamageAction::TARGET_RANDOM_ENEMY);
        $action->handle();

        // Удар не заблокирован
        self::assertFalse($action->isBlocked($enemyUnit));
        self::assertEquals($unit->getOffense()->getDamage(), $action->getFactualPowerByUnit($enemyUnit));
    }

    /**
     * @throws Exception
     */
    public function testDamageActionDodged(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(30);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = $this->createDamageAction($unit, $enemyCommand, $command, DamageAction::TARGET_RANDOM_ENEMY);

        self::assertFalse($action->isDodged($enemyUnit));

        self::assertTrue($action->canByUsed());

        $action->handle();

        self::assertTrue($action->isDodged($enemyUnit));
    }

    /**
     * Тест на ситуацию, когда юнит большой защитой (шанс попадания 0%) с эффектом паралича получает урон и не может от
     * него уклониться
     *
     * @throws Exception
     */
    public function testDamageActionParalysisNoDodged(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(30);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        // Накладываем паралич на $enemyUnit
        $ability = $this->createAbilityByDataProvider($unit, 'Paralysis');

        foreach ($ability->getActions($enemyCommand, $command) as $action) {
            self::assertTrue($action->canByUsed());
            $action->handle();
        }

        // Наносим удар $enemyUnit
        $action = $this->createDamageAction($unit, $enemyCommand, $command, DamageAction::TARGET_RANDOM_ENEMY);
        $action->handle();

        // Уклонение не сработало
        self::assertFalse($action->isDodged($enemyUnit));
        self::assertEquals($unit->getOffense()->getDamage(), $action->getFactualPowerByUnit($enemyUnit));
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

        $action = $this->createDamageAction($unit, $enemyCommand, $command, DamageAction::TARGET_RANDOM_ENEMY);

        $action->handle();

        self::assertTrue(!$action->isBlocked($enemyUnit));
        self::assertEquals($unit->getOffense()->getDamage(), $action->getFactualPowerByUnit($enemyUnit));
        self::assertEquals($enemyUnit->getTotalLife() - $unit->getOffense()->getDamage(), $enemyUnit->getLife());
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
            $unit->getOffense()->getDamage(),
            false,
            DamageAction::DEFAULT_NAME,
            DamageAction::UNIT_ANIMATION_METHOD,
            DamageAction::DEFAULT_MESSAGE_METHOD
        );

        $action->handle();

        self::assertTrue(!$action->isBlocked($enemyUnit));
        self::assertEquals($unit->getOffense()->getDamage(), $action->getFactualPowerByUnit($enemyUnit));
        self::assertEquals($enemyUnit->getTotalLife() - $unit->getOffense()->getDamage(), $enemyUnit->getLife());
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

        $action = $this->createDamageAction($unit, $enemyCommand, $command, DamageAction::TARGET_RANDOM_ENEMY);

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

        $action = $this->createDamageAction($unit, $enemyCommand, $command, DamageAction::TARGET_RANDOM_ENEMY);

        $action->handle();

        // Минимальный шанс попадания 5%, т.е. все равно может попасть. Соответственно мы не можем проверить
        // конкретное попадание или конкретное уклонение, по этому делается простая условная проверка
        self::assertIsInt($action->getPower());
    }

    /**
     * @param UnitInterface $unit
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $command
     * @param int $typeTarget
     * @return DamageAction
     */
    private function createDamageAction(
        UnitInterface $unit,
        CommandInterface $enemyCommand,
        CommandInterface $command,
        int $typeTarget
    ): DamageAction
    {
        return new DamageAction(
            $unit,
            $enemyCommand,
            $command,
            $typeTarget,
            $unit->getOffense()->getDamage(),
            true,
            DamageAction::DEFAULT_NAME,
            DamageAction::UNIT_ANIMATION_METHOD,
            DamageAction::DEFAULT_MESSAGE_METHOD
        );
    }

    /**
     * @param UnitInterface $unit
     * @param string $abilityName
     * @param int $abilityLevel
     * @return AbilityInterface
     * @throws Exception
     */
    private function createAbilityByDataProvider(UnitInterface $unit, string $abilityName, int $abilityLevel = 1): AbilityInterface
    {
        return $this->getFactory()->create(
            $unit,
            $this->getAbilityDataProvider()->get($abilityName, $abilityLevel)
        );
    }

    /**
     * @return AbilityFactory
     */
    private function getFactory(): AbilityFactory
    {
        return new AbilityFactory();
    }

    /**
     * @return AbilityDataProviderInterface
     */
    private function getAbilityDataProvider(): AbilityDataProviderInterface
    {
        return new ExampleAbilityDataProvider();
    }
}
