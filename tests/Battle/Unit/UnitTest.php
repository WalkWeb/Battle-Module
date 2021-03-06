<?php

declare(strict_types=1);

namespace Tests\Battle\Unit;

use Battle\Container\Container;
use Battle\Unit\Ability\AbilityCollection;
use Exception;
use Battle\Unit\Unit;
use Battle\Command\Command;
use Battle\Unit\UnitCollection;
use Battle\Unit\UnitException;
use Battle\Unit\UnitInterface;
use PHPUnit\Framework\TestCase;
use Battle\Command\CommandFactory;
use Battle\Command\CommandException;
use Tests\Battle\Factory\UnitFactory;
use Battle\Action\Damage\DamageAction;
use Tests\Battle\Factory\UnitFactoryException;
use Tests\Battle\Factory\Mock\ActionMockFactory;

class UnitTest extends TestCase
{
    /**
     * @throws UnitFactoryException
     * @throws Exception
     */
    public function testUniCreate(): void
    {
        $template = 1;
        $unit = UnitFactory::createByTemplate($template);
        $data = UnitFactory::getData($template);

        self::assertEquals($data['id'], $unit->getId());
        self::assertEquals($data['name'], $unit->getName());
        self::assertEquals($data['level'], $unit->getLevel());
        self::assertEquals($data['avatar'], $unit->getAvatar());
        self::assertEquals($data['damage'], $unit->getDamage());
        self::assertEquals($data['life'], $unit->getLife());
        self::assertEquals($data['total_life'], $unit->getTotalLife());
        self::assertEquals($data['attack_speed'], $unit->getAttackSpeed());
        self::assertFalse($unit->isAction());
        self::assertTrue($unit->isAlive());
        self::assertTrue($unit->isMelee());
        self::assertEquals($data['class'], $unit->getClass()->getId());
        self::assertEquals($data['race'], $unit->getRace()->getId());
        self::assertEquals($unit->getClass()->getAbilities($unit, $unit->getContainer()), $unit->getAbilities());
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

        $action = new DamageAction($attackUnit, $enemyCommand, $alliesCommand, $container->getMessage());

        $action->handle();

        $defendLife = UnitFactory::getData($defendUnitTemplate)['life'];

        self::assertEquals($defendLife - $attackUnit->getDamage(), $defendUnit->getLife());

        $action2 = new DamageAction($attackUnit, $enemyCommand, $alliesCommand, $container->getMessage());
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
     * ?????????????????? ???????????????????? ???????????????????? ?????????????????? action ?? ??????????, ?????? ???????????? ???????????? ????????????
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
     * ?????????????????? ???????????????????? ???????????????????? ???????????????????????? ?? ???????????? ??????????, ?????? ???????????? ???????????? ????????????
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
     * ???????? ???? ?????????????????? ???????????????????????? ?? ???????????? ?????? ???????????????????? ????????????????, ?? ?????????????????? ???????????????? ???? ?????????????? ??????????
     *
     * @throws CommandException
     * @throws UnitException
     * @throws Exception
     */
    public function testUnitAddConcentrationAndRage(): void
    {
        $leftUnit = UnitFactory::createByTemplate(1);
        $leftCollection = new UnitCollection();
        $leftCollection->add($leftUnit);
        $leftCommand = new Command($leftCollection);

        $rightUnit =  UnitFactory::createByTemplate(2);
        $rightCollection = new UnitCollection();
        $rightCollection->add($rightUnit);
        $rightCommand = new Command($rightCollection);

        self::assertEquals(0, $leftUnit->getConcentration());
        self::assertEquals(0, $rightUnit->getConcentration());
        self::assertEquals(0, $leftUnit->getRage());
        self::assertEquals(0, $rightUnit->getRage());

        $actionCollection = $leftUnit->getAction($rightCommand, $leftCommand);

        self::assertEquals(UnitInterface::ADD_CON_ACTION_UNIT, $leftUnit->getConcentration());
        self::assertEquals(UnitInterface::ADD_RAGE_ACTION_UNIT, $leftUnit->getRage());

        foreach ($actionCollection as $action) {
            $action->handle();
        }

        self::assertEquals(UnitInterface::ADD_CON_RECEIVING_UNIT, $rightUnit->getConcentration());
        self::assertEquals(UnitInterface::ADD_RAGE_RECEIVING_UNIT, $rightUnit->getRage());
    }

    /**
     * @throws Exception
     */
    public function testUnitUpMaxConcentration(): void
    {
        $unit = UnitFactory::createByTemplate(1);

        self::assertEquals(0, $unit->getConcentration());
        $unit->upMaxConcentration();
        self::assertEquals(Unit::MAX_CONS, $unit->getConcentration());
    }

    /**
     * @throws Exception
     */
    public function testUnitUmMaxRage(): void
    {
        $unit = UnitFactory::createByTemplate(1);

        self::assertEquals(0, $unit->getRage());
        $unit->upMaxRage();
        self::assertEquals(Unit::MAX_RAGE, $unit->getRage());
    }
}
