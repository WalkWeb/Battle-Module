<?php

declare(strict_types=1);

namespace Tests\Battle\Command;

use Battle\Action\ActionFactory;
use Battle\Action\ActionInterface;
use Battle\Action\EffectAction;
use Battle\Command\CommandInterface;
use Battle\Unit\Effect\EffectFactory;
use Battle\Unit\Effect\EffectInterface;
use Exception;
use Battle\Battle;
use Battle\Container\Container;
use Battle\Command\Command;
use Battle\Command\CommandException;
use Battle\Command\CommandFactory;
use Battle\Unit\UnitCollection;
use Battle\Unit\UnitInterface;
use PHPUnit\Framework\TestCase;
use Battle\Action\DamageAction;
use Tests\Battle\Factory\UnitFactory;
use Tests\Battle\Factory\Mock\UnitMockFactory;
use Tests\Battle\Factory\CommandFactory as TestCommandFactory;

class CommandTest extends TestCase
{
    /**
     * Проверяем успешное создание команды
     *
     * @throws Exception
     */
    public function testCreate(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $command = CommandFactory::create([$unit]);
        self::assertInstanceOf(Command::class, $command);
    }

    /**
     * Проверяем соответствие переданных юнитов и тех, что возвращает команда
     *
     * @throws Exception
     */
    public function testCommandUnits(): void
    {
        $units = [UnitFactory::createByTemplate(1), UnitFactory::createByTemplate(2)];

        $collection = new UnitCollection();
        foreach ($units as $unit) {
            $collection->add($unit);
        }

        $command = CommandFactory::create($units);
        self::assertEquals($collection, $command->getUnits());
    }

    /**
     * Проверяем корректный возврат юнитов ближнего и дальнего боя
     *
     * @throws Exception
     */
    public function testMeleeAndRangeUnits(): void
    {
        $meleeUnit = UnitFactory::createByTemplate(1);
        $rangeUnit = UnitFactory::createByTemplate(5);
        $command = CommandFactory::create([$meleeUnit, $rangeUnit]);

        $resultMeleeUnits = $command->getMeleeUnits();
        $resultRangeUnits = $command->getRangeUnits();

        self::assertCount(1, $resultMeleeUnits);
        self::assertCount(1, $resultRangeUnits);

        foreach ($resultMeleeUnits as $resultMeleeUnit) {
            self::assertEquals($meleeUnit, $resultMeleeUnit);
        }

        foreach ($resultRangeUnits as $resultRangeUnit) {
            self::assertEquals($rangeUnit, $resultRangeUnit);
        }
    }

    /**
     * Проверяем корректное отсутствие бойцов ближнего боя
     *
     * @throws Exception
     */
    public function testNoMeleeUnits(): void
    {
        $rangeUnit = UnitFactory::createByTemplate(5);
        $command = CommandFactory::create([$rangeUnit]);

        self::assertFalse($command->existMeleeUnits());
        self::assertCount(0, $command->getMeleeUnits());
    }

    /**
     * Проверяем корректное отсутствие живых бойцов ближнего боя
     *
     * @throws Exception
     */
    public function testNoAliveMeleeUnits(): void
    {
        $meleeUnit = UnitFactory::createByTemplate(10);
        $rangeUnit = UnitFactory::createByTemplate(5);
        $command = CommandFactory::create([$meleeUnit, $rangeUnit]);
        self::assertFalse($command->existMeleeUnits());
        self::assertNull($command->getMeleeUnitForAttacks());

        // Проверяем, что возвращается именно юнит дальнего боя
        self::assertEquals($rangeUnit->getId(), $command->getUnitForAttacks()->getId());
    }

    /**
     * Проверяем неуспешное создание команды - не передан пустой массив
     *
     * @throws Exception
     */
    public function testNoUnits(): void
    {
        $this->expectException(CommandException::class);
        new Command(new UnitCollection());
    }

    /**
     * Проверяем неуспешное создание команды - передан некорректный объект
     *
     * @throws Exception
     */
    public function testIncorrectUnit(): void
    {
        $this->expectException(CommandException::class);
        $array = ['name' => 'unit', 'damage' => 10, 'life' => 100];
        CommandFactory::create([(object)$array]);
    }

    /**
     * Проверяем корректное возвращение юнита для получения удара
     *
     * @throws Exception
     */
    public function testGetUserFromAttack(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $command = CommandFactory::create([$unit]);
        $defined = $command->getUnitForAttacks();

        self::assertTrue($command->isAlive());
        self::assertInstanceOf(UnitInterface::class, $defined);
        self::assertEquals($unit->getName(), $defined->getName());
        self::assertTrue($defined->isAlive());
        self::assertTrue($defined->getLife() > 0);
    }

