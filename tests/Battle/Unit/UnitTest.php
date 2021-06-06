<?php

declare(strict_types=1);

namespace Tests\Battle\Unit;

use Exception;
use Battle\Unit\Unit;
use Battle\Command\Command;
use Battle\Result\Chat\Message;
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
    }

    /**
     * @throws Exception
     */
    public function testUnitApplyDamage(): void
    {
        $message = new Message();
        $attackUnitTemplate = 2;
        $defendUnitTemplate = 6;
        $attackUnit = UnitFactory::createByTemplate($attackUnitTemplate, $message);
        $defendUnit = UnitFactory::createByTemplate($defendUnitTemplate, $message);

        $enemyCommand = CommandFactory::create([$defendUnit], $message);
        $alliesCommand = CommandFactory::create([$attackUnit], $message);

        $action = new DamageAction($attackUnit, $enemyCommand, $alliesCommand, $message);

        $action->handle();

        $defendLife = UnitFactory::getData($defendUnitTemplate)['life'];

        self::assertEquals($defendLife - $attackUnit->getDamage(), $defendUnit->getLife());

        $action2 = new DamageAction($attackUnit, $enemyCommand, $alliesCommand, $message);
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
     * Проверяем корректное обновление параметра action у юнита, при начале нового раунда, и добавление концентрации
     *
     * @throws Exception
     */
    public function testUnitAddedConcentration(): void
    {
        $template = 1;
        $unit = UnitFactory::createByTemplate($template);

        $unit->madeAction();
        self::assertTrue($unit->isAction());
        self::assertEquals(0, $unit->getConcentration());
        $unit->newRound();
        self::assertFalse($unit->isAction());
        self::assertEquals(Unit::ADD_CON_NEW_ROUND, $unit->getConcentration());
    }

    /**
     * Проверяем корректное добавление концентрации юниту, при начале нового раунда
     *
     * @throws Exception
     */
    public function testUnitNewRoundAddConcentration(): void
    {
        $unit = UnitFactory::createByTemplate(1);

        self::assertEquals(0 , $unit->getConcentration());

        $unit->newRound();

        self::assertEquals(UnitInterface::ADD_CON_NEW_ROUND, $unit->getConcentration());
    }

    /**
     * Тест на получение концентрации при совершении действия, и получения действия от другого юнита
     *
     * @throws CommandException
     * @throws UnitException
     * @throws Exception
     */
    public function testUnitAddConcentration(): void
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

        $actionCollection = $leftUnit->getAction($rightCommand, $leftCommand);

        self::assertEquals(UnitInterface::ADD_CON_ACTION_UNIT, $leftUnit->getConcentration());

        foreach ($actionCollection as $action) {
            $action->handle();
        }

        self::assertEquals(UnitInterface::ADD_CON_RECEIVING_UNIT, $rightUnit->getConcentration());
    }
}
