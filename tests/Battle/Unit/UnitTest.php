<?php

declare(strict_types=1);

namespace Tests\Battle\Unit;

use Battle\Action\ActionCollection;
use Battle\Command\CommandInterface;
use Battle\Container\Container;
use Battle\Unit\Ability\AbilityCollection;
use Battle\Unit\Defense\Defense;
use Battle\Unit\Defense\DefenseInterface;
use Battle\Unit\Offense\OffenseInterface;
use Exception;
use Battle\Unit\Unit;
use Battle\Command\Command;
use Battle\Unit\UnitCollection;
use Battle\Unit\UnitException;
use Battle\Unit\UnitInterface;
use Battle\Command\CommandFactory;
use Tests\AbstractUnitTest;
use Tests\Factory\UnitFactory;
use Battle\Action\DamageAction;
use Tests\Factory\Mock\ActionMockFactory;
use Tests\Factory\CommandFactory as CommandFactoryTest;
use Tests\Factory\UnitFactoryException;

class UnitTest extends AbstractUnitTest
{
    /**
     * @dataProvider createDataProvider
     * @param int $template
     * @throws Exception
     */
    public function testUniCreate(int $template): void
    {
        $unit = UnitFactory::createByTemplate($template);
        $data = UnitFactory::getData($template);

        self::assertEquals($data['id'], $unit->getId());
        self::assertEquals($data['name'], $unit->getName());
        self::assertEquals($data['level'], $unit->getLevel());
        self::assertEquals($data['avatar'], $unit->getAvatar());
        self::assertEquals($data['life'], $unit->getLife());
        self::assertEquals($data['total_life'], $unit->getTotalLife());
        self::assertEquals($data['mana'], $unit->getMana());
        self::assertEquals($data['total_mana'], $unit->getTotalMana());
        self::assertFalse($unit->isAction());
        self::assertEquals($data['life'] > 0, $unit->isAlive());
        self::assertEquals($data['melee'], $unit->isMelee());
        self::assertEquals($data['race'], $unit->getRace()->getId());
        self::assertFalse($unit->isParalysis());
        self::assertEquals((int)(Unit::BASE_CUNNING * ((100 + $data['cunning_multiplier']) / 100)), $unit->getCunning());
        self::assertEquals($data['add_concentration_multiplier'], $unit->getAddConcentrationMultiplier());
        self::assertEquals($data['cunning_multiplier'], $unit->getCunningMultiplier());
        self::assertEquals($data['add_rage_multiplier'], $unit->getAddRageMultiplier());

        // Проверка метода getDamage подразумевает, что все юниты имеют 0 сопротивления
        self::assertEquals(
            ($data['offense']['physical_damage'] +
                $data['offense']['fire_damage'] +
                $data['offense']['water_damage'] +
                $data['offense']['air_damage'] +
                $data['offense']['earth_damage'] +
                $data['offense']['life_damage'] +
                $data['offense']['death_damage']) * ($data['offense']['damage_multiplier'] / 100),
            $unit->getOffense()->getDamage($this->getDefense())
        );

        // Здесь проверяется лишь несколько параметров Offense, все проверяются в OffenseFactoryTest
        self::assertEquals($data['offense']['physical_damage'], $unit->getOffense()->getPhysicalDamage());
        self::assertEquals(round($data['offense']['attack_speed'], 2), $unit->getOffense()->getAttackSpeed());
        self::assertEquals(round($data['offense']['cast_speed'], 2), $unit->getOffense()->getCastSpeed());
        self::assertEquals($data['offense']['accuracy'], $unit->getOffense()->getAccuracy());
        self::assertEquals($data['offense']['block_ignoring'], $unit->getOffense()->getBlockIgnoring());

        $speed = $data['offense']['damage_type'] === OffenseInterface::TYPE_ATTACK ?
            $data['offense']['attack_speed'] : $data['offense']['cast_speed'];

        self::assertEquals(
            round(
                (
                    $data['offense']['physical_damage'] +
                    $data['offense']['fire_damage'] +
                    $data['offense']['water_damage'] +
                    $data['offense']['air_damage'] +
                    $data['offense']['earth_damage'] +
                    $data['offense']['life_damage'] +
                    $data['offense']['death_damage']
                )
                * $speed
                * ($data['offense']['damage_multiplier'] / 100)
                * (1 + ($data['offense']['critical_chance'] / 100) * ($data['offense']['critical_multiplier'] / 100 - 1)),
                1
            ),
            $unit->getOffense()->getDPS()
        );

        // Здесь проверяется лишь несколько параметров Defense, все проверяются в DefenseFactoryTest
        self::assertEquals($data['defense']['defense'], $unit->getDefense()->getDefense());
        self::assertEquals($data['defense']['magic_defense'], $unit->getDefense()->getMagicDefense());
        self::assertEquals($data['defense']['block'], $unit->getDefense()->getBlock());
        self::assertEquals($data['defense']['mental_barrier'], $unit->getDefense()->getMentalBarrier());

        $expectedAbilities = new AbilityCollection(true);

        if ($data['class']) {
            $classData = $this->container->getClassDataProvider()->get($data['class']);
            foreach ($this->container->getUnitClassFactory()->create($classData)->getAbilities($unit) as $ability) {
                $expectedAbilities->add($ability);
            }
        }

        $classData = $this->container->getRaceDataProvider()->get($data['race']);
        foreach ($this->container->getRaceFactory()->create($classData)->getAbilities($unit) as $ability) {
            $expectedAbilities->add($ability);
        }

        self::assertEquals($expectedAbilities, $unit->getAbilities());
        self::assertEquals(new UnitCollection(), $unit->getLastTargets());
    }