    /**
     * Проверяем корректное отсутствие юнитов для атаки и отсутствие живых юнитов в команде
     *
     * @throws Exception
     */
    public function testNoUnitFromAttack(): void
    {
        $unit = UnitFactory::createByTemplate(10);
        $command = CommandFactory::create([$unit]);
        self::assertEquals(null, $command->getUnitForAction());
        self::assertEquals(null, $command->getUnitForAttacks());
        self::assertEquals(false, $command->isAlive());
    }

    /**
     * Проверяем, что все юниты походили и на начало нового раунда - все юниты опять могут ходить
     *
     * @throws Exception
     */
    public function testAllUnitAction(): void
    {
        $units = [UnitFactory::createByTemplate(1), UnitFactory::createByTemplate(2)];
        $command = CommandFactory::create($units);

        $firstActionUnit = $command->getUnitForAction();
        $firstActionUnit->madeAction();
        $secondActionUnit = $command->getUnitForAction();
        $secondActionUnit->madeAction();

        self::assertEquals(null, $command->getUnitForAction());

        $command->newRound();

        $firstActionUnit = $command->getUnitForAction();
        $firstActionUnit->madeAction();
        $secondActionUnit = $command->getUnitForAction();
        $secondActionUnit->madeAction();

        self::assertEquals(null, $command->getUnitForAction());
    }

    /**
     * Проверяем корректное возвращение юнита для совершения хода
     *
     * @throws Exception
     */
    public function testGetUnitForActionOne(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $command = CommandFactory::create([$unit]);

        self::assertEquals($unit, $command->getUnitForAction());
    }

