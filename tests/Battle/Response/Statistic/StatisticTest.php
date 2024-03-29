<?php

declare(strict_types=1);

namespace Tests\Battle\Response\Statistic;

use Battle\Action\ActionInterface;
use Battle\Action\HealAction;
use Battle\Action\ResurrectionAction;
use Battle\Command\CommandInterface;
use Battle\Unit\Offense\OffenseFactory;
use Battle\Unit\UnitInterface;
use Battle\Weapon\Type\WeaponTypeInterface;
use Exception;
use Battle\Action\DamageAction;
use Battle\Action\SummonAction;
use Battle\Command\CommandFactory;
use Battle\Response\Statistic\Statistic;
use Tests\AbstractUnitTest;
use Tests\Factory\UnitFactory;

class StatisticTest extends AbstractUnitTest
{
    public function testStatisticsRoundNumber(): void
    {
        $statistics = new Statistic();

        $statistics->increasedRound();
        $statistics->increasedRound();
        $statistics->increasedRound();

        self::assertEquals(4, $statistics->getRoundNumber());
    }

    public function testStatisticsStrokeNumber(): void
    {
        $statistics = new Statistic();

        $statistics->increasedStroke();
        $statistics->increasedStroke();
        $statistics->increasedStroke();

        self::assertEquals(4, $statistics->getStrokeNumber());
    }

    public function testStatisticsRoundAndStrokeNumber(): void
    {
        $statistics = new Statistic();

        $statistics->increasedRound();
        $statistics->increasedStroke();
        $statistics->increasedRound();
        $statistics->increasedStroke();
        $statistics->increasedRound();

        self::assertEquals(4, $statistics->getRoundNumber());
        self::assertEquals(3, $statistics->getStrokeNumber());
    }

    /**
     * @throws Exception
     */
    public function testStatisticsUnitCausedDamage(): void
    {
        $statistics = $this->container->getStatistic();

        $attackUnit = UnitFactory::createByTemplate(1, $this->container);
        $defendUnit = UnitFactory::createByTemplate(2, $this->container);
        $enemyCommand = CommandFactory::create([$defendUnit]);
        $alliesCommand = CommandFactory::create([$attackUnit]);

        $actionCollection = $attackUnit->getActions($enemyCommand, $alliesCommand);

        foreach ($actionCollection as $action) {
            $action->handle();
            $statistics->addUnitAction($action);
            self::assertEquals(20, $statistics->getUnitsStatistics()->get($attackUnit->getId())->getCausedDamage());
        }

        // Делаем 10 ударов
        for ($i = 0; $i < 10; $i++) {
            $actionCollection = $attackUnit->getActions($enemyCommand, $alliesCommand);

            foreach ($actionCollection as $action) {

                if (!$enemyCommand->isAlive()) {
                    break;
                }

                $action->handle();
                $statistics->addUnitAction($action);
            }
        }

        $defendUnitData = UnitFactory::getData(2);

        self::assertEquals($defendUnitData['total_life'], $statistics->getUnitsStatistics()->get($attackUnit->getId())->getCausedDamage());

        // Также проверяем, что было 0 критических ударов
        self::assertEquals(0, $statistics->getUnitsStatistics()->get($attackUnit->getId())->getCriticalHits());
    }