    /**
     * @throws Exception
     */
    public function testUnitApplyDamage(): void
    {
        $attackUnitTemplate = 2;
        $defendUnitTemplate = 6;
        $unit = UnitFactory::createByTemplate($attackUnitTemplate);
        $enemyUnit = UnitFactory::createByTemplate($defendUnitTemplate);

        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = new DamageAction(
           $this->container,
            $unit,
            $enemyCommand,
            $command,
            DamageAction::TARGET_RANDOM_ENEMY,
            true,
            DamageAction::DEFAULT_NAME,
            DamageAction::UNIT_ANIMATION_METHOD,
            DamageAction::DEFAULT_MESSAGE_METHOD,
            $unit->getOffense()
        );

        $action->handle();

        $defendLife = UnitFactory::getData($defendUnitTemplate)['life'];

        self::assertEquals($defendLife - $unit->getOffense()->getDamage($enemyUnit->getDefense()), $enemyUnit->getLife());

        $action2 = new DamageAction(
            $this->container,
            $unit,
            $enemyCommand,
            $command,
            DamageAction::TARGET_RANDOM_ENEMY,
            true,
            DamageAction::DEFAULT_NAME,
            DamageAction::UNIT_ANIMATION_METHOD,
            DamageAction::DEFAULT_MESSAGE_METHOD,
            $unit->getOffense()
        );

        $action2->handle();

        self::assertEquals(0, $enemyUnit->getLife());
        self::assertFalse($enemyUnit->isAlive());
    }

    /**
     * @throws Exception
     */
    public function testUnitUnknownAction(): void
    {
        $factory = new ActionMockFactory();
        $action = $factory->createDamageActionMock('unknownMethod');
        $unit = UnitFactory::createByTemplate(1);

        $this->expectException(UnitException::class);
        $this->expectExceptionMessage(UnitException::UNDEFINED_ACTION_METHOD);
        $unit->applyAction($action);
    }

    /**
     * Проверяем корректное обновление параметра action у юнита, при начале нового раунда
     *
     * @throws Exception
     */
    public function testUnitChangeAction(): void
    {
        $unit = UnitFactory::createByTemplate(1);

        $unit->madeAction();
        self::assertTrue($unit->isAction());
        $unit->newRound();
        self::assertFalse($unit->isAction());
    }

