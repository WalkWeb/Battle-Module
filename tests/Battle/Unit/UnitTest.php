<?php

declare(strict_types=1);

namespace Tests\Battle\Unit;

use Battle\Action\ActionException;
use Battle\Container\Container;
use Exception;
use Battle\Unit\Unit;
use Battle\Command\Command;
use Battle\Unit\UnitCollection;
use Battle\Unit\UnitException;
use Battle\Unit\UnitInterface;
use Battle\Command\CommandFactory;
use Battle\Command\CommandException;
use Tests\AbstractUnitTest;
use Tests\Battle\Factory\UnitFactory;
use Battle\Action\DamageAction;
use Tests\Battle\Factory\UnitFactoryException;
use Tests\Battle\Factory\Mock\ActionMockFactory;
use Tests\Battle\Factory\CommandFactory as CommandFactoryTest;

class UnitTest extends AbstractUnitTest
{
    /**
     * @dataProvider createDataProvider
     * @param int $template
     * @throws UnitFactoryException
     */
    public function testUniCreate(int $template): void
    {
        $unit = UnitFactory::createByTemplate($template);
        $data = UnitFactory::getData($template);

        self::assertEquals($data['id'], $unit->getId());
        self::assertEquals($data['name'], $unit->getName());
        self::assertEquals($data['level'], $unit->getLevel());
        self::assertEquals($data['avatar'], $unit->getAvatar());
        self::assertEquals($data['damage'], $unit->getDamage());
        self::assertEquals(round($data['damage'] * $data['attack_speed'], 1), $unit->getDPS());
        self::assertEquals($data['life'], $unit->getLife());
        self::assertEquals($data['total_life'], $unit->getTotalLife());
        self::assertEquals($data['attack_speed'], $unit->getAttackSpeed());
        self::assertEquals($data['block'], $unit->getBlock());
        self::assertFalse($unit->isAction());
        self::assertEquals($data['life'] > 0, $unit->isAlive());
        self::assertEquals($data['melee'], $unit->isMelee());
        self::assertEquals($data['race'], $unit->getRace()->getId());

        if ($data['class']) {
            self::assertEquals($data['class'], $unit->getClass()->getId());
            self::assertEquals($unit->getClass()->getAbilities($unit), $unit->getAbilities());
        }
    }

    /**
     * @throws Exception
     */
    public function testUnitApplyDamage(): void
    {
        $container = new Container();
        $attackUnitTemplate = 2;
        $defendUnitTemplate = 6;
        $attackUnit = UnitFactory::createByTemplate($attackUnitTemplate, $container);
        $defendUnit = UnitFactory::createByTemplate($defendUnitTemplate, $container);

        $enemyCommand = CommandFactory::create([$defendUnit], $container);
        $alliesCommand = CommandFactory::create([$attackUnit], $container);

        $action = new DamageAction($attackUnit, $enemyCommand, $alliesCommand, DamageAction::TARGET_RANDOM_ENEMY);

        $action->handle();

        $defendLife = UnitFactory::getData($defendUnitTemplate)['life'];

        self::assertEquals($defendLife - $attackUnit->getDamage(), $defendUnit->getLife());

        $action2 = new DamageAction($attackUnit, $enemyCommand, $alliesCommand, DamageAction::TARGET_RANDOM_ENEMY);
        $action2->handle();

        self::assertEquals(0, $defendUnit->getLife());
        self::assertFalse($defendUnit->isAlive());
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
     * @throws CommandException
     * @throws UnitException
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

        $actionCollection = $unit->getAction($enemyCommand, $command);

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

        self::assertEquals(Unit::MAX_CONS, $unit->getConcentration());
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

        self::assertEquals(Unit::MAX_CONS, $unit->getConcentration());

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

        $actions = $unit->getAction($enemyCommand, $command);

        self::assertCount(2, $actions);

        foreach ($actions as $action) {
            self::assertInstanceOf(DamageAction::class, $action);
        }

        // В этом же тесте проверяем другую ситуацию, скорость атаки 1.0001 - т.е. расчет дополнительной атаки будет
        // Но он будет всегда неуспешным

        $unit = UnitFactory::createByTemplate(16);
        $command = CommandFactory::create([$unit]);

        $actions = $unit->getAction($enemyCommand, $command);

        self::assertCount(1, $actions);

        foreach ($actions as $action) {
            self::assertInstanceOf(DamageAction::class, $action);
        }
    }

    /**
     * Тест на удаление эффектов при смерти юнита
     *
     * @throws CommandException
     * @throws UnitException
     * @throws UnitFactoryException
     * @throws ActionException
     */
    public function testUnitRemoveEffectsAtDie(): void
    {
        $unit = UnitFactory::createByTemplate(21);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        self::assertEquals(100, $unit->getTotalLife());
        self::assertEquals(100, $unit->getLife());
        self::assertCount(0, $unit->getEffects());

        for ($i = 0; $i < 10; $i++) {
            $unit->newRound();
        }

        $actions = $unit->getAction($enemyCommand, $command);

        foreach ($actions as $action) {
            self::assertTrue($action->canByUsed());
            // Применяем баф
            $action->handle();
        }

        self::assertEquals(130, $unit->getTotalLife());
        self::assertEquals(130, $unit->getLife());
        self::assertCount(1, $unit->getEffects());

        // Убиваем юнита
        $damageAction = new DamageAction($enemyUnit, $command, $enemyCommand, DamageAction::TARGET_RANDOM_ENEMY, 500);

        $damageAction->handle();

        // Проверяем, что он умер
        self::assertEquals(0, $unit->getLife());

        // Проверяем, что здоровье вернулось к изначальному
        self::assertEquals(100, $unit->getTotalLife());
        self::assertCount(0, $unit->getEffects());
    }

    public function createDataProvider(): array
    {
        return [
            [1], [2], [3], [4], [5], [6], [7], [8], [10], [11], [12], [13], [14], [15], [16], [17], [18], [19], [20],
            [21], [22], [23], [24], [25], [26], [27], [28],
        ];
    }
}