    /**
     * Проверяем корректное отсутствие юнитов для хода, когда один может ходить но мертвый, другой - живой но уже ходил
     *
     * @throws Exception
     */
    public function testGetUnitForActionNothing(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $alliesUnit = UnitFactory::createByTemplate(5);
        $enemyUnit = UnitFactory::createByTemplate(12);

        $alliesCommand = CommandFactory::create([$unit, $alliesUnit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        // вначале юнит присутствует
        self::assertInstanceOf(UnitInterface::class, $alliesCommand->getUnitForAction());

        // убиваем первого юнита ($alliesCommand и $enemyCommand переставлены местами - это правильно, ходит вражеская команда)
        $action = new DamageAction($enemyUnit, $alliesCommand, $enemyCommand, DamageAction::TARGET_RANDOM_ENEMY);
        $action->handle();

        // указываем, что второй юнит походил
        $alliesUnit->madeAction();

        self::assertNull($alliesCommand->getUnitForAction());
    }

    /**
     * Тест на необычную ситуацию, когда юниты в команде вначале сообщают, что есть готовые ходить, а при попытке
     * вернуть такого юнита - его нет
     *
     * @throws Exception
     */
    public function testCommandGetUnitForActionBroken(): void
    {
        $factory = new UnitMockFactory();
        $unit = $factory->create();
        $command = CommandFactory::create([$unit]);

        $this->expectException(CommandException::class);
        $this->expectExceptionMessage(CommandException::UNEXPECTED_EVENT_NO_ACTION_UNIT);
        $command->getUnitForAction();
    }

    /**
     * @throws Exception
     */
    public function testCommandClone(): void
    {
        $leftCommand = TestCommandFactory::createLeftCommand();
        $rightCommand = TestCommandFactory::createRightCommand();

        $battle = new Battle($leftCommand, $rightCommand, new Container());
        $result = $battle->handle();

        // Проверяем клонирование команд
        foreach ($result->getStartLeftCommand()->getUnits() as $unit) {
            self::assertEquals($unit->getLife(), $unit->getTotalLife());
        }

        foreach ($result->getStartRightCommand()->getUnits() as $unit) {
            self::assertEquals($unit->getLife(), $unit->getTotalLife());
        }
    }

    /**
     * @throws Exception
     */
    public function testCommandTotalLife(): void
    {
        $warrior = UnitFactory::createByTemplate(1);
        $priest = UnitFactory::createByTemplate(5);

        $command = CommandFactory::create([$warrior, $priest]);

        self::assertEquals($warrior->getTotalLife() + $priest->getTotalLife(), $command->getTotalLife());

        // Нанесем урон и проверим общее здоровье еще раз
        $zombie = UnitFactory::createByTemplate(1);
        $enemyCommand = CommandFactory::create([$zombie]);

        $damage = new DamageAction($zombie, $command, $enemyCommand, DamageAction::TARGET_RANDOM_ENEMY);
        $damage->handle();

        self::assertEquals($warrior->getTotalLife() + $priest->getTotalLife() - $zombie->getDamage(), $command->getTotalLife());
    }

    // ---------------------------------- Тест на метод getUnitForEffect() ---------------------------------------------
    // Проверены следующие ситуации:
    // 1. Живых юнитов в команде нет         => получаем null
    // 2. Юнит с эффектом + Мертвый юнит     => получаем null
    // 3. Два живых юнита, оба с эффектом    => получаем null
    // 4. Один живой юнит без эффекта        => получаем unit
    // 5. Юнит без эффекта + Мертвый юнит    => получаем unit
    // 6. Юнит с эффектом + Юнит без эффекта => получаем unit

    /**
     * 1. Тест на получение юнита для эффекта, когда живых юнитов в команде нет - получаем null
     *
     * @throws Exception
     */
    public function testCommandGetUnitForEffectNoAliveUnits(): void
    {
        $unit = UnitFactory::createByTemplate(10);
        $enemyUnit = UnitFactory::createByTemplate(1);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $effect = $this->createEffect($unit, $command, $enemyCommand);

        self::assertNull($command->getUnitForEffect($effect));
    }

    /**
     * 2. Тест на получение юнита для эффекта, когда есть два юнита - один живой с эффектом, а другой мертвый - получаем
     * null
     *
     * @throws Exception
     */
    public function testCommandGetUnitForEffectNoUnits(): void
    {
        $unit = UnitFactory::createByTemplate(2);
        $otherUnit = UnitFactory::createByTemplate(10);
        $enemyUnit = UnitFactory::createByTemplate(1);
        $command = CommandFactory::create([$unit, $otherUnit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $effect = $this->createEffect($unit, $command, $enemyCommand);

        $action = $this->createEffectAction($effect, $unit, $command, $enemyCommand);

        self::assertTrue($action->canByUsed());
        $action->handle();

        // Проверяем, что юнит получил эффект
        self::assertTrue($unit->getEffects()->exist($effect));

        // Проверяем, что новых целей для наложения эффекта нет
        self::assertNull($command->getUnitForEffect($effect));

        // Проверяем, что Action больше не может примениться
        self::assertFalse($action->canByUsed());
    }

    /**
     * 3. Тест на получение юнита для эффекта, когда есть два юнита - оба живых, и оба с эффектом - получаем null
     *
     * @throws Exception
     */
    public function testCommandGetUnitForEffectAllHaveEffect(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $otherUnit = UnitFactory::createByTemplate(2);
        $enemyUnit = UnitFactory::createByTemplate(3);
        $command = CommandFactory::create([$unit, $otherUnit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $effect = $this->createEffect($unit, $command, $enemyCommand);

        $action = $this->createEffectAction($effect, $unit, $command, $enemyCommand);

        self::assertTrue($action->canByUsed());
        $action->handle();

        $action = $this->createEffectAction($effect, $otherUnit, $command, $enemyCommand);

        self::assertTrue($action->canByUsed());
        $action->handle();

        self::assertNull($command->getUnitForEffect($effect));
    }

    /**
     * 4. Тест на получение юнита для эффекта, когда есть только один юнит, он живой и не имеет указанного эффекта
     *
     * @throws Exception
     */
    public function testCommandGetUnitForEffectOneUnit(): void
    {
        $unit = UnitFactory::createByTemplate(2);
        $enemyUnit = UnitFactory::createByTemplate(1);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $effect = $this->createEffect($unit, $command, $enemyCommand);

        self::assertEquals($unit, $command->getUnitForEffect($effect));
    }

    /**
     * 5. Тест на получение юнита для эффекта, когда есть два юнита - один живой и без эффета, а другой мертвый
     *
     * @throws Exception
     */
    public function testCommandGetUnitForEffectOneAliveUnit(): void
    {
        $unit = UnitFactory::createByTemplate(10);
        $otherUnit = UnitFactory::createByTemplate(3);
        $enemyUnit = UnitFactory::createByTemplate(1);
        $command = CommandFactory::create([$unit, $otherUnit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $effect = $this->createEffect($unit, $command, $enemyCommand);

        self::assertEquals($otherUnit, $command->getUnitForEffect($effect));
    }

    /**
     * 6. Тест на получение юнита для эффекта, когда есть два юнита - юнит с эффектом и юнит без эффекта
     *
     * @throws Exception
     */
    public function testCommandGetUnitForEffectExistOne(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $otherUnit = UnitFactory::createByTemplate(2);
        $enemyUnit = UnitFactory::createByTemplate(3);
        $command = CommandFactory::create([$unit, $otherUnit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $effect = $this->createEffect($unit, $command, $enemyCommand);

        $action = $this->createEffectAction($effect, $unit, $command, $enemyCommand);

        self::assertTrue($action->canByUsed());
        $action->handle();

        self::assertEquals($otherUnit, $command->getUnitForEffect($effect));
    }

    // ---------------------------------- Тест на метод getUnitForEffectHeal() -----------------------------------------
    // Проверены следующие ситуации:
    // 1. Один живой юнит не имеющий эффекта => null
    // 2. Один раненый юнит не имеющий эффекта => unit
    // 3. Один раненый юнит с эффектом => null
    // 4. Два раненых юнита, один с эффектом, другой без => unit
    // 5. Два раненых юнита, выбирается самый раненый => unit

    /**
     * 1. Один живой юнит не имеющий эффекта. Возвращает null
     *
     * @throws Exception
     */
    public function testCommandGetUnitForEffectHealFullLife(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(3);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $effect = $this->createEffect($unit, $command, $enemyCommand);

        self::assertNull($command->getUnitForEffectHeal($effect));
    }

    /**
     * 2. Один раненый юнит не имеющий эффекта. Возвращается этот юнит
     *
     * @throws Exception
     */
    public function testCommandGetUnitForEffectHealWoundedUnit(): void
    {
        $unit = UnitFactory::createByTemplate(11);
        $enemyUnit = UnitFactory::createByTemplate(3);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $effect = $this->createEffect($unit, $command, $enemyCommand);

        self::assertEquals($unit, $command->getUnitForEffectHeal($effect));
    }

    /**
     * 3. Один раненый юнит с эффектом. Получаем null
     *
     * @throws Exception
     */
    public function testCommandGetUnitForEffectHealOneWoundedUnit(): void
    {
        $unit = UnitFactory::createByTemplate(11);
        $enemyUnit = UnitFactory::createByTemplate(3);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $effect = $this->createEffect($unit, $command, $enemyCommand);

        $action = $this->createEffectAction($effect, $unit, $command, $enemyCommand);

        // Применяем эффект
        self::assertTrue($action->canByUsed());
        $action->handle();

        self::assertNull($command->getUnitForEffectHeal($effect));
    }

    /**
     * 4. Два раненых юнита, один с эффектом, другой без. Выбирается юнит без эффекта
     *
     * @throws Exception
     */
    public function testCommandGetUnitForEffectHealWhoWoundedUnit(): void
    {
        $unit = UnitFactory::createByTemplate(11);
        $otherUnit = UnitFactory::createByTemplate(9);
        $enemyUnit = UnitFactory::createByTemplate(3);
        $command = CommandFactory::create([$unit, $otherUnit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $effect = $this->createEffect($unit, $command, $enemyCommand);

        $action = $this->createEffectAction($effect, $unit, $command, $enemyCommand);

        // Применяем эффект
        self::assertTrue($action->canByUsed());
        $action->handle();

        // getUnitForEffectHeal() возвращает второго юнита в команде, без эффекта
        self::assertEquals($otherUnit, $command->getUnitForEffectHeal($effect));
    }

    /**
     * 5. Два раненых юнита, выбирается самый раненый
     *
     * @throws Exception
     */
    public function testCommandGetUnitForEffectHealMostWounded(): void
    {
        // Life: 1/100
        $unit = UnitFactory::createByTemplate(11);
        // Life: 90/100
        $otherUnit = UnitFactory::createByTemplate(9);
        $enemyUnit = UnitFactory::createByTemplate(3);
        $command = CommandFactory::create([$unit, $otherUnit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $effect = $this->createEffect($unit, $command, $enemyCommand);

        self::assertEquals($unit, $command->getUnitForEffectHeal($effect));
    }

    /**
     * @param UnitInterface $unit
     * @param CommandInterface $command
     * @param CommandInterface $enemyCommand
     * @return EffectInterface
     * @throws Exception
     */
    private function createEffect(
        UnitInterface $unit,
        CommandInterface $command,
        CommandInterface $enemyCommand
    ): EffectInterface
    {
        $factory = new EffectFactory(new ActionFactory());

        $data = [
            'name'                  => 'Poison',
            'icon'                  => 'icon.png',
            'duration'              => 10,
            'on_apply_actions'      => [],
            'on_next_round_actions' => [
                [
                    'type'             => ActionInterface::DAMAGE,
                    'action_unit'      => $unit,
                    'enemy_command'    => $enemyCommand,
                    'allies_command'   => $command,
                    'type_target'      => ActionInterface::TARGET_SELF,
                    'name'             => 'Poison',
                    'power'            => 15,
                    'animation_method' => DamageAction::EFFECT_ANIMATION_METHOD,
                    'message_method'   => DamageAction::EFFECT_MESSAGE_METHOD,
                ],
            ],
            'on_disable_actions'    => [],
        ];

        return $factory->create($data);
    }

    /**
     * @param EffectInterface $effect
     * @param UnitInterface $unit
     * @param CommandInterface $command
     * @param CommandInterface $enemyCommand
     * @return ActionInterface
     */
    private function createEffectAction(
        EffectInterface $effect,
        UnitInterface $unit,
        CommandInterface $command,
        CommandInterface $enemyCommand
    ): ActionInterface
    {
        return new EffectAction(
            $unit,
            $enemyCommand,
            $command,
            EffectAction::TARGET_SELF,
            'effect',
            $effect
        );
    }
}