    /**
     * Проверяем корректное добавление концентрации и ярости юниту, при начале нового раунда
     *
     * @throws Exception
     */
    public function testUnitNewRoundAddConcentrationAndRage(): void
    {
        $unit = UnitFactory::createByTemplate(1);

        self::assertEquals(0, $unit->getConcentration());
        self::assertEquals(0, $unit->getRage());
        $unit->newRound();
        self::assertEquals(Unit::ADD_CON_NEW_ROUND, $unit->getConcentration());
        self::assertEquals(Unit::ADD_RAGE_NEW_ROUND, $unit->getRage());
    }

    /**
     * Тест на получение концентрации и ярости при совершении действия, и получения действия от другого юнита
     *
     * @throws Exception
     */
    public function testUnitAddConcentrationAndRage(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $collection = new UnitCollection();
        $collection->add($unit);
        $command = new Command($collection);

        $enemyUnit = UnitFactory::createByTemplate(2);
        $enemyCollection = new UnitCollection();
        $enemyCollection->add($enemyUnit);
        $enemyCommand = new Command($enemyCollection);

        self::assertEquals(0, $unit->getConcentration());
        self::assertEquals(0, $enemyUnit->getConcentration());
        self::assertEquals(0, $unit->getRage());
        self::assertEquals(0, $enemyUnit->getRage());

        $actionCollection = $unit->getActions($enemyCommand, $command);

        self::assertEquals(UnitInterface::ADD_CON_ACTION_UNIT, $unit->getConcentration());
        self::assertEquals(UnitInterface::ADD_RAGE_ACTION_UNIT, $unit->getRage());

        foreach ($actionCollection as $action) {
            $action->handle();
        }

        self::assertEquals(UnitInterface::ADD_CON_RECEIVING_UNIT, $enemyUnit->getConcentration());
        self::assertEquals(UnitInterface::ADD_RAGE_RECEIVING_UNIT, $enemyUnit->getRage());
    }

    /**
     * @throws Exception
     */
    public function testUnitUpMaxConcentration(): void
    {
        $unit = UnitFactory::createByTemplate(1);

        self::assertEquals(0, $unit->getConcentration());

        // Up concentration
        for ($i = 0; $i < 10; $i++) {
            $unit->newRound();
        }

        self::assertEquals(Unit::MAX_CONCENTRATION, $unit->getConcentration());
    }

    /**
     * @throws Exception
     */
    public function testUnitUpMaxRage(): void
    {
        $unit = UnitFactory::createByTemplate(1);

        self::assertEquals(0, $unit->getRage());

        // Up rage
        for ($i = 0; $i < 20; $i++) {
            $unit->newRound();
        }

        self::assertEquals(Unit::MAX_RAGE, $unit->getRage());
    }

    /**
     * @throws Exception
     */
    public function testUnitUseConcentrationAbility(): void
    {
        $unit = UnitFactory::createByTemplate(1);

        // Up concentration
        for ($i = 0; $i < 10; $i++) {
            $unit->newRound();
        }

        self::assertEquals(Unit::MAX_CONCENTRATION, $unit->getConcentration());

        $unit->useConcentrationAbility();
        self::assertEquals(0, $unit->getConcentration());
    }

    /**
     * @throws Exception
     */
    public function testUnitUseRageAbility(): void
    {
        $unit = UnitFactory::createByTemplate(1);

        // Up rage
        for ($i = 0; $i < 20; $i++) {
            $unit->newRound();
        }

        self::assertEquals(Unit::MAX_RAGE, $unit->getRage());

        $unit->useRageAbility();
        self::assertEquals(0, $unit->getRage());
    }

