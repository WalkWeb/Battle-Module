<?php

declare(strict_types=1);

namespace Tests\Battle\Action;

use Battle\Action\ActionException;
use Battle\Action\ActionFactory;
use Battle\Action\ActionInterface;
use Battle\Action\DamageAction;
use Battle\Action\HealAction;
use Battle\Command\CommandException;
use Battle\Command\CommandFactory;
use Battle\Command\CommandInterface;
use Battle\Container\Container;
use Battle\Unit\UnitException;
use Battle\Unit\UnitInterface;
use Exception;
use PHPUnit\Framework\TestCase;
use Tests\Battle\Factory\UnitFactory;

class HealActionTest extends TestCase
{
    /**
     * @throws CommandException
     * @throws UnitException
     * @throws Exception
     */
    public function testHealActionRealistic(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $alliesUnit = UnitFactory::createByTemplate(5);
        $enemyUnit = UnitFactory::createByTemplate(3);
        $alliesCommand = CommandFactory::create([$unit, $alliesUnit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        // Наносим урон
        $damages = $enemyUnit->getAction($alliesCommand, $enemyCommand);

        foreach ($damages as $damage) {
            $damage->handle();
        }

        // Проверяем, что у одного из юнитов здоровье уменьшилось
        self::assertTrue($unit->getLife() < $unit->getTotalLife() || $alliesUnit->getLife() < $alliesUnit->getTotalLife());

        // Накапливаем концентрацию
        for ($i = 0; $i < 10; $i++) {
            $alliesUnit->newRound();
        }

        // Применяем лечение (получаем Action от способности GreatHealAbility)
        $heals =  $alliesUnit->getAction($enemyCommand, $alliesCommand);

        foreach ($heals as $heal) {
            self::assertEquals(HealAction::UNIT_ANIMATION_METHOD, $heal->getAnimationMethod());
            self::assertEquals('healAbility', $heal->getMessageMethod());
            // Проверяем, что лечение может быть использовано:
            self::assertTrue($heal->canByUsed());
            $heal->handle();
        }

        // Проверяем, что оба юнита стали здоровы
        self::assertTrue($unit->getLife() === $unit->getTotalLife() && $alliesUnit->getLife() === $alliesUnit->getTotalLife());
    }

    /**
     * Более простой вариант теста, без нанесения урона
     *
     * @throws Exception
     */
    public function testHealActionSimple(): void
    {
        $actionUnit = UnitFactory::createByTemplate(5);
        $woundedUnit = UnitFactory::createByTemplate(11);
        $enemyUnit = UnitFactory::createByTemplate(3);
        $actionCommand = CommandFactory::create([$actionUnit, $woundedUnit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        // Накапливаем концентрацию
        for ($i = 0; $i < 10; $i++) {
            $actionCommand->newRound();
        }

        // Применяем лечение
        $actions = $actionUnit->getAction($enemyCommand, $actionCommand);

        foreach ($actions as $action) {
            $action->handle();
        }

        // Проверяем лечение
        self::assertEquals(1 + $actionUnit->getDamage() * 3, $woundedUnit->getLife());
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

        $healAction = new HealAction($unit, $enemyCommand, $command, HealAction::TARGET_WOUNDED_ALLIES);

        self::assertEquals($unit->getDamage(), $healAction->getPower());
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
        $actionCollection = $alliesUnit->getAction($enemyCommand, $alliesCommand);

        foreach ($actionCollection as $action) {
            self::assertContainsOnlyInstancesOf(DamageAction::class, [$action]);
            $action->handle();
        }

        self::assertEquals($enemyUnit->getTotalLife() - $alliesUnit->getDamage(), $enemyUnit->getLife());
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

        $action = new HealAction($unit, $enemyCommand, $command, HealAction::TARGET_WOUNDED_ALLIES);

        $this->expectException(ActionException::class);
        $this->expectExceptionMessage(ActionException::NO_TARGET_UNIT);
        $action->getTargetUnit();
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

        $action = new HealAction($unit, $enemyCommand, $command, HealAction::TARGET_WOUNDED_ALLIES);

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
        $container = new Container();
        $unit = UnitFactory::createByTemplate(1, $container);
        $otherUnit = UnitFactory::createByTemplate(11, $container);
        $enemyUnit = UnitFactory::createByTemplate(2, $container);
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
        $actionFactory = new ActionFactory();

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
                        'type'            => ActionInterface::HEAL,
                        'action_unit'     => $unit,
                        'enemy_command'   => $enemyCommand,
                        'allies_command'  => $command,
                        'type_target'     => ActionInterface::TARGET_SELF,
                        'name'            => null,
                        'power'           => 100,
                        'animation_method' => HealAction::EFFECT_ANIMATION_METHOD,
                    ],
                ],
                'on_disable_actions'    => [],
            ],
        ];

        return $actionFactory->create($data);
    }
}
