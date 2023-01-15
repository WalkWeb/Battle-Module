<?php

declare(strict_types=1);

namespace Tests\Battle\Action;

use Battle\Action\DamageAction;
use Battle\Action\ActionException;
use Battle\Command\CommandFactory;
use Battle\Command\CommandInterface;
use Battle\Container\Container;
use Battle\Unit\Ability\AbilityInterface;
use Battle\Unit\UnitInterface;
use Exception;
use Tests\AbstractUnitTest;
use Tests\Factory\Mock\CommandMockFactory;
use Tests\Factory\UnitFactory;

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

        self::assertEquals($unit->getOffense(), $action->getOffense());
        self::assertEquals($unit, $action->getActionUnit());
        self::assertEquals($unit, $action->getCreatorUnit());
        self::assertEquals($canBeAvoided, $action->isCanBeAvoided());
        self::assertTrue($action->canByUsed());
        self::assertEquals(DamageAction::UNIT_ANIMATION_METHOD, $action->getAnimationMethod());
        self::assertEquals('damage', $action->getMessageMethod());
        self::assertEquals('attack', $action->getNameAction());
        self::assertFalse($action->isCriticalDamage());
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

        self::assertEquals(20, $action->getFactualPower());
        self::assertEquals(20, $action->getFactualPowerByUnit($enemyUnit));
        self::assertEquals($unit->getId(), $action->getActionUnit()->getId());
        self::assertCount(1, $action->getTargetUnits());

        foreach ($action->getTargetUnits() as $targetUnit) {
            self::assertEquals($enemyUnit->getId(), $targetUnit->getId());
        }

        self::assertEquals(
            $enemyUnit->getTotalLife() - $unit->getOffense()->getDamage($enemyUnit->getDefense()),
            $enemyUnit->getLife()
        );
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
            self::assertEquals(30, $action->getOffense()->getDamage($enemyUnit->getDefense()));
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
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $typeTarget = 10;

        $action = new DamageAction(
            $this->getContainer(),
            $unit,
            $enemyCommand,
            $command,
            $typeTarget,
            $unit->getOffense(),
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
        self::assertEquals($unit->getOffense()->getDamage($enemyUnit->getDefense()), $action->getFactualPower());

        // factualPower, по юниту, по которому урон наносился - тоже
        self::assertEquals($unit->getOffense()->getDamage($enemyUnit->getDefense()), $action->getFactualPowerByUnit($enemyUnit));

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
        self::assertEquals($enemyUnit->getTotalLife() - $unit->getOffense()->getDamage($enemyUnit->getDefense()), $enemyUnit->getLife());
        self::assertEquals($secondaryEnemyUnit->getTotalLife() - $unit->getOffense()->getDamage($enemyUnit->getDefense()), $secondaryEnemyUnit->getLife());
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
     * Тест на ситуацию, когда атака не блокируется
     *
     * @throws Exception
     */
    public function testDamageActionAttackNoBlocked(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = $this->createDamageAction($unit, $enemyCommand, $command, DamageAction::TARGET_RANDOM_ENEMY);

        $action->handle();

        self::assertFalse($action->isBlocked($enemyUnit));
        self::assertEquals($unit->getOffense()->getDamage($enemyUnit->getDefense()), $action->getFactualPowerByUnit($enemyUnit));
    }

    /**
     * Тест на блокирование атаки (цель имеет 100% блок)
     *
     * @throws Exception
     */
    public function testDamageActionAttackBlocked(): void
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
     * Тест на ситуацию, когда заклинание не блокируется (хотя цель имеет обычный блок 100%)
     *
     * @throws Exception
     */
    public function testDamageActionSpellNoBlocked(): void
    {
        $unit = UnitFactory::createByTemplate(5);
        $enemyUnit = UnitFactory::createByTemplate(28);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = $this->createDamageAction($unit, $enemyCommand, $command, DamageAction::TARGET_RANDOM_ENEMY);

        $action->handle();

        self::assertFalse($action->isBlocked($enemyUnit));
        self::assertEquals($unit->getOffense()->getDamage($enemyUnit->getDefense()), $action->getFactualPowerByUnit($enemyUnit));
    }

    /**
     * Тест на ситуацию, когда заклинание блокируется (цель имеет магический блок 100%)
     *
     * @throws Exception
     */
    public function testDamageActionSpellBlocked(): void
    {
        $unit = UnitFactory::createByTemplate(5);
        $enemyUnit = UnitFactory::createByTemplate(38);
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
        self::assertEquals($unit->getOffense()->getDamage($enemyUnit->getDefense()), $action->getFactualPowerByUnit($enemyUnit));
    }

    /**
     * Тест на попадание атаки
     *
     * Юнит с accuracy=100000 бьет по юниту с defense=100
     *
     * @throws Exception
     */
    public function testDamageActionNoDodgedAttack(): void
    {
        $unit = UnitFactory::createByTemplate(30);
        $enemyUnit = UnitFactory::createByTemplate(1);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = $this->createDamageAction($unit, $enemyCommand, $command, DamageAction::TARGET_RANDOM_ENEMY);

        self::assertFalse($action->isDodged($enemyUnit));

        self::assertTrue($action->canByUsed());

        $action->handle();

        self::assertFalse($action->isDodged($enemyUnit));
    }

    /**
     * Тест на уклонение от атаки
     *
     * Юнит с accuracy=200 бьет по юниту с defense=100000
     *
     * @throws Exception
     */
    public function testDamageActionDodgedAttack(): void
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
     * Тест на попадание заклинания
     *
     * Юнит с magic_accuracy=100000 и damage_type=2 бьет по юниту с magic_defense=50
     *
     * @throws Exception
     */
    public function testDamageActionNoDodgedSpell(): void
    {
        $unit = UnitFactory::createByTemplate(37);
        $enemyUnit = UnitFactory::createByTemplate(1);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = $this->createDamageAction($unit, $enemyCommand, $command, DamageAction::TARGET_RANDOM_ENEMY);

        self::assertFalse($action->isDodged($enemyUnit));

        self::assertTrue($action->canByUsed());

        $action->handle();

        self::assertFalse($action->isDodged($enemyUnit));
    }

    /**
     * Тест на уклонение от заклинания
     *
     * Юнит с magic_accuracy=100 и damage_type=2 бьет по юниту с magic_defense=100000
     *
     * @throws Exception
     */
    public function testDamageActionDodgedSpell(): void
    {
        $unit = UnitFactory::createByTemplate(5);
        $enemyUnit = UnitFactory::createByTemplate(37);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = $this->createDamageAction($unit, $enemyCommand, $command, DamageAction::TARGET_RANDOM_ENEMY);

        self::assertFalse($action->isDodged($enemyUnit));

        self::assertTrue($action->canByUsed());

        $action->handle();

        self::assertTrue($action->isDodged($enemyUnit));
    }

    /**
     * Тест на ситуацию, когда юнит с большой защитой (шанс попадания 0%) с эффектом паралича получает урон и не может
     * от него уклониться
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
        self::assertEquals($unit->getOffense()->getDamage($enemyUnit->getDefense()), $action->getFactualPowerByUnit($enemyUnit));
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
        self::assertEquals($unit->getOffense()->getDamage($enemyUnit->getDefense()), $action->getFactualPowerByUnit($enemyUnit));
        self::assertEquals($enemyUnit->getTotalLife() - $unit->getOffense()->getDamage($enemyUnit->getDefense()), $enemyUnit->getLife());
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
            $this->getContainer(),
            $unit,
            $enemyCommand,
            $command,
            DamageAction::TARGET_RANDOM_ENEMY,
            $unit->getOffense(),
            false,
            DamageAction::DEFAULT_NAME,
            DamageAction::UNIT_ANIMATION_METHOD,
            DamageAction::DEFAULT_MESSAGE_METHOD
        );

        $action->handle();

        self::assertTrue(!$action->isBlocked($enemyUnit));
        self::assertEquals($unit->getOffense()->getDamage($enemyUnit->getDefense()), $action->getFactualPowerByUnit($enemyUnit));
        self::assertEquals($enemyUnit->getTotalLife() - $unit->getOffense()->getDamage($enemyUnit->getDefense()), $enemyUnit->getLife());
    }

    /**
     * Тест на ситуацию, когда шанс попадания по юниту выше максимального (UnitInterface::MIN_HIT_CHANCE), и
     * приравнивается к максимальному
     *
     * В текущем варианте кода, единственный вариант проверить, что происходит именно то, что ожидается - это заменить
     * покрытие кода тестами (php vendor/bin/phpunit --coverage-html html) и увидеть, что код ниже покрыт тестами:
     *
     * if ($chanceOfHit > self::MAX_HIT_CHANCE) {
     *   return self::MAX_HIT_CHANCE;
     * }
     *
     * TODO В будущем, когда будет добавлен класс Calculator тест будет переписан, и он станет более очевидным - будет
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
        self::assertIsInt($action->getFactualPower());
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
        self::assertIsInt($action->getFactualPower());
    }

    /**
     * Тест на урон по юниту со 100% ментальным барьером и 100 маны
     *
     * @throws Exception
     */
    public function testDamageActionBy100MentalBarrierAnd100Mana(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(32);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = $this->createDamageAction($unit, $enemyCommand, $command, DamageAction::TARGET_RANDOM_ENEMY);

        $action->handle();

        // Здоровье не изменилось
        self::assertEquals($enemyUnit->getTotalLife(), $enemyUnit->getLife());
        // А вот мана уменьшилась
        self::assertEquals($enemyUnit->getTotalMana() - $unit->getOffense()->getDamage($enemyUnit->getDefense()), $enemyUnit->getMana());
    }

    /**
     * Тест на урон по юниту со 100% ментальным барьером и 0 маны
     *
     * @throws Exception
     */
    public function testDamageActionBy100MentalBarrierAnd0Mana(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(33);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = $this->createDamageAction($unit, $enemyCommand, $command, DamageAction::TARGET_RANDOM_ENEMY);

        $action->handle();

        // Так как маны не было - урон по здоровью прошел, не смотря на 100% ментальный барьер
        self::assertEquals($enemyUnit->getTotalLife() - $unit->getOffense()->getDamage($enemyUnit->getDefense()), $enemyUnit->getLife());
        // Мана как была 0 так и осталась
        self::assertEquals(0, $enemyUnit->getMana());
    }

    /**
     * Тест на урон по юниту со 100% ментальным барьером и 10 маны - в итоге часть урона идет по здоровью, часть по мане
     *
     * @throws Exception
     */
    public function testDamageActionBy100MentalBarrierAnd10Mana(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(34);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = $this->createDamageAction($unit, $enemyCommand, $command, DamageAction::TARGET_RANDOM_ENEMY);

        $action->handle();

        // 10 оставшегося урона пошло по здоровью
        self::assertEquals($enemyUnit->getTotalLife() - 10, $enemyUnit->getLife());
        // 10 урона пошло по мане - осталось 0 маны
        self::assertEquals(0, $enemyUnit->getMana());
    }

    /**
     * Тест на урон по юниту со 50% ментальным барьером и 100 маны - 50% урона идет по здоровью, 50% по мане
     *
     * @throws Exception
     */
    public function testDamageActionBy50MentalBarrierAnd100Mana(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(35);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = $this->createDamageAction($unit, $enemyCommand, $command, DamageAction::TARGET_RANDOM_ENEMY);

        $action->handle();

        // 50% урона идет по здоровью
        self::assertEquals($enemyUnit->getTotalLife() - $unit->getOffense()->getDamage($enemyUnit->getDefense()) / 2, $enemyUnit->getLife());
        // 50% урона идет по мане
        self::assertEquals($enemyUnit->getTotalMana() - $unit->getOffense()->getDamage($enemyUnit->getDefense()) / 2, $enemyUnit->getMana());
    }

    /**
     * И в завершение тестов на ментальный барьер - тест без круглых цифр: по юниту со 80% ментальным барьером и 5 маны
     *
     * @throws Exception
     */
    public function testDamageActionBy80MentalBarrierAnd5Mana(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(36);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = $this->createDamageAction($unit, $enemyCommand, $command, DamageAction::TARGET_RANDOM_ENEMY);

        $action->handle();

        // Общий урон: 20
        // Урон по мане: 16
        // Урон по здоровью: 4
        // Мана после урона становится 0
        self::assertEquals(0, $enemyUnit->getMana());
        // При этом остаток урона (15) идет по здоровью
        self::assertEquals($enemyUnit->getTotalLife() - 15, $enemyUnit->getLife());
    }

    /**
     * Тест на уменьшение получаемого урона от сопротивлений (юнит с 80% сопротивления физическому урону)
     *
     * @throws Exception
     */
    public function testDamageActionPhysicalResist(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(39);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $actions = $unit->getActions($enemyCommand, $command);

        foreach ($actions as $action) {
            self::assertTrue($action->canByUsed());
            $action->handle();
        }

        // Проверяем, что нанесено 20% урона
        self::assertEquals(96, $enemyUnit->getLife());
        self::assertEquals($enemyUnit->getTotalLife() - $unit->getOffense()->getDamage($enemyUnit->getDefense()), $enemyUnit->getLife());
    }

    /**
     * Тест на нанесение критического удара
     *
     * Один юнит с шансом критического удара 50% (будет округлен в 100%, т.к. тестовый режим) - всегда наносит удар
     * Другой юнит с шансом критического удара 49% (будет округлен в 0%, т.к. тестовый режим) - всегда обычный удар
     *
     * @dataProvider criticalDamageDataProvider
     * @param int $unitId
     * @param bool $isCritical
     * @throws Exception
     */
    public function testDamageActionCriticalDamage(int $unitId, bool $isCritical): void
    {
        $unit = UnitFactory::createByTemplate($unitId);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $actions = $unit->getActions($enemyCommand, $command);

        foreach ($actions as $action) {
            self::assertTrue($action->canByUsed());
            self::assertEquals($isCritical, $action->isCriticalDamage());
            $action->handle();
        }

        if ($isCritical) {
            self::assertEquals(
                $enemyUnit->getTotalLife() - $unit->getOffense()->getDamage($enemyUnit->getDefense()) * ($unit->getOffense()->getCriticalMultiplier()/100),
                $enemyUnit->getLife()
            );
        } else {
            self::assertEquals(
                $enemyUnit->getTotalLife() - $unit->getOffense()->getDamage($enemyUnit->getDefense()),
                $enemyUnit->getLife()
            );
        }
    }

    /**
     * Тест на вампиризм от удара
     *
     * @throws Exception
     */
    public function testDamageActionVampirism(): void
    {
        $unit = UnitFactory::createByTemplate(42);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = $this->createDamageAction($unit, $enemyCommand, $command, DamageAction::TARGET_RANDOM_ENEMY);

        // Количество здоровья атакующего до нанесения урона
        self::assertEquals(50, $unit->getLife());

        $action->handle();

        // Количество здоровья после нанесения урона - восстановилось 25 здоровья
        self::assertEquals(75, $unit->getLife());

        // Проверяем количество восстановленного здоровья в DamageAction
        self::assertEquals(25, $action->getRestoreLifeFromVampirism());
    }

    /**
     * Тест на магический вампиризм от удара
     *
     * @throws Exception
     */
    public function testDamageActionMagicVampirism(): void
    {
        $unit = UnitFactory::createByTemplate(2);
        $enemyUnit = UnitFactory::createByTemplate(3);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = $this->createDamageAction($unit, $enemyCommand, $command, DamageAction::TARGET_RANDOM_ENEMY);

        // Количество маны атакующего до нанесения урона
        self::assertEquals(20, $unit->getMana());

        $action->handle();

        // Количество маны после нанесения урона - восстановилось 3 маны (30 урона и 10% магического вампиризма)
        self::assertEquals(23, $unit->getMana());

        // Проверяем количество восстановленной маны в DamageAction
        self::assertEquals(3, $action->getRestoreManaFromMagicVampirism());
    }

    /**
     * @return array
     */
    public function criticalDamageDataProvider(): array
    {
        return [
            // Этот юнит нанесет критический удар
            [
                40,
                true
            ],
            // Этот юнит не нанесет критический удар
            [
                41,
                false
            ],
        ];
    }

    /**
     * @param UnitInterface $unit
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $command
     * @param int $typeTarget
     * @return DamageAction
     * @throws Exception
     */
    private function createDamageAction(
        UnitInterface $unit,
        CommandInterface $enemyCommand,
        CommandInterface $command,
        int $typeTarget
    ): DamageAction
    {
        return new DamageAction(
            $this->getContainer(),
            $unit,
            $enemyCommand,
            $command,
            $typeTarget,
            $unit->getOffense(),
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
        $container = new Container();

        return $container->getAbilityFactory()->create(
            $unit,
            $container->getAbilityDataProvider()->get($abilityName, $abilityLevel)
        );
    }
}