    /**
     * Проверяем корректный подсчет нескольких атак за ход
     *
     * Юнит имеет скорость атаки = 1.9999, специально, чтобы точно была одна атака, плюс, 99.99% шанс на добавление
     * второй атаки, которая, с 99.99% вероятностью будет добавлена. Недостаток теста - что в 1 вызове из 100 он будет
     * падать
     *
     * Просто указать скорость атаки 2 нельзя - будет просто 2 атаки, и не будет проверен именно шанс добавления
     * дополнительной атаки
     *
     * И затем делается аналогичный тест, только со скоростью атаки 1.0001, чтобы протестировать обратную ситуацию
     *
     * @throws Exception
     */
    public function testUnitCalculateAttackSpeed(): void
    {
        $unit = UnitFactory::createByTemplate(15);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactoryTest::createRightCommand();

        $actions = $unit->getActions($enemyCommand, $command);

        self::assertCount(2, $actions);

        foreach ($actions as $action) {
            self::assertInstanceOf(DamageAction::class, $action);
        }

        // В этом же тесте проверяем другую ситуацию, скорость атаки 1.0001 - т.е. расчет дополнительной атаки будет
        // Но он будет всегда неуспешным

        $unit = UnitFactory::createByTemplate(16);
        $command = CommandFactory::create([$unit]);

        $actions = $unit->getActions($enemyCommand, $command);

        self::assertCount(1, $actions);

        foreach ($actions as $action) {
            self::assertInstanceOf(DamageAction::class, $action);
        }
    }

    /**
     * В этом тесте проверяется, что если у юнита указан тип урона заклинание: 'damage_type' => 2
     *
     * То в расчете количества ударов используется параметр cast_speed, а не attack_speed
     *
     * @throws Exception
     */
    public function testUnitCalculateCastSpeed(): void
    {
        $unit = UnitFactory::createByTemplate(43);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactoryTest::createRightCommand();

        // Вначале на всякий случай проверяем, что скорость attack_speed = 0, а cast_speed = 3
        self::assertEquals(0, $unit->getOffense()->getAttackSpeed());
        self::assertEquals(3, $unit->getOffense()->getCastSpeed());

        $actions = $unit->getActions($enemyCommand, $command);

        // Проверяем, что получено 3 DamageAction
        self::assertCount(3, $actions);

        foreach ($actions as $action) {
            self::assertInstanceOf(DamageAction::class, $action);
        }
    }

    /**
     * Тест на удаление эффектов при смерти юнита
     *
     * @throws Exception
     */
    public function testUnitRemoveEffectsAtDie(): void
    {
        $unit = UnitFactory::createByTemplate(21);
        $enemyUnit = UnitFactory::createByTemplate(13);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        self::assertEquals(100, $unit->getTotalLife());
        self::assertEquals(100, $unit->getLife());
        self::assertCount(0, $unit->getEffects());

        for ($i = 0; $i < 10; $i++) {
            $unit->newRound();
        }

        $actions = $unit->getActions($enemyCommand, $command);

        foreach ($actions as $action) {
            self::assertTrue($action->canByUsed());
            // Применяем баф
            $action->handle();
        }

        self::assertEquals(130, $unit->getTotalLife());
        self::assertEquals(130, $unit->getLife());
        self::assertCount(1, $unit->getEffects());

        // Убиваем юнита
        $damageAction = new DamageAction(
           $this->container,
            $enemyUnit,
            $command,
            $enemyCommand,
            DamageAction::TARGET_RANDOM_ENEMY,
            true,
            DamageAction::DEFAULT_NAME,
            DamageAction::UNIT_ANIMATION_METHOD,
            DamageAction::DEFAULT_MESSAGE_METHOD,
            $enemyUnit->getOffense()
        );

        $damageAction->handle();

        // Проверяем, что он умер
        self::assertEquals(0, $unit->getLife());

        // Проверяем, что здоровье вернулось к изначальному
        self::assertEquals(100, $unit->getTotalLife());
        self::assertCount(0, $unit->getEffects());
    }

    /**
     * Тест на ситуацию, когда у мертвого юнита запрашиваются действия - получаем ошибку
     *
     * @throws Exception
     */
    public function testUnitGetActionDiedUnit(): void
    {
        $unit = UnitFactory::createByTemplate(10);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $this->expectException(UnitException::class);
        $this->expectExceptionMessage(UnitException::CANNOT_ACTION);
        $unit->getActions($enemyCommand, $command);
    }

    /**
     * Тест на ситуацию, когда у юнита, который уже ходил запрашиваются действия - получаем ошибку
     *
     * @throws Exception
     */
    public function testUnitGetActionAlreadyAction(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $unit->madeAction();

        $this->expectException(UnitException::class);
        $this->expectExceptionMessage(UnitException::CANNOT_ACTION);
        $unit->getActions($enemyCommand, $command);
    }

