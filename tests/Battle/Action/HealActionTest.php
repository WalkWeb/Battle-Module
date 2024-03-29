<?php

declare(strict_types=1);

namespace Tests\Battle\Action;

use Battle\Action\ActionCollection;
use Battle\Action\ActionException;
use Battle\Action\ActionInterface;
use Battle\Action\DamageAction;
use Battle\Action\HealAction;
use Battle\Command\CommandException;
use Battle\Command\CommandFactory;
use Battle\Command\CommandInterface;
use Battle\Unit\UnitException;
use Battle\Unit\UnitInterface;
use Exception;
use Tests\AbstractUnitTest;
use Tests\Factory\UnitFactory;

class HealActionTest extends AbstractUnitTest
{
    /**
     * @throws CommandException
     * @throws UnitException
     * @throws Exception
     */
    public function testHealActionRealistic(): void
    {
        $priest = UnitFactory::createByTemplate(5);
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(3);
        $command = CommandFactory::create([$unit, $priest]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        // Наносим урон
        $damages = $enemyUnit->getActions($command, $enemyCommand);

        foreach ($damages as $damage) {
            $damage->handle();
        }

        // Проверяем, что у одного из юнитов здоровье уменьшилось
        self::assertTrue($unit->getLife() < $unit->getTotalLife() || $priest->getLife() < $priest->getTotalLife());

        // Накапливаем концентрацию
        for ($i = 0; $i < 10; $i++) {
            $priest->newRound();
        }

        // Применяем лечение (получаем Action от способности GreatHealAbility)
        $actions = $priest->getActions($enemyCommand, $command);

        foreach ($actions as $action) {
            self::assertEquals(HealAction::UNIT_ANIMATION_METHOD, $action->getAnimationMethod());
            self::assertEquals('healAbility', $action->getMessageMethod());
            // Проверяем, что лечение может быть использовано:
            self::assertTrue($action->canByUsed());
            $action->handle();

            // Проверяем factualPower
            self::assertEquals($priest->getOffense()->getDamage($enemyUnit->getDefense()), $action->getFactualPower());
            self::assertEquals($priest->getOffense()->getDamage($enemyUnit->getDefense()), $action->getFactualPowerByUnit($unit));
        }

        // Проверяем, что оба юнита стали здоровы
        self::assertTrue($unit->getLife() === $unit->getTotalLife() && $priest->getLife() === $priest->getTotalLife());
    }

    /**
     * Более простой вариант теста, без нанесения урона
     *
     * @throws Exception
     */
    public function testHealActionSimple(): void
    {
        $unit = UnitFactory::createByTemplate(5);
        $woundedUnit = UnitFactory::createByTemplate(11);
        $enemyUnit = UnitFactory::createByTemplate(3);
        $command = CommandFactory::create([$unit, $woundedUnit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        // Накапливаем концентрацию
        for ($i = 0; $i < 10; $i++) {
            $command->newRound();
        }

        // Применяем лечение
        $actions = $unit->getActions($enemyCommand, $command);

        foreach ($actions as $action) {
            self::assertEquals(new ActionCollection(), $action->handle());
        }

        // Проверяем лечение
        self::assertEquals(1 + $action->getPower(), $woundedUnit->getLife());
    }

    /**
     * Тест на ситуацию, когда у HealAction запрашивается фактическое лечение по юниту, по которому лечения не было
     *
     * @throws Exception
     */
    public function testHealActionNoPowerByUnit(): void
    {
        $unit = UnitFactory::createByTemplate(5);
        $woundedUnit = UnitFactory::createByTemplate(11);
        $enemyUnit = UnitFactory::createByTemplate(3);
        $command = CommandFactory::create([$unit, $woundedUnit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        // Накапливаем концентрацию
        for ($i = 0; $i < 10; $i++) {
            $command->newRound();
        }

        // Применяем лечение
        $actions = $unit->getActions($enemyCommand, $command);

        foreach ($actions as $action) {
            $action->handle();
        }

        // Общий factualPower получаем нормально
        self::assertEquals($action->getPower(), $action->getFactualPower());

        // factualPower, по юниту, по которому урон наносился - тоже
        self::assertEquals($action->getPower(), $action->getFactualPowerByUnit($woundedUnit));

        // А вот factualPower по юниту, по которому урон не наносился - отсутствует
        $this->expectException(ActionException::class);
        $this->expectExceptionMessage(ActionException::NO_POWER_BY_UNIT);
        $action->getFactualPowerByUnit($unit);
    }

    /**
     * @throws CommandException
     * @throws UnitException
     * @throws Exception
     */
    public function testGetPowerHealAction(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(3);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $healAction = new HealAction(
           $this->container,
            $unit,
            $enemyCommand,
            $command,
            HealAction::TARGET_WOUNDED_ALLIES,
            20,
            '',
            HealAction::UNIT_ANIMATION_METHOD,
            HealAction::DEFAULT_MESSAGE_METHOD
        );

        self::assertEquals($unit->getOffense()->getDamage($enemyUnit->getDefense()), $healAction->getPower());
    }

    /**
     * @throws Exception
     */
    public function testHealActionNoTargetForHeal(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $alliesUnit = UnitFactory::createByTemplate(5);
        $enemyUnit = UnitFactory::createByTemplate(3);
        $alliesCommand = CommandFactory::create([$unit, $alliesUnit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        for ($i = 0; $i < 10; $i++) {
            $alliesUnit->newRound();
        }

        // Лечить некого - по этому будет получен удар
        $actionCollection = $alliesUnit->getActions($enemyCommand, $alliesCommand);

        foreach ($actionCollection as $action) {
            self::assertContainsOnlyInstancesOf(DamageAction::class, [$action]);
            $action->handle();
        }

        self::assertEquals(
            $enemyUnit->getTotalLife() - $alliesUnit->getOffense()->getDamage($enemyUnit->getDefense()),
            $enemyUnit->getLife()
        );
    }

    /**
     * В этом тесте мы просто получаем исключение по прямому вызову getTargetUnit(), который используется после
     * применения способности
     *
     * @throws Exception
     */
    public function testHealActionNoTargetException(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(3);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = new HealAction(
           $this->container,
            $unit,
            $enemyCommand,
            $command,
            HealAction::TARGET_WOUNDED_ALLIES,
            20,
            '',
            HealAction::UNIT_ANIMATION_METHOD,
            HealAction::DEFAULT_MESSAGE_METHOD
        );

        $this->expectException(ActionException::class);
        $this->expectExceptionMessage(ActionException::NO_TARGET_UNIT);
        $action->getTargetUnits();
    }

    /**
     * В этом тесте эмулируем исключение ActionException::NO_TARGET_FOR_HEAL при вызове Action::handle()
     *
     * @throws Exception
     */
    public function testHealActionHandleNoTarget(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(3);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = new HealAction(
           $this->container,
            $unit,
            $enemyCommand,
            $command,
            HealAction::TARGET_WOUNDED_ALLIES,
            20,
            '',
            HealAction::UNIT_ANIMATION_METHOD,
            HealAction::DEFAULT_MESSAGE_METHOD
        );

        $this->expectException(ActionException::class);
        $this->expectExceptionMessage(ActionException::NO_TARGET_FOR_HEAL);
        $action->handle();
    }

    /**
     * Тест на ситуацию, когда лечение - это эффект, а юнит, на котором оно находится - полностью здоров
     *
     * @throws Exception
     */
    public function testHealActionNoCanByUsedSelf(): void
    {
        $unit = UnitFactory::createByTemplate(1, $this->container);
        $otherUnit = UnitFactory::createByTemplate(11, $this->container);
        $enemyUnit = UnitFactory::createByTemplate(2, $this->container);
        $command = CommandFactory::create([$unit, $otherUnit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $effectAction = $this->getEffectAction($unit, $command, $enemyCommand);

        // Применяем эффект на юнита
        self::assertTrue($effectAction->canByUsed());
        $effectAction->handle();

        // Проверяем, что юнит получил эффект
        self::assertCount(1, $unit->getEffects());

        // Проверяем, что сам эффект не может примениться
        $effects = $unit->getEffects();

        // Проверяем, что эффект не может примениться, потому что на кого он наложен - здоров
        foreach ($effects as $effect) {

            $actions = $effect->getOnNextRoundActions();

            foreach ($actions as $action) {
                self::assertFalse($action->canByUsed());
            }
        }
    }

    /**
     * Тест на механику выбора цели TARGET_WOUNDED_SELF
     *
     * @throws Exception
     */
    public function testHealActionTargetWoundedSelf(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = new HealAction(
           $this->container,
            $unit,
            $enemyCommand,
            $command,
            HealAction::TARGET_WOUNDED_SELF,
            20,
            '',
            HealAction::UNIT_ANIMATION_METHOD,
            HealAction::DEFAULT_MESSAGE_METHOD
        );

        // Цель не ранена - событие не может примениться
        self::assertEquals($unit->getLife(), $unit->getTotalLife());
        self::assertFalse($action->canByUsed());

        // Наносим урон
        $damages = $enemyUnit->getActions($command, $enemyCommand);

        foreach ($damages as $damage) {
            $damage->handle();
        }

        // Теперь цель ранена - событие может примениться
        self::assertTrue($unit->getLife() < $unit->getTotalLife());
        self::assertTrue($action->canByUsed());
    }

    /**
     * @throws Exception
     */
    public function testHealActionTargetAllWoundedAllies(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $slightlyWoundedUnit = UnitFactory::createByTemplate(9);
        $badlyWoundedUnit = UnitFactory::createByTemplate(11);
        $deadUnit = UnitFactory::createByTemplate(10);
        $enemyUnit = UnitFactory::createByTemplate(2);

        $command = CommandFactory::create([$unit, $slightlyWoundedUnit, $badlyWoundedUnit, $deadUnit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $power = 50;
        $oldLife = $badlyWoundedUnit->getLife();

        $action = new HealAction(
           $this->container,
            $unit,
            $enemyCommand,
            $command,
            HealAction::TARGET_ALL_WOUNDED_ALLIES,
            $power,
            '',
            HealAction::UNIT_ANIMATION_METHOD,
            HealAction::DEFAULT_MESSAGE_METHOD
        );

        $action->handle();

        // Слегка раненый юнит становится полностью здоровым
        self::assertEquals($slightlyWoundedUnit->getTotalLife(), $slightlyWoundedUnit->getLife());

        // Сильно раненый юнит восстанавливает здоровье
        self::assertEquals($oldLife + $power, $badlyWoundedUnit->getLife());

        // Проверяем, что мертвый юнит остался мертвым
        self::assertFalse($deadUnit->isAlive());
    }

    /**
     * Тест на вызов isBlocked() у не-DamageAction - всегда получаем false
     *
     * @throws Exception
     */
    public function testHealActionIsBlocked(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(3);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $healAction = new HealAction(
           $this->container,
            $unit,
            $enemyCommand,
            $command,
            HealAction::TARGET_WOUNDED_ALLIES,
            20,
            '',
            HealAction::UNIT_ANIMATION_METHOD,
            HealAction::DEFAULT_MESSAGE_METHOD
        );

        self::assertFalse($healAction->isBlocked($enemyUnit));
    }

    /**
     * @param UnitInterface $unit
     * @param CommandInterface $command
     * @param CommandInterface $enemyCommand
     * @return ActionInterface
     * @throws Exception
     */
    private function getEffectAction(
        UnitInterface $unit,
        CommandInterface $command,
        CommandInterface $enemyCommand
    ): ActionInterface
    {
        $data = [
            'type'           => ActionInterface::EFFECT,
            'action_unit'    => $unit,
            'enemy_command'  => $enemyCommand,
            'allies_command' => $command,
            'type_target'    => ActionInterface::TARGET_SELF,
            'name'           => 'Heal Potion',
            'effect'         => [
                'name'                  => 'Heal Potion',
                'icon'                  => 'icon.png',
                'duration'              => 10,
                'on_apply_actions'      => [],
                'on_next_round_actions' => [
                    [
                        'type'             => ActionInterface::HEAL,
                        'action_unit'      => $unit,
                        'enemy_command'    => $enemyCommand,
                        'allies_command'   => $command,
                        'type_target'      => ActionInterface::TARGET_SELF,
                        'name'             => '',
                        'power'            => 100,
                        'animation_method' => HealAction::EFFECT_ANIMATION_METHOD,
                        'message_method'   => HealAction::DEFAULT_MESSAGE_METHOD,
                    ],
                ],
                'on_disable_actions'    => [],
            ],
        ];

        return $this->container->getActionFactory()->create($data);
    }
}