    /**
     * Тест на подсчет полученного урона от Action с несколькими целями
     *
     * @throws Exception
     */
    public function testStatisticsUnitCausedMultipleDamage(): void
    {
        $statistics = new Statistic();

        $unit = UnitFactory::createByTemplate(1);
        $firstEnemyUnit = UnitFactory::createByTemplate(2);
        $secondaryEnemyUnit = UnitFactory::createByTemplate(3);
        $thirdEnemyUnit = UnitFactory::createByTemplate(4);

        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$firstEnemyUnit, $secondaryEnemyUnit, $thirdEnemyUnit]);

        $action = $this->createDamageAction($unit, $enemyCommand, $command);

        $action->handle();

        $statistics->addUnitAction($action);

        // Проверяем, что атакующий юнит нанес тройной урон (урон * 3 цели)
        self::assertEquals(60, $statistics->getUnitsStatistics()->get($unit->getId())->getCausedDamage());

        // Проверяем, что всем врагам добавлен полученный урон
        self::assertEquals($unit->getOffense()->getDamage($firstEnemyUnit->getDefense()), $statistics->getUnitsStatistics()->get($firstEnemyUnit->getId())->getTakenDamage());
        self::assertEquals($unit->getOffense()->getDamage($secondaryEnemyUnit->getDefense()), $statistics->getUnitsStatistics()->get($secondaryEnemyUnit->getId())->getTakenDamage());
        self::assertEquals($unit->getOffense()->getDamage($thirdEnemyUnit->getDefense()), $statistics->getUnitsStatistics()->get($thirdEnemyUnit->getId())->getTakenDamage());
    }

    /**
     * Тест на подсчет заблокированных ударов
     *
     * @throws Exception
     */
    public function testStatisticsCausedBlockedHits(): void
    {
        $statistics = new Statistic();

        $unit = UnitFactory::createByTemplate(1);
        $firstEnemyUnit = UnitFactory::createByTemplate(2);
        // Юнит со 100% блоком
        $secondaryEnemyUnit = UnitFactory::createByTemplate(28);

        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$firstEnemyUnit, $secondaryEnemyUnit]);

        $action = $this->createDamageAction($unit, $enemyCommand, $command);

        $action->handle();

        $statistics->addUnitAction($action);

        // Проверяем, что статистика по блокам обновилась
        self::assertEquals(0, $statistics->getUnitsStatistics()->get($firstEnemyUnit->getId())->getBlockedHits());
        self::assertEquals(1, $statistics->getUnitsStatistics()->get($secondaryEnemyUnit->getId())->getBlockedHits());

        // И делаем удар еще раз
        $action = $this->createDamageAction($unit, $enemyCommand, $command);

        $action->handle();

        $statistics->addUnitAction($action);

        // И еще раз проверяем статистику по блокам
        self::assertEquals(0, $statistics->getUnitsStatistics()->get($firstEnemyUnit->getId())->getBlockedHits());
        self::assertEquals(2, $statistics->getUnitsStatistics()->get($secondaryEnemyUnit->getId())->getBlockedHits());
    }

    /**
     * Тест на подсчет удары от которых юнит уклонился
     *
     * @throws Exception
     */
    public function testStatisticsCausedDodgedHits(): void
    {
        $statistics = new Statistic();

        $unit = UnitFactory::createByTemplate(1);
        $firstEnemyUnit = UnitFactory::createByTemplate(2);
        // Юнит с высоким уклонением
        $secondaryEnemyUnit = UnitFactory::createByTemplate(30);

        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$firstEnemyUnit, $secondaryEnemyUnit]);

        $action = $this->createDamageAction($unit, $enemyCommand, $command);

        $action->handle();

        $statistics->addUnitAction($action);

        // Проверяем, что статистика по уклонениям обновилась
        self::assertEquals(0, $statistics->getUnitsStatistics()->get($firstEnemyUnit->getId())->getDodgedHits());
        self::assertEquals(1, $statistics->getUnitsStatistics()->get($secondaryEnemyUnit->getId())->getDodgedHits());

        // И делаем удар еще раз
        $action = $this->createDamageAction($unit, $enemyCommand, $command);

        $action->handle();

        $statistics->addUnitAction($action);

        // И еще раз проверяем статистику по уклонениям
        self::assertEquals(0, $statistics->getUnitsStatistics()->get($firstEnemyUnit->getId())->getDodgedHits());
        self::assertEquals(2, $statistics->getUnitsStatistics()->get($secondaryEnemyUnit->getId())->getDodgedHits());
    }

    /**
     * Тест на подсчет полученного урона от EffectAction, а именно то, что нанесенный урон засчитывается юниту который
     * создал эффект, а не тому, на ком эффект находится
     *
     * @throws Exception
     */
    public function testStatisticsUnitCausedEffectDamage(): void
    {
        $statistics = new Statistic();

        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = $this->createEffectDamageAction($unit, $command, $enemyCommand);

        // Проверяем, что вначале эффекта на юните нет
        self::assertCount(0, $enemyUnit->getEffects());
        // И его здоровье полное
        self::assertEquals($enemyUnit->getTotalLife(), $enemyUnit->getLife());

        self::assertTrue($action->canByUsed());
        $action->handle();

        // Проверяем, что эффект появился
        self::assertCount(1, $enemyUnit->getEffects());

        // Наносим урон юниту от эффекта и считаем статистику от эффекта
        foreach ($enemyUnit->getBeforeActions() as $effectAction) {
            $effectAction->handle();
            $statistics->addUnitAction($effectAction);
        }

        // Проверяем, что урон получен
        self::assertEquals($enemyUnit->getTotalLife() - 8, $enemyUnit->getLife());

        // Проверяем, что урон засчитался юниту, который создал эффект
        self::assertEquals(8, $statistics->getUnitsStatistics()->get($unit->getId())->getCausedDamage());
    }

    /**
     * @throws Exception
     */
    public function testStatisticsUnitCausedHeal(): void
    {
        $statistics = new Statistic();

        $woundedUnit = UnitFactory::createByTemplate(11);
        $priest = UnitFactory::createByTemplate(5);
        $enemy = UnitFactory::createByTemplate(1);

        $alliesCommand = CommandFactory::create([$priest, $woundedUnit]);
        $enemyCommand = CommandFactory::create([$enemy]);

        // Применяем лечение
        for ($i = 0; $i < 10; $i++) {
            $priest->newRound();
        }

        $actionCollection = $priest->getActions($enemyCommand, $alliesCommand);

        foreach ($actionCollection as $action) {
            $action->handle();
            $statistics->addUnitAction($action);
        }

        self::assertEquals($action->getPower(), $statistics->getUnitsStatistics()->get($priest->getId())->getHeal());

        // И еще раз
        for ($i = 0; $i < 10; $i++) {
            $priest->newRound();
        }

        $actionCollection = $priest->getActions($enemyCommand, $alliesCommand);

        foreach ($actionCollection as $action) {
            $action->handle();
            $statistics->addUnitAction($action);
        }

        self::assertEquals(120, $statistics->getUnitsStatistics()->get($priest->getId())->getHeal());
    }

    /**
     * Тест на подсчет лечения от EffectAction, а именно то, что лечение засчитывается юниту который создал эффект, а не
     * тому, на ком эффект находится
     *
     * @throws Exception
     */
    public function testStatisticsUnitCausedEffectHeal(): void
    {
        $statistics = new Statistic();

        $unit = UnitFactory::createByTemplate(1);
        $woundedUnit = UnitFactory::createByTemplate(11);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit, $woundedUnit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = $this->createEffectHealAction($unit, $command, $enemyCommand);

        // Проверяем, что вначале эффекта на юните нет
        self::assertCount(0, $woundedUnit->getEffects());
        self::assertEquals(1, $woundedUnit->getLife());

        self::assertTrue($action->canByUsed());
        $action->handle();

        // Проверяем, что эффект появился
        self::assertCount(1, $woundedUnit->getEffects());

        // Лечим юнита от эффекта и считаем статистику от эффекта
        foreach ($woundedUnit->getBeforeActions() as $effectAction) {
            $effectAction->handle();
            $statistics->addUnitAction($effectAction);
        }

        // Проверяем, что лечение произошло
        self::assertEquals(16, $woundedUnit->getLife());

        // Проверяем, что лечение засчиталось юниту, который создал эффект
        self::assertEquals(15, $statistics->getUnitsStatistics()->get($unit->getId())->getHeal());
    }

    /**
     * @throws Exception
     */
    public function testStatisticsUnitCausedResurrected(): void
    {
        $statistics = new Statistic();

        $unit = UnitFactory::createByTemplate(5);
        $deadUnit = UnitFactory::createByTemplate(10);
        $enemyUnit = UnitFactory::createByTemplate(1);

        $command = CommandFactory::create([$unit, $deadUnit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = new ResurrectionAction(
            $this->container,
            $unit,
            $enemyCommand,
            $command,
            ResurrectionAction::TARGET_DEAD_ALLIES,
            50,
            ResurrectionAction::DEFAULT_NAME,
            '',
            ResurrectionAction::DEFAULT_MESSAGE_METHOD
        );

        self::assertTrue($action->canByUsed());
        $action->handle();

        $statistics->addUnitAction($action);

        foreach ($statistics->getUnitsStatistics() as $unitStatistic) {
            self::assertEquals(50, $unitStatistic->getHeal());
            self::assertEquals(1, $unitStatistic->getResurrections());
        }
    }

    /**
     * @throws Exception
     */
    public function testStatisticsUnitCountingSummons(): void
    {
        $statistics = new Statistic();

        $darkMage = UnitFactory::createByTemplate(7);
        $enemy = UnitFactory::createByTemplate(1);

        $command = CommandFactory::create([$darkMage]);
        $enemyCommand = CommandFactory::create([$enemy]);

        // Max concentration
        for ($i = 0; $i < 10; $i++) {
            $darkMage->newRound();
        }

        $actions = $darkMage->getActions($enemyCommand, $command);

        foreach ($actions as $action) {
            self::assertInstanceOf(SummonAction::class, $action);
            $action->handle();
            $statistics->addUnitAction($action);
        }

        self::assertEquals(1, $statistics->getUnitsStatistics()->get($darkMage->getId())->getSummons());
        self::assertCount(2, $command->getUnits());

        // Again
        for ($i = 0; $i < 10; $i++) {
            $darkMage->newRound();
        }

        $actions = $darkMage->getActions($enemyCommand, $command);

        foreach ($actions as $action) {
            self::assertInstanceOf(SummonAction::class, $action);
            $action->handle();
            $statistics->addUnitAction($action);
        }

        self::assertEquals(2, $statistics->getUnitsStatistics()->get($darkMage->getId())->getSummons());
        self::assertCount(3, $command->getUnits());
    }

    /**
     * Тест на время обработки боя
     */
    public function testStatisticsRuntime(): void
    {
        $statistic = new Statistic();

        // 5 миллисекунд
        usleep(5000);

        self::assertIsFloat($statistic->getRuntime());

        // Время выполнения больше 5 миллисекунд и меньше 6 (реально уходит 5.10 - 5.30 миллисекунд)
        self::assertTrue($statistic->getRuntime() > 5 && $statistic->getRuntime() < 8);
    }

    /**
     * Тест на затраченную память
     *
     * @throws Exception
     */
    public function testStatisticsMemoryCostBase(): void
    {
        $statistic = new Statistic();

        random_bytes(1000000);

        // Расход памяти будет разным в зависимости от контекста выполнения теста
        self::assertIsInt($statistic->getMemoryCost());
        self::assertIsString($statistic->getMemoryCostClipped());
    }

    /**
     * Тест на подсчет убийств
     *
     * @throws Exception
     */
    public function testStatisticsKills(): void
    {
        $statistics = new Statistic();
        $unit = UnitFactory::createByTemplate(12);
        $enemyUnit = UnitFactory::createByTemplate(1);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = $this->createDamageAction($unit, $enemyCommand, $command);

        $action->handle();
        $statistics->addUnitAction($action);

        self::assertFalse($enemyUnit->isAlive());
        self::assertEquals(1, $statistics->getUnitsStatistics()->get($unit->getId())->getKilling());
    }

    /**
     * Подсчет полученного урона от Action с несколькими целями
     *
     * @throws Exception
     */
    public function testStatisticsMultipleKills(): void
    {
        $statistics = new Statistic();

        $unit = UnitFactory::createByTemplate(1);
        $firstEnemyUnit = UnitFactory::createByTemplate(2);
        $secondaryEnemyUnit = UnitFactory::createByTemplate(3);
        $thirdEnemyUnit = UnitFactory::createByTemplate(4);

        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$firstEnemyUnit, $secondaryEnemyUnit, $thirdEnemyUnit]);

        $action = new DamageAction(
            $this->container,
            $unit,
            $enemyCommand,
            $command,
            DamageAction::TARGET_ALL_ENEMY,
            true,
            DamageAction::DEFAULT_NAME,
            DamageAction::UNIT_ANIMATION_METHOD,
            DamageAction::DEFAULT_MESSAGE_METHOD,
            OffenseFactory::create([
                'damage_type'         => 1,
                'weapon_type'         => WeaponTypeInterface::SWORD,
                'physical_damage'     => 1000,
                'fire_damage'         => 0,
                'water_damage'        => 0,
                'air_damage'          => 0,
                'earth_damage'        => 0,
                'life_damage'         => 0,
                'death_damage'        => 0,
                'attack_speed'        => 1,
                'cast_speed'          => 0,
                'accuracy'            => 10000,
                'magic_accuracy'      => 10000,
                'block_ignoring'      => 0,
                'critical_chance'     => 5,
                'critical_multiplier' => 200,
                'damage_multiplier'   => 100,
                'vampirism'           => 0,
                'magic_vampirism'     => 0,
            ], $this->container),
        );

        $action->handle();

        $statistics->addUnitAction($action);

        // Проверяем, что атакующий убил 3 юнита
        self::assertEquals(3, $statistics->getUnitsStatistics()->get($unit->getId())->getKilling());
    }

    /**
     * Тест на подсчет критических ударов
     *
     * @throws Exception
     */
    public function testStatisticsUnitCriticalHits(): void
    {
        $statistics = $this->container->getStatistic();

        $attackUnit = UnitFactory::createByTemplate(40, $this->container);
        $defendUnit = UnitFactory::createByTemplate(2, $this->container);
        $enemyCommand = CommandFactory::create([$defendUnit]);
        $alliesCommand = CommandFactory::create([$attackUnit]);

        // Делаем 3 удара
        $hits = 3;
        for ($i = 0; $i < $hits; $i++) {
            $actionCollection = $attackUnit->getActions($enemyCommand, $alliesCommand);

            foreach ($actionCollection as $action) {

                if (!$enemyCommand->isAlive()) {
                    break;
                }

                $action->handle();
                $statistics->addUnitAction($action);
            }
        }

        // И все 3 удара были критическими (т.к. в тестовом режиме шанс критического удара округляется в большую сторону)
        self::assertEquals($hits, $statistics->getUnitsStatistics()->get($attackUnit->getId())->getCriticalHits());
    }

    /**
     * @param UnitInterface $unit
     * @param CommandInterface $command
     * @param CommandInterface $enemyCommand
     * @return ActionInterface
     * @throws Exception
     */
    private function createEffectDamageAction(
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
            'type_target'    => ActionInterface::TARGET_EFFECT_ENEMY,
            'name'           => 'Poison',
            'icon'           => '/images/icons/ability/202.png',
            'message_method' => 'applyEffect',
            'effect'         => [
                'name'                  => 'Poison',
                'icon'                  => '/images/icons/ability/202.png',
                'duration'              => 5,
                'on_apply_actions'      => [],
                'on_next_round_actions' => [
                    [
                        'type'             => ActionInterface::DAMAGE,
                        'action_unit'      => $unit,
                        'enemy_command'    => $enemyCommand,
                        'allies_command'   => $command,
                        'type_target'      => ActionInterface::TARGET_SELF,
                        'name'             => 'Poison',
                        'offense'          => [
                            'damage_type'         => 2,
                            'weapon_type'         => WeaponTypeInterface::NONE,
                            'physical_damage'     => 8,
                            'fire_damage'         => 0,
                            'water_damage'        => 0,
                            'air_damage'          => 0,
                            'earth_damage'        => 0,
                            'life_damage'         => 0,
                            'death_damage'        => 0,
                            'attack_speed'        => 0,
                            'cast_speed'          => 1,
                            'accuracy'            => 500,
                            'magic_accuracy'      => 500,
                            'block_ignoring'      => 0,
                            'critical_chance'     => 0,
                            'critical_multiplier' => 0,
                            'damage_multiplier'   => 100,
                            'vampirism'           => 0,
                            'magic_vampirism'     => 0,
                        ],
                        'can_be_avoided'   => false,
                        'animation_method' => DamageAction::EFFECT_ANIMATION_METHOD,
                        'message_method'   => DamageAction::EFFECT_MESSAGE_METHOD,
                        'icon'             => '/images/icons/ability/202.png',
                    ],
                ],
                'on_disable_actions'    => [],
            ],
        ];

        return $this->container->getActionFactory()->create($data);
    }

    /**
     * @param UnitInterface $unit
     * @param CommandInterface $command
     * @param CommandInterface $enemyCommand
     * @return ActionInterface
     * @throws Exception
     */
    private function createEffectHealAction(
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
            'type_target'    => ActionInterface::TARGET_WOUNDED_ALLIES,
            'name'           => 'Healing Potion',
            'icon'           => '/images/icons/ability/234.png',
            'message_method' => 'applyEffect',
            'effect'         => [
                'name'                  => 'Healing Potion',
                'icon'                  => '/images/icons/ability/234.png',
                'duration'              => 4,
                'on_apply_actions'      => [],
                'on_next_round_actions' => [
                    [
                        'type'             => ActionInterface::HEAL,
                        'action_unit'      => $unit,
                        'enemy_command'    => $enemyCommand,
                        'allies_command'   => $command,
                        'type_target'      => ActionInterface::TARGET_SELF,
                        'name'             => 'Healing Potion',
                        'power'            => 15,
                        'animation_method' => HealAction::EFFECT_ANIMATION_METHOD,
                        'message_method'   => HealAction::EFFECT_MESSAGE_METHOD,
                        'icon'             => '/images/icons/ability/234.png',
                    ],
                ],
                'on_disable_actions'    => [],
            ],
        ];

        return $this->container->getActionFactory()->create($data);
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
            DamageAction::TARGET_ALL_ENEMY,
            true,
            DamageAction::DEFAULT_NAME,
            DamageAction::UNIT_ANIMATION_METHOD,
            DamageAction::DEFAULT_MESSAGE_METHOD,
            $unit->getOffense()
        );
    }
}