    /**
     * @throws Exception
     */
    public function testUnitParalysis(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        // Изначально юнит не обездвижен
        self::assertFalse($enemyUnit->isParalysis());

        // Накладываем паралич на $unit
        $ability = $this->getAbility($unit, 'Paralysis');

        foreach ($ability->getActions($enemyCommand, $command) as $action) {
            self::assertTrue($action->canByUsed());
            $action->handle();
        }

        // Теперь обездвижен
        self::assertTrue($enemyUnit->isParalysis());
    }

    /**
     * Тест на множитель к получению концентрации
     *
     * @throws Exception
     */
    public function testUnitAddedConcentrationMultiplier(): void
    {
        // Юнит со +40% получаемой концентрации
        $unit = UnitFactory::createByTemplate(45);

        self::assertEquals(0, $unit->getConcentration());

        // При новом раунде добавляется концентрация
        $unit->newRound();

        // Проверяем, что полученная концентрация больше на 40% чем базовое значение
        self::assertEquals((int)(UnitInterface::ADD_CON_NEW_ROUND * 1.4), $unit->getConcentration());
    }

    /**
     * Тест на множитель к получению концентрации
     *
     * @throws Exception
     */
    public function testUnitAddedRageMultiplier(): void
    {
        // Юнит со -20% получаемой ярости
        $unit = UnitFactory::createByTemplate(45);

        self::assertEquals(0, $unit->getRage());

        // При новом раунде добавляется ярость
        $unit->newRound();

        // Проверяем, что полученная ярость на 20% меньше чем базовое значение
        self::assertEquals((int)(UnitInterface::ADD_RAGE_NEW_ROUND * 0.8), $unit->getRage());
    }

    /**
     * Тест на получение (пустой) коллекции событий, когда юнит обрабатывает действие
     *
     * @throws Exception
     */
    public function testUnitApplyActionCallbackActionsEmpty(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $actions = $enemyUnit->getActions($command, $enemyCommand);

        self::assertCount(1, $actions);

        foreach ($actions as $action) {
            self::assertTrue($action->canByUsed());
            self::assertEquals(new ActionCollection(), $action->handle());
        }
    }

    /**
     * Тест на получение коллекции событий с оглушением, когда юнит обрабатывает урон по себе. Урон наносит юнит с
     * булавой и со 100% шансом нанесения критического удара
     *
     * @throws Exception
     */
    public function testUnitApplyActionCallbackActionsStun(): void
    {
        $unit = UnitFactory::createByTemplate(1, $this->container);
        $enemyUnit = UnitFactory::createByTemplate(47, $this->container);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $actions = $enemyUnit->getActions($command, $enemyCommand);

        self::assertCount(1, $actions);

        foreach ($actions as $action) {
            // Проверяем, что получили атаку
            self::assertEquals('attack', $action->getNameAction());

            // Вручную применяем событие к юниту (обычно делается $action->handle)
            $callbackActions = $unit->applyAction($action);

            // Проверяем полученные $callbackActions, и что это именно оглушение от оружия
            self::assertCount(1, $callbackActions);

            foreach ($callbackActions as $callbackAction) {
                self::assertEquals('Stun Weapon Effect', $callbackAction->getNameAction());
            }
        }
    }


    /**
     * Тест на работу методов addLastTarget() и clearLastTarget()
     *
     * @throws UnitException
     * @throws Exception
     */
    public function testUnitLastTargets(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);

        $expectedTargets = new UnitCollection();

        self::assertEquals($expectedTargets, $unit->getLastTargets());

        $unit->addLastTarget($enemyUnit);
        $expectedTargets->add($enemyUnit);

        self::assertEquals($expectedTargets, $unit->getLastTargets());

        $unit->clearLastTarget();

