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
use Battle\Action\DamageAction;
use Tests\AbstractUnitTest;
use Tests\Battle\Factory\UnitFactory;
use Tests\Battle\Factory\Mock\UnitMockFactory;
use Tests\Battle\Factory\CommandFactory as TestCommandFactory;

class CommandTest extends AbstractUnitTest
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
        $action = new DamageAction(
            $enemyUnit,
            $alliesCommand,
            $enemyCommand,
            DamageAction::TARGET_RANDOM_ENEMY,
            $enemyUnit->getOffense(),
            true,
            DamageAction::DEFAULT_NAME,
            DamageAction::UNIT_ANIMATION_METHOD,
            DamageAction::DEFAULT_MESSAGE_METHOD
        );

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
        $enemyUnit = UnitFactory::createByTemplate(1);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $damage = new DamageAction(
            $enemyUnit,
            $command,
            $enemyCommand,
            DamageAction::TARGET_RANDOM_ENEMY,
            $enemyUnit->getOffense(),
            true,
            DamageAction::DEFAULT_NAME,
            DamageAction::UNIT_ANIMATION_METHOD,
            DamageAction::DEFAULT_MESSAGE_METHOD
        );

        $damage->handle();

        self::assertEquals($warrior->getTotalLife() + $priest->getTotalLife() - $enemyUnit->getOffense()->getDamage($warrior->getDefense()), $command->getTotalLife());
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
     * @throws Exception
     */
    public function testGetUnitForHealNull(): void
    {
        $unit = UnitFactory::createByTemplate(10);

        $unitCollection = new UnitCollection();
        $unitCollection->add($unit);
        $command = new Command($unitCollection);

        self::assertNull($command->getUnitForHeal());
    }

    /**
     * Тест на выбор самого раненого юнита из команды для лечения
     *
     * @dataProvider unitDataProvider
     * @param array $data
     * @param string $unitIdForHeal
     * @throws Exception
     */
    public function testGetMostWoundedUnitForHeal(array $data, string $unitIdForHeal): void
    {
        $command = CommandFactory::create($data);
        self::assertEquals($unitIdForHeal, $command->getUnitForHeal()->getId());
    }

    // ------------------------------ Тест на метод getUnitForResurrection() -------------------------------------------
    // Проверены следующие ситуации:
    // 1. Один живой юнит => null
    // 2. Живой + мертвый юнит => unit

    /**
     * 1. Один живой юнит в команде - получаем null
     *
     * @throws Exception
     */
    public function testCommandGetUnitForResurrectionNull(): void
    {
        $unit = UnitFactory::createByTemplate(1);

        $command = CommandFactory::create([$unit]);

        self::assertNull($command->getUnitForResurrection());
    }

    /**
     * 2. Живой + мертвый юнит => получаем мертвого юнита
     *
     * @throws Exception
     */
    public function testCommandGetUnitForResurrectionSuccess(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $deadUnit = UnitFactory::createByTemplate(10);

        $command = CommandFactory::create([$unit, $deadUnit]);

        self::assertEquals($deadUnit, $command->getUnitForResurrection());
    }

    /**
     * @throws Exception
     */
    public function testCommandGetAllAliveUnits(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $deadUnit = UnitFactory::createByTemplate(10);

        $command = CommandFactory::create([$unit, $deadUnit]);

        $expected = new UnitCollection();
        $expected->add($unit);

        self::assertEquals($expected, $command->getAllAliveUnits());
    }

    /**
     * @throws Exception
     */
    public function testCommandGetAllWoundedUnits(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $deadUnit = UnitFactory::createByTemplate(10);
        $woundedUnit = UnitFactory::createByTemplate(11);

        $command = CommandFactory::create([$unit, $deadUnit, $woundedUnit]);

        $expected = new UnitCollection();
        $expected->add($woundedUnit);

        self::assertEquals($expected, $command->getAllWoundedUnits());
    }

    /**
     * Тест на получение всех живых юнитов не имеющих указанного эффекта
     *
     * @throws Exception
     */
    public function testCommandGetUnitsForEffect(): void
    {
        $unit = UnitFactory::createByTemplate(2);
        $otherUnit = UnitFactory::createByTemplate(3);
        $deadUnit = UnitFactory::createByTemplate(10);
        $enemyUnit = UnitFactory::createByTemplate(1);
        $command = CommandFactory::create([$unit, $otherUnit, $deadUnit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $effect = $this->createEffect($unit, $command, $enemyCommand);

        $action = $this->createEffectAction($effect, $unit, $command, $enemyCommand, ActionInterface::TARGET_EFFECT_ALLIES);

        // Вначале мы получаем двух юнитов не имеющих эффекта
        self::assertCount(2, $command->getUnitsForEffect($effect));

        self::assertTrue($action->canByUsed());
        $action->handle();

        // А после применения - одного
        self::assertCount(1, $command->getUnitsForEffect($effect));

        self::assertTrue($action->canByUsed());
        $action->handle();

        // Применив еще раз - целей для эффекта не осталось
        self::assertCount(0, $command->getUnitsForEffect($effect));
    }

    /**
     * @return array
     */
    public function unitDataProvider(): array
    {
        return [
            [
                [
                    [
                        'id'         => '63ad76c6-6a11-44ef-997b-fea1778bebe5',
                        'name'       => 'Warrior',
                        'level'      => 1,
                        'avatar'     => 'url avatar 1',
                        'life'       => 70,
                        'total_life' => 80,
                        'mana'       => 50,
                        'total_mana' => 50,
                        'melee'      => true,
                        'class'      => 1,
                        'race'       => 1,
                        'command'    => 1,
                        'offense'    => [
                            'type_damage'     => 1,
                            'damage'          => 15,
                            'physical_damage' => 0,
                            'attack_speed'    => 1.2,
                            'accuracy'        => 200,
                            'magic_accuracy'  => 100,
                            'block_ignore'    => 0,
                        ],
                        'defense'    => [
                            'physical_resist' => 0,
                            'defense'         => 100,
                            'magic_defense'   => 50,
                            'block'           => 0,
                            'magic_block'     => 0,
                            'mental_barrier'  => 0,
                        ],
                    ],
                    // Самый битый юнит - именно он должен выбираться для лечения
                    [
                        'id'         => 'fb8be211-0782-4c60-8865-68b177ffbe0c',
                        'name'       => 'Knight',
                        'level'      => 1,
                        'avatar'     => 'url avatar 2',
                        'life'       => 12,
                        'total_life' => 115,
                        'mana'       => 50,
                        'total_mana' => 50,
                        'melee'      => false,
                        'class'      => 1,
                        'race'       => 1,
                        'command'    => 1,
                        'offense'    => [
                            'type_damage'     => 1,
                            'damage'          => 15,
                            'physical_damage' => 0,
                            'attack_speed'    => 1.2,
                            'accuracy'        => 200,
                            'magic_accuracy'  => 100,
                            'block_ignore'    => 0,
                        ],
                        'defense'    => [
                            'physical_resist' => 0,
                            'defense'         => 100,
                            'magic_defense'   => 50,
                            'block'           => 0,
                            'magic_block'     => 0,
                            'mental_barrier'  => 0,
                        ],
                    ],
                    [
                        'id'         => 'fb8be211-0782-4c60-8865-68b177ffbedc',
                        'name'       => 'Archer',
                        'level'      => 1,
                        'avatar'     => 'url avatar 3',
                        'life'       => 50,
                        'total_life' => 75,
                        'mana'       => 50,
                        'total_mana' => 50,
                        'melee'      => false,
                        'class'      => 2,
                        'race'       => 1,
                        'command'    => 1,
                        'offense'    => [
                            'type_damage'     => 1,
                            'damage'          => 15,
                            'physical_damage' => 0,
                            'attack_speed'    => 1.2,
                            'accuracy'        => 200,
                            'magic_accuracy'  => 100,
                            'block_ignore'    => 0,
                        ],
                        'defense'    => [
                            'physical_resist' => 0,
                            'defense'         => 100,
                            'magic_defense'   => 50,
                            'block'           => 0,
                            'magic_block'     => 0,
                            'mental_barrier'  => 0,
                        ],
                    ],
                    // Мертвый юнит - он выбираться не должен
                    [
                        'id'         => 'fb8be211-0782-4c60-8865-68b177ffbe3c',
                        'name'       => 'Priest',
                        'level'      => 1,
                        'avatar'     => 'url avatar 3',
                        'life'       => 0,
                        'total_life' => 60,
                        'mana'       => 50,
                        'total_mana' => 50,
                        'melee'      => false,
                        'class'      => 2,
                        'race'       => 1,
                        'command'    => 1,
                        'offense'    => [
                            'type_damage'     => 1,
                            'damage'          => 15,
                            'physical_damage' => 0,
                            'attack_speed'    => 1.2,
                            'accuracy'        => 200,
                            'magic_accuracy'  => 100,
                            'block_ignore'    => 0,
                        ],
                        'defense'    => [
                            'physical_resist' => 0,
                            'defense'         => 100,
                            'magic_defense'   => 50,
                            'block'           => 0,
                            'magic_block'     => 0,
                            'mental_barrier'  => 0,
                        ],
                    ],
                ],
                'fb8be211-0782-4c60-8865-68b177ffbe0c',
            ],
        ];
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
                    'offense'    => [
                        'type_damage'     => 2,
                        'damage'          => 15,
                        'physical_damage' => 15,
                        'attack_speed'    => 1,
                        'accuracy'        => 500,
                        'magic_accuracy'  => 500,
                        'block_ignore'    => 0,
                    ],
                    'can_be_avoided'   => false,
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
     * @param int $typeTarget
     * @return ActionInterface
     */
    private function createEffectAction(
        EffectInterface $effect,
        UnitInterface $unit,
        CommandInterface $command,
        CommandInterface $enemyCommand,
        int $typeTarget = ActionInterface::TARGET_SELF
    ): ActionInterface
    {
        return new EffectAction(
            $unit,
            $enemyCommand,
            $command,
            $typeTarget,
            'effect',
            'icon.png',
            $effect
        );
    }
}