        self::assertEquals(new UnitCollection(), $unit->getLastTargets());
    }

    /**
     * Тест на автоматическое добавление последней цели при обработке события
     *
     * @throws Exception
     */
    public function testUnitTargetTracking(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $secondaryEnemyUnit = UnitFactory::createByTemplate(3);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit, $secondaryEnemyUnit]);

        $action = new DamageAction(
           $this->container,
            $unit,
            $enemyCommand,
            $command,
            DamageAction::TARGET_RANDOM_ENEMY,
            true,
            DamageAction::DEFAULT_NAME,
            DamageAction::UNIT_ANIMATION_METHOD,
            DamageAction::DEFAULT_MESSAGE_METHOD,
            $unit->getOffense()
        );

        $action->handle();

        $expectedTargets = new UnitCollection();

        // Проверяем, что в последние цели добавилась именно атакованная цель
        if ($enemyUnit->getLife() < $enemyUnit->getTotalLife()) {
            $expectedTargets->add($enemyUnit);
            self::assertEquals($expectedTargets, $unit->getLastTargets());
        }
        if ($secondaryEnemyUnit->getLife() < $secondaryEnemyUnit->getTotalLife()) {
            $expectedTargets->add($secondaryEnemyUnit);
            self::assertEquals($expectedTargets, $unit->getLastTargets());
        }

        // Проверяем очистку последних целей при новом раунде
        $unit->newRound();

        self::assertEquals(new UnitCollection(), $unit->getLastTargets());
    }

    /**
     * Тест на установку множителя получаемой концентрации меньше минимального значения
     *
     * @throws UnitFactoryException
     */
    public function testUnitOverMinAddConcentrationMultiplier(): void
    {
        $unit = UnitFactory::createByTemplate(1);

        $this->expectException(UnitException::class);
        $this->expectExceptionMessage(
            UnitException::INCORRECT_ADD_CONC_MULTIPLIER_VALUE . UnitInterface::MIN_RESOURCE_MULTIPLIER . ' - ' . UnitInterface::MAX_RESOURCE_MULTIPLIER
        );
        $unit->setAddConcentrationMultiplier(UnitInterface::MIN_RESOURCE_MULTIPLIER - 1);
    }

    /**
     * Тест на установку множителя получаемой концентрации больше максимального значения
     *
     * @throws UnitFactoryException
     */
    public function testUnitOverMaxAddConcentrationMultiplier(): void
    {
        $unit = UnitFactory::createByTemplate(1);

        $this->expectException(UnitException::class);
        $this->expectExceptionMessage(
            UnitException::INCORRECT_ADD_CONC_MULTIPLIER_VALUE . UnitInterface::MIN_RESOURCE_MULTIPLIER . ' - ' . UnitInterface::MAX_RESOURCE_MULTIPLIER
        );
        $unit->setAddConcentrationMultiplier(UnitInterface::MAX_RESOURCE_MULTIPLIER + 1);
    }

    /**
     * Тест на установку множителя хитрости меньше минимального значения
     *
     * @throws UnitFactoryException
     */
    public function testUnitOverMinAddCunningMultiplier(): void
    {
        $unit = UnitFactory::createByTemplate(1);

        $this->expectException(UnitException::class);
        $this->expectExceptionMessage(
            UnitException::INCORRECT_CUNNING_MULTIPLIER_VALUE . UnitInterface::MIN_RESOURCE_MULTIPLIER . ' - ' . UnitInterface::MAX_RESOURCE_MULTIPLIER
        );
        $unit->setCunningMultiplier(UnitInterface::MIN_RESOURCE_MULTIPLIER - 1);
    }

    /**
     * Тест на установку множителя хитрости больше максимального значения
     *
     * @throws UnitFactoryException
     */
    public function testUnitOverMaxAddCunningMultiplier(): void
    {
        $unit = UnitFactory::createByTemplate(1);

        $this->expectException(UnitException::class);
        $this->expectExceptionMessage(
            UnitException::INCORRECT_CUNNING_MULTIPLIER_VALUE . UnitInterface::MIN_RESOURCE_MULTIPLIER . ' - ' . UnitInterface::MAX_RESOURCE_MULTIPLIER
        );
        $unit->setCunningMultiplier(UnitInterface::MAX_RESOURCE_MULTIPLIER + 1);
    }

    /**
     * Тест на установку множителя получаемой ярости меньше минимального значения
     *
     * @throws UnitFactoryException
     */
    public function testUnitOverMinAddRageMultiplier(): void
    {
        $unit = UnitFactory::createByTemplate(1);

        $this->expectException(UnitException::class);
        $this->expectExceptionMessage(
            UnitException::INCORRECT_ADD_RAGE_MULTIPLIER_VALUE . UnitInterface::MIN_RESOURCE_MULTIPLIER . ' - ' . UnitInterface::MAX_RESOURCE_MULTIPLIER
        );
        $unit->setAddRageMultiplier(UnitInterface::MIN_RESOURCE_MULTIPLIER - 1);
    }

    /**
     * Тест на установку множителя получаемой ярости больше максимального значения
     *
     * @throws UnitFactoryException
     */
    public function testUnitOverMaxAddRageMultiplier(): void
    {
        $unit = UnitFactory::createByTemplate(1);

        $this->expectException(UnitException::class);
        $this->expectExceptionMessage(
            UnitException::INCORRECT_ADD_RAGE_MULTIPLIER_VALUE . UnitInterface::MIN_RESOURCE_MULTIPLIER . ' - ' . UnitInterface::MAX_RESOURCE_MULTIPLIER
        );
        $unit->setAddRageMultiplier(UnitInterface::MAX_RESOURCE_MULTIPLIER + 1);
    }

    /**
     * @throws Exception
     */
    public function testUnitIncreasedDamageInLongBattle(): void
    {
        $container = new Container(true);
        $unit = UnitFactory::createByTemplate(1, $container);
        $enemyUnit = UnitFactory::createByTemplate(2, $container);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        // Проверяем изначальное здоровье
        self::assertEquals(250, $enemyUnit->getLife());

        // Удар на первом раунде наносит х1 урон = 20
        $this->createDamageAction($unit, $enemyCommand, $command)->handle();
        self::assertEquals(230, $enemyUnit->getLife());

        for ($i = 0; $i < 30; $i++) {
            $container->getStatistic()->increasedRound();
        }

        // На 30+ раунде удар наносит х2 урона = 40
        $this->createDamageAction($unit, $enemyCommand, $command)->handle();

        self::assertEquals(190, $enemyUnit->getLife());

        for ($i = 0; $i < 10; $i++) {
            $container->getStatistic()->increasedRound();
        }

        // На 40+ раунде удар наносит х4 урона = 80
        $this->createDamageAction($unit, $enemyCommand, $command)->handle();

        self::assertEquals(110, $enemyUnit->getLife());
    }

    /**
     * @return array
     */
    public function createDataProvider(): array
    {
        return [
            [1], [2], [3], [4], [5], [6], [7], [8], [10], [11], [12], [13], [14], [15], [16], [17], [18], [19], [20],
            [21], [22], [23], [24], [25], [26], [27], [28], [29], [30], [31], [32], [33], [34], [35], [36], [37], [38],
            [39], [40], [41], [42], [43], [44], [45], [46], [47], [48], [49], [50], [51], [52],
        ];
    }

    /**
     * @return DefenseInterface
     * @throws Exception
     */
    private function getDefense(): DefenseInterface
    {
        return new Defense(
            0,
            0,
            0,
            0,
            0,
            0,
            0,
            10,
            10,
            10,
            5,
            0,
            75,
            75,
            75,
            75,
            75,
            75,
            75,
            0,
            0
        );
    }

    /**
     * @param UnitInterface $unit
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $command
     * @return DamageAction
     * @throws Exception
     */
    private function createDamageAction(
        UnitInterface $unit,
        CommandInterface $enemyCommand,
        CommandInterface $command
    ): DamageAction
    {
        return new DamageAction(
            $this->container,
            $unit,
            $enemyCommand,
            $command,
            DamageAction::TARGET_RANDOM_ENEMY,
            true,
            DamageAction::DEFAULT_NAME,
            DamageAction::UNIT_ANIMATION_METHOD,
            DamageAction::DEFAULT_MESSAGE_METHOD,
            $unit->getOffense(),
            null,
            '',
            true,
            false
        );
    }
}
