<?php

declare(strict_types=1);

namespace Tests\Battle\Response\Scenario;

use Battle\Action\ActionInterface;
use Battle\Action\BuffAction;
use Battle\Action\EffectAction;
use Battle\Action\HealAction;
use Battle\Action\ManaRestoreAction;
use Battle\Response\Scenario\ScenarioException;
use Battle\Unit\Ability\AbilityFactory;
use Exception;
use Battle\Action\SummonAction;
use Battle\Command\CommandInterface;
use Battle\Unit\UnitInterface;
use Battle\Action\DamageAction;
use Battle\Action\WaitAction;
use Battle\Command\CommandFactory;
use Battle\Response\Scenario\Scenario;
use Battle\Response\Statistic\Statistic;
use Tests\AbstractUnitTest;
use Tests\Factory\BaseFactory;
use Tests\Factory\UnitFactory;

class ScenarioTest extends AbstractUnitTest
{
    /**
     * Тест на создание анимации урона по юниту (без ментального барьера)
     *
     * За основу берется DamageActionTest::testApplyDamageAction()
     *
     * И дополняется созданием и проверкой сценария
     *
     * @throws Exception
     */
    public function testScenarioAddDamageByLife(): void
    {
        $statistic = new Statistic();
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = $this->createDamageAction($unit, $enemyCommand, $command, DamageAction::TARGET_RANDOM_ENEMY);

        $action->handle();

        $scenario = new Scenario();
        $scenario->addAnimation($action, $statistic);

        $expectedData = [
            'step'    => $statistic->getRoundNumber(),
            'attack'  => $statistic->getStrokeNumber(),
            'effects' => [
                [
                    'user_id'        => $unit->getId(),
                    'class'          => 'd_attack',
                    'unit_cons_bar2' => 0,
                    'unit_rage_bar2' => 0,
                    'unit_effects'   => [],
                    'targets'        => [
                        [
                            'type'              => 'change',
                            'user_id'           => $enemyUnit->getId(),
                            'hp'                => $enemyUnit->getTotalLife() - $unit->getOffense()->getDamage($enemyUnit->getDefense()),
                            'thp'               => $enemyUnit->getTotalLife(),
                            'hp_bar_class'      => 'unit_hp_bar',
                            'hp_bar_class2'     => 'unit_hp_bar2',
                            'recdam'            => '-20',
                            'unit_hp_bar_width' => 92,
                            'unit_cons_bar2'    => 10,
                            'unit_rage_bar2'    => 7,
                            'ava'               => 'unit_ava_red',
                            'avas'              => 'unit_ava_blank',
                            'unit_effects'      => [],
                        ],
                    ],
                ],
            ],
        ];

        self::assertEquals($expectedData, $scenario->getArray()[0]);
    }

    /**
     * Тест на урон + лечение от вампиризма
     *
     * @throws Exception
     */
    public function testScenarioAddDamageAndVampirism(): void
    {
        $statistic = new Statistic();
        $unit = UnitFactory::createByTemplate(42);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command =CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = $this->createDamageAction($unit, $enemyCommand, $command, DamageAction::TARGET_RANDOM_ENEMY);

        $action->handle();

        $scenario = new Scenario();
        $scenario->addAnimation($action, $statistic);

        $expectedData = [
            'step'    => $statistic->getRoundNumber(),
            'attack'  => $statistic->getStrokeNumber(),
            'effects' => [
                [
                    'user_id'        => $unit->getId(),
                    'class'          => 'd_attack',
                    'unit_cons_bar2' => 10,
                    'unit_rage_bar2' => 7,
                    'unit_effects'   => [],
                    'targets'        => [
                        [
                            'type'              => 'change',
                            'user_id'           => $enemyUnit->getId(),
                            'hp'                => $enemyUnit->getTotalLife() - $unit->getOffense()->getDamage($enemyUnit->getDefense()),
                            'thp'               => $enemyUnit->getTotalLife(),
                            'hp_bar_class'      => 'unit_hp_bar',
                            'hp_bar_class2'     => 'unit_hp_bar2',
                            'recdam'            => '-50',
                            'unit_hp_bar_width' => 80,
                            'unit_cons_bar2'    => 10,
                            'unit_rage_bar2'    => 7,
                            'ava'               => 'unit_ava_red',
                            'avas'              => 'unit_ava_blank',
                            'unit_effects'      => [],
                        ],
                        [
                            'type'              => 'change',
                            'user_id'           => '91cc27a4-0120-4d6b-9ad2-fca05c8aa3d7',
                            'ava'               => 'unit_ava_green',
                            'recdam'            => '+25',
                            'hp'                => 75,
                            'thp'               => 100,
                            'unit_hp_bar_width' => 75,
                        ],
                    ],
                ],
            ],
        ];

        self::assertEquals($expectedData, $scenario->getArray()[0]);
    }

    /**
     * Тест на создание анимации урона по юниту с ментальным барьером и маной
     *
     * @throws Exception
     */
    public function testScenarioAddDamageByMana(): void
    {
        $statistic = new Statistic();
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(32);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = $this->createDamageAction($unit, $enemyCommand, $command, DamageAction::TARGET_RANDOM_ENEMY);

        $action->handle();

        $scenario = new Scenario();
        $scenario->addAnimation($action, $statistic);

        $expectedData = [
            'step'    => $statistic->getRoundNumber(),
            'attack'  => $statistic->getStrokeNumber(),
            'effects' => [
                [
                    'user_id'        => $unit->getId(),
                    'class'          => 'd_attack',
                    'unit_cons_bar2' => 0,
                    'unit_rage_bar2' => 0,
                    'unit_effects'   => [],
                    'targets'        => [
                        [
                            'type'              => 'change',
                            'user_id'           => $enemyUnit->getId(),
                            'hp'                => $enemyUnit->getTotalMana() - $unit->getOffense()->getDamage($enemyUnit->getDefense()),
                            'thp'               => $enemyUnit->getTotalMana(),
                            'hp_bar_class'      => 'unit_hp_bar_mana',
                            'hp_bar_class2'     => 'unit_hp_bar2_mana',
                            'recdam'            => '-20',
                            'unit_hp_bar_width' => 80,
                            'unit_cons_bar2'    => 10,
                            'unit_rage_bar2'    => 7,
                            'ava'               => 'unit_ava_red',
                            'avas'              => 'unit_ava_blank',
                            'unit_effects'      => [],
                        ],
                    ],
                ],
            ],
        ];

        self::assertEquals($expectedData, $scenario->getArray()[0]);
    }

    /**
     * Тест на формирование массива параметров для анимации блока атаки
     *
     * @throws Exception
     */
    public function testScenarioAddBlockedDamage(): void
    {
        $statistic = new Statistic();
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(28);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = $this->createDamageAction($unit, $enemyCommand, $command, DamageAction::TARGET_RANDOM_ENEMY);

        $action->handle();

        $scenario = new Scenario();
        $scenario->addAnimation($action, $statistic);

        $expectedData = [
            'step'    => $statistic->getRoundNumber(),
            'attack'  => $statistic->getStrokeNumber(),
            'effects' => [
                [
                    'user_id'        => $unit->getId(),
                    'class'          => 'd_attack',
                    'unit_cons_bar2' => 0,
                    'unit_rage_bar2' => 0,
                    'unit_effects'   => [],
                    'targets'        => [
                        [
                            'type'         => 'change',
                            'user_id'      => $enemyUnit->getId(),
                            'class'        => 'd_block',
                            'unit_effects' => [],
                        ],
                    ],
                ],
            ],
        ];

        self::assertEquals($expectedData, $scenario->getArray()[0]);
    }

    /**
     * Тест на формирование массива параметров для анимации уклонения от удара
     *
     * @throws Exception
     */
    public function testScenarioAddDodgedDamage(): void
    {
        $statistic = new Statistic();
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(30);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = $this->createDamageAction($unit, $enemyCommand, $command, DamageAction::TARGET_RANDOM_ENEMY);

        $action->handle();

        $scenario = new Scenario();
        $scenario->addAnimation($action, $statistic);

        $expectedData = [
            'step'    => $statistic->getRoundNumber(),
            'attack'  => $statistic->getStrokeNumber(),
            'effects' => [
                [
                    'user_id'        => $unit->getId(),
                    'class'          => 'd_attack',
                    'unit_cons_bar2' => 0,
                    'unit_rage_bar2' => 0,
                    'unit_effects'   => [],
                    'targets'        => [
                        [
                            'type'         => 'change',
                            'user_id'      => $enemyUnit->getId(),
                            'class'        => 'd_evasion_s2',
                            'unit_effects' => [],
                        ],
                    ],
                ],
            ],
        ];

        self::assertEquals($expectedData, $scenario->getArray()[0]);
    }

    /**
     * @throws Exception
     */
    public function testScenarioAddMultipleDamage(): void
    {
        $statistic = new Statistic();
        $unit = UnitFactory::createByTemplate(1);
        $firstEnemyUnit = UnitFactory::createByTemplate(2);
        $secondaryEnemyUnit = UnitFactory::createByTemplate(3);
        $thirdEnemyUnit = UnitFactory::createByTemplate(4);

        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$firstEnemyUnit, $secondaryEnemyUnit, $thirdEnemyUnit]);

        $action = $this->createDamageAction($unit, $enemyCommand, $command, DamageAction::TARGET_ALL_ENEMY);

        self::assertTrue($action->canByUsed());

        $action->handle();

        $scenario = new Scenario();
        $scenario->addAnimation($action, $statistic);

        $expectedData = [
            'step'    => 1,
            'attack'  => 1,
            'effects' => [
                [
                    'user_id'        => 'f7e84eab-e4f6-469f-b0e3-f5f965f9fbce',
                    'class'          => 'd_attack',
                    'unit_cons_bar2' => 0,
                    'unit_rage_bar2' => 0,
                    'unit_effects'   => [],
                    'targets'        => [
                        [
                            'type'              => 'change',
                            'user_id'           => '1aab367d-37e8-4544-9915-cb3d7779308b',
                            'hp'                => 230,
                            'thp'               => 250,
                            'hp_bar_class'      => 'unit_hp_bar',
                            'hp_bar_class2'     => 'unit_hp_bar2',
                            'recdam'            => '-20',
                            'unit_hp_bar_width' => 92,
                            'unit_cons_bar2'    => 10,
                            'unit_rage_bar2'    => 7,
                            'ava'               => 'unit_ava_red',
                            'avas'              => 'unit_ava_blank',
                            'unit_effects'      => [],
                        ],
                        [
                            'type'              => 'change',
                            'user_id'           => '72df87f5-b3a7-4574-9526-45a20aa77119',
                            'hp'                => 100,
                            'thp'               => 120,
                            'hp_bar_class'      => 'unit_hp_bar',
                            'hp_bar_class2'     => 'unit_hp_bar2',
                            'recdam'            => '-20',
                            'unit_hp_bar_width' => 83,
                            'unit_cons_bar2'    => 10,
                            'unit_rage_bar2'    => 7,
                            'ava'               => 'unit_ava_red',
                            'avas'              => 'unit_ava_blank',
                            'unit_effects'      => [],
                        ],
                        [
                            'type'              => 'change',
                            'user_id'           => 'c310ce86-7bb2-44b0-b634-ea0d28fb1180',
                            'hp'                => 0,
                            'thp'               => 20,
                            'hp_bar_class'      => 'unit_hp_bar',
                            'hp_bar_class2'     => 'unit_hp_bar2',
                            'recdam'            => '-20',
                            'unit_hp_bar_width' => 0,
                            'unit_cons_bar2'    => 10,
                            'unit_rage_bar2'    => 7,
                            'ava'               => 'unit_ava_red',
                            'avas'              => 'unit_ava_dead',
                            'unit_effects'      => [],
                        ],
                    ],
                ],
            ],
        ];

        self::assertEquals($expectedData, $scenario->getArray()[0]);
    }

    /**
     * @throws Exception
     */
    public function testScenarioAddEffectDamage(): void
    {
        $abilityFactory = new AbilityFactory();
        [$unit, $command, $enemyCommand, $enemyUnit] = BaseFactory::create(23, 2, $this->container);

        $ability = $abilityFactory->create($unit, $this->container->getAbilityDataProvider()->get('Poison', 1));

        // Применение эффекта
        foreach ($ability->getActions($enemyCommand, $command) as $action) {
            self::assertTrue($action->canByUsed());
            $action->handle();
            $this->container->getScenario()->addAnimation($action, $this->container->getStatistic());
        }

        // Применение эффекта от урона
        foreach ($enemyUnit->getBeforeActions() as $action) {
            if ($action->canByUsed()) {
                $action->handle();
                $this->container->getScenario()->addAnimation($action, $this->container->getStatistic());
            }
        }

        $expectedData = [
            'step'    => $this->container->getStatistic()->getRoundNumber(),
            'attack'  => $this->container->getStatistic()->getStrokeNumber(),
            'effects' => [
                [
                    'user_id'      => $enemyUnit->getId(),
                    'unit_effects' => [
                        [
                            'icon'     => '/images/icons/ability/202.png',
                            'duration' => 5,
                        ],
                    ],
                    'targets'      => [
                        [
                            'type'              => 'change',
                            'user_id'           => $enemyUnit->getId(),
                            'ava'               => 'unit_ava_effect_damage',
                            'recdam'            => '-8',
                            'hp'                => 242,
                            'thp'               => 250,
                            'unit_hp_bar_width' => 96,
                            'avas'              => 'unit_ava_blank',
                            'hp_bar_class'      => 'unit_hp_bar',
                            'hp_bar_class2'     => 'unit_hp_bar2',
                            'unit_cons_bar2'    => 20,
                            'unit_rage_bar2'    => 14,
                            'unit_effects'      => [
                                [
                                    'icon'     => '/images/icons/ability/202.png',
                                    'duration' => '5',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        self::assertEquals($expectedData, $this->container->getScenario()->getArray()[1]);
    }

    /**
     * За основу берется HealActionTest::testHealActionSimple()
     *
     * И дополняется созданием и проверкой сценария
     *
     * @throws Exception
     */
    public function testScenarioAddHeal(): void
    {
        $statistic = new Statistic();
        $scenario = new Scenario();
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
        $actions = $actionUnit->getActions($enemyCommand, $actionCommand);

        foreach ($actions as $action) {
            $action->handle();
            $scenario->addAnimation($action, $statistic);
        }

        // Проверяем лечение
        self::assertEquals(1 + $action->getPower(), $woundedUnit->getLife());

        $expectedData = [
            'step'    => $statistic->getRoundNumber(),
            'attack'  => $statistic->getStrokeNumber(),
            'effects' => [
                [
                    'user_id'        => $actionUnit->getId(),
                    'class'          => 'd_buff',
                    'unit_cons_bar2' => 0,
                    'unit_rage_bar2' => 50,
                    'targets'        => [
                        [
                            'type'              => 'change',
                            'user_id'           => $woundedUnit->getId(),
                            'ava'               => 'unit_ava_green',
                            'recdam'            => '+' . $action->getPower(),
                            'hp'                => 1 + $action->getPower(),
                            'thp'               => $woundedUnit->getTotalLife(),
                            'hp_bar_class'      => 'unit_hp_bar',
                            'hp_bar_class2'     => 'unit_hp_bar2',
                            'unit_hp_bar_width' => 6,
                            'unit_effects'      => [],
                        ],
                    ],
                ],
            ],
        ];

        self::assertEquals($expectedData, $scenario->getArray()[0]);
    }

    /**
     * Тест на формирование сценария при применении лечения сразу по нескольким целям
     *
     * @throws Exception
     */
    public function testScenarioAddMultipleHeal(): void
    {
        $statistic = new Statistic();
        $unit = UnitFactory::createByTemplate(9);
        $woundedUnit = UnitFactory::createByTemplate(11);
        $enemyUnit = UnitFactory::createByTemplate(4);

        $command = CommandFactory::create([$unit, $woundedUnit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = new HealAction(
           $this->container,
            $unit,
            $enemyCommand,
            $command,
            DamageAction::TARGET_ALL_WOUNDED_ALLIES,
            35,
            '',
            HealAction::UNIT_ANIMATION_METHOD,
            HealAction::DEFAULT_MESSAGE_METHOD
        );

        self::assertTrue($action->canByUsed());

        $action->handle();

        $scenario = new Scenario();
        $scenario->addAnimation($action, $statistic);

        $expectedData = [
            'step'    => 1,
            'attack'  => 1,
            'effects' => [
                [
                    'user_id'        => '5e5c15fb-fa29-4bf0-8a0d-f2be9f90ca9d',
                    'class'          => 'd_buff',
                    'unit_cons_bar2' => 10,
                    'unit_rage_bar2' => 7,
                    'targets'        => [
                        [
                            'type'              => 'change',
                            'user_id'           => '5e5c15fb-fa29-4bf0-8a0d-f2be9f90ca9d',
                            'ava'               => 'unit_ava_green',
                            'recdam'            => '+10',
                            'hp'                => 100,
                            'thp'               => 100,
                            'hp_bar_class'      => 'unit_hp_bar',
                            'hp_bar_class2'     => 'unit_hp_bar2',
                            'unit_hp_bar_width' => 100,
                            'unit_effects'      => [],
                        ],
                        [
                            'type'              => 'change',
                            'user_id'           => '92e7b39c-dbfc-4493-b563-50314c524c3c',
                            'ava'               => 'unit_ava_green',
                            'recdam'            => '+35',
                            'hp'                => 36,
                            'thp'               => 1000,
                            'hp_bar_class'      => 'unit_hp_bar',
                            'hp_bar_class2'     => 'unit_hp_bar2',
                            'unit_hp_bar_width' => 3,
                            'unit_effects'      => [],
                        ],
                    ],
                ],
            ],
        ];

        self::assertEquals($expectedData, $scenario->getArray()[0]);
    }

    /**
     * @throws Exception
     */
    public function testScenarioAddEffectHeal(): void
    {
        $abilityFactory = new AbilityFactory();
        [$unit, $command, $enemyCommand] = BaseFactory::create(11, 2, $this->container);

        $ability = $abilityFactory->create($unit, $this->container->getAbilityDataProvider()->get('Healing Potion', 1));

        // Применение эффекта
        foreach ($ability->getActions($enemyCommand, $command) as $action) {
            self::assertTrue($action->canByUsed());
            $action->handle();
            $this->container->getScenario()->addAnimation($action, $this->container->getStatistic());
        }

        // Применение эффекта от лечения
        foreach ($unit->getBeforeActions() as $action) {
            if ($action->canByUsed()) {
                $action->handle();
                $this->container->getScenario()->addAnimation($action, $this->container->getStatistic());
            }
        }

        $expectedData = [
            'step'    => $this->container->getStatistic()->getRoundNumber(),
            'attack'  => $this->container->getStatistic()->getStrokeNumber(),
            'effects' => [
                [
                    'user_id'      => $unit->getId(),
                    'unit_effects' => [
                        [
                            'icon'     => '/images/icons/ability/234.png',
                            'duration' => 4,
                        ],
                    ],
                    'targets'      => [
                        [
                            'type'              => 'change',
                            'user_id'           => $unit->getId(),
                            'ava'               => 'unit_ava_green',
                            'recdam'            => '+15',
                            'hp'                => 16,
                            'thp'               => 1000,
                            'unit_hp_bar_width' => 1,
                        ],
                    ],
                ],
            ],
        ];

        self::assertEquals($expectedData, $this->container->getScenario()->getArray()[1]);
    }

    /**
     * Тест на создание эффекта по восстановлению маны от эффекта
     *
     * @throws Exception
     */
    public function testScenarioAddEffectManaRestore(): void
    {
        $abilityFactory = new AbilityFactory();
        [$unit, $command, $enemyCommand] = BaseFactory::create(33, 2, $this->container);

        $ability = $abilityFactory->create($unit, $this->container->getAbilityDataProvider()->get('Restore Potion', 1));

        // Применение эффекта
        foreach ($ability->getActions($enemyCommand, $command) as $action) {
            self::assertInstanceOf(EffectAction::class, $action);
            self::assertTrue($action->canByUsed());
            $action->handle();
            $this->container->getScenario()->addAnimation($action, $this->container->getStatistic());
        }

        // Применение эффекта от восстановления маны
        $i = 0;
        foreach ($unit->getBeforeActions() as $action) {
            // Первым эффектом будет лечение, его пропускаем
            if ($i === 1) {
                self::assertInstanceOf(ManaRestoreAction::class, $action);
                if ($action->canByUsed()) {
                    $action->handle();
                    $this->container->getScenario()->addAnimation($action, $this->container->getStatistic());
                }
            }
            $i++;
        }

        $expectedData = [
            'step'    => $this->container->getStatistic()->getRoundNumber(),
            'attack'  => $this->container->getStatistic()->getStrokeNumber(),
            'effects' => [
                [
                    'user_id'      => $unit->getId(),
                    'unit_effects' => [
                        [
                            'icon'     => '/images/icons/ability/234.png',
                            'duration' => 5,
                        ],
                    ],
                    'targets'      => [
                        [
                            'type'              => 'change',
                            'user_id'           => $unit->getId(),
                            'ava'               => 'unit_ava_blue',
                            'recdam'            => '+7',
                            'hp'                => 7,
                            'thp'               => 100,
                            'unit_hp_bar_width' => 7,
                        ],
                    ],
                ],
            ],
        ];

        self::assertEquals($expectedData, $this->container->getScenario()->getArray()[1]);
    }

    /**
     * @throws Exception
     */
    public function testScenarioAddSummon(): void
    {
        $statistic = new Statistic();

        $actionUnit = UnitFactory::createByTemplate(7);
        $enemyUnit = UnitFactory::createByTemplate(1);

        $actionCommand = CommandFactory::create([$actionUnit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = $this->getSummonImpAction($actionUnit, $enemyCommand, $actionCommand);

        $scenario = new Scenario();

        $scenario->addAnimation($action, $statistic);

        $expectedData = [
            'step'    => $statistic->getRoundNumber(),
            'attack'  => $statistic->getStrokeNumber(),
            'effects' => [
                [
                    'user_id'        => $action->getActionUnit()->getId(),
                    'class'          => 'd_buff',
                    'unit_cons_bar2' => 0,
                    'unit_rage_bar2' => 0,
                    'targets'        => [
                        [
                            'type'            => 'summon',
                            'summon_row'      => 'left_command_melee',
                            'id'              => $action->getSummonUnit()->getId(),
                            'hp_bar_class'    => 'unit_hp_bar',
                            'hp_bar_class2'   => 'unit_hp_bar2',
                            'hp_bar_width'    => 100,
                            'unit_box2_class' => 'unit_box2_na',
                            'hp'              => 30,
                            'thp'             => 30,
                            'cons_bar_width'  => 0,
                            'rage_bar_width'  => 0,
                            'avatar'          => '/images/avas/monsters/004.png',
                            'name'            => 'Imp',
                            'name_color'      => '#ba4829',
                            'icon'            => '/images/icons/small/base-inferno.png',
                            'level'           => 1,
                            'exist_class'     => false,
                        ],
                    ],
                ],
            ],
        ];

        self::assertEquals($expectedData, $scenario->getArray()[0]);
    }

    /**
     * @throws Exception
     */
    public function testScenarioAddEffectDefault(): void
    {
        $statistic = new Statistic();
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = $this->getReserveForcesAction($unit, $enemyCommand, $command, ActionInterface::TARGET_SELF);

        self::assertTrue($action->canByUsed());

        $action->handle();

        $scenario = new Scenario();
        $scenario->addAnimation($action, $statistic);

        $expectedData = [
            [
                'step'    => $statistic->getRoundNumber(),
                'attack'  => $statistic->getStrokeNumber(),
                'effects' => [
                    [
                        'user_id'        => $unit->getId(),
                        'class'          => 'd_buff',
                        'hp'             => 130,
                        'thp'            => 130,
                        'unit_cons_bar2' => 20,
                        'unit_rage_bar2' => 14,
                        'unit_effects'   => [
                            [
                                'icon'     => '/images/icons/ability/156.png',
                                'duration' => '8',
                            ],
                        ],
                        'targets'        => [
                            [
                                'type'              => 'change',
                                'user_id'           => $action->getActionUnit()->getId(),
                                'hp'                => $action->getActionUnit()->getLife(),
                                'thp'               => $action->getActionUnit()->getTotalLife(),
                                'unit_hp_bar_width' => 100,
                                'unit_effects'      => [
                                    [
                                        'icon'     => '/images/icons/ability/156.png',
                                        'duration' => '8',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        self::assertEquals($expectedData, $scenario->getArray());
    }

    /**
     * Тест на ситуацию, когда эффект накладывается на цель имеющую ману и ментальный барьер
     *
     * (тест на ошибку, когда в момент наложения эффекта отображались цифры здоровья, хотя должны отображаться цифры маны)
     *
     * @throws Exception
     */
    public function testScenarioAddEffectMentalBarrier(): void
    {
        $statistic = new Statistic();
        $scenario = new Scenario();
        $unit = UnitFactory::createByTemplate(8);
        $alliesUnit = UnitFactory::createByTemplate(32);
        $enemyUnit = UnitFactory::createByTemplate(3);
        $command = CommandFactory::create([$unit, $alliesUnit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = $this->container->getAbilityFactory()->create(
            $unit,
            $this->container->getAbilityDataProvider()->get('Restore Potion', 1)
        );

        $unit->newRound();

        self::assertTrue($ability->canByUsed($enemyCommand, $command));
        self::assertCount(1, $ability->getActions($enemyCommand, $command));

        foreach ($ability->getActions($enemyCommand, $command) as $action) {
            self::assertInstanceOf(EffectAction::class, $action);
            self::assertTrue($action->canByUsed());
            $action->handle();

            $scenario->addAnimation($action, $statistic);

            $expectedData = [
                [
                    'step'    => $statistic->getRoundNumber(),
                    'attack'  => $statistic->getStrokeNumber(),
                    'effects' => [
                        [
                            'user_id'        => $unit->getId(),
                            'class'          => 'd_buff',
                            'hp'             => 100,
                            'thp'            => 100,
                            'unit_cons_bar2' => 20,
                            'unit_rage_bar2' => 5,
                            'unit_effects'   => [],
                            'targets'        => [
                                [
                                    'type'              => 'change',
                                    'user_id'           => $alliesUnit->getId(),
                                    'hp'                => $alliesUnit->getMana(),
                                    'thp'               => $alliesUnit->getTotalMana(),
                                    'unit_hp_bar_width' => 100,
                                    'unit_effects'      => [
                                        [
                                            'icon'     => '/images/icons/ability/234.png',
                                            'duration' => '5',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ];

            self::assertEquals($expectedData, $scenario->getArray());
        }
    }

    /**
     * Тест на формирование сценария при применении эффекта сразу всей команде
     *
     * @throws Exception
     */
    public function testScenarioAddMultipleEffect(): void
    {
        $statistic = new Statistic();
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $otherEnemyUnit = UnitFactory::createByTemplate(3);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit, $otherEnemyUnit]);

        $action = $this->getReserveForcesAction($unit, $enemyCommand, $command, ActionInterface::TARGET_ALL_ENEMY);

        self::assertTrue($action->canByUsed());

        $action->handle();

        $scenario = new Scenario();
        $scenario->addAnimation($action, $statistic);

        $expectedData = [
            'step'    => 1,
            'attack'  => 1,
            'effects' =>
                [
                    [
                        'user_id'        => 'f7e84eab-e4f6-469f-b0e3-f5f965f9fbce',
                        'class'          => 'd_buff',
                        'hp'             => 100,
                        'thp'            => 100,
                        'unit_cons_bar2' => 0,
                        'unit_rage_bar2' => 0,
                        'unit_effects'   => [],
                        'targets'        => [
                            [
                                'type'              => 'change',
                                'user_id'           => '1aab367d-37e8-4544-9915-cb3d7779308b',
                                'hp'                => 325,
                                'thp'               => 325,
                                'unit_hp_bar_width' => 100,
                                'unit_effects'      => [
                                    [
                                        'icon'     => '/images/icons/ability/156.png',
                                        'duration' => '8',
                                    ],
                                ],
                            ],
                            [
                                'type'              => 'change',
                                'user_id'           => '72df87f5-b3a7-4574-9526-45a20aa77119',
                                'hp'                => 156,
                                'thp'               => 156,
                                'unit_hp_bar_width' => 100,
                                'unit_effects'      => [
                                    [
                                        'icon'     => '/images/icons/ability/156.png',
                                        'duration' => '8',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
        ];

        self::assertEquals($expectedData, $scenario->getArray()[0]);
    }

    /**
     * @throws Exception
     */
    public function testScenarioAddWait(): void
    {
        $statistic = new Statistic();
        $actionUnit = UnitFactory::createByTemplate(14);
        $enemyUnit = UnitFactory::createByTemplate(3);

        $actionCommand = CommandFactory::create([$actionUnit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = new WaitAction(
           $this->container,
            $actionUnit,
            $enemyCommand,
            $actionCommand
        );

        $scenario = new Scenario();

        $scenario->addAnimation($action, $statistic);

        $expectedData = [
            'step'    => $statistic->getRoundNumber(),
            'attack'  => $statistic->getStrokeNumber(),
            'effects' => [],
        ];

        self::assertEquals($expectedData, $scenario->getArray()[0]);
    }

    /**
     * @throws Exception
     */
    public function testScenarioAddResurrected(): void
    {
        $abilityFactory = new AbilityFactory();
        $statistic = new Statistic();
        $scenario = new Scenario();
        $unit = UnitFactory::createByTemplate(1);
        $deadUnit = UnitFactory::createByTemplate(10);
        $enemyUnit = UnitFactory::createByTemplate(3);

        $command = CommandFactory::create([$unit, $deadUnit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = $abilityFactory->create($unit, $this->container->getAbilityDataProvider()->get('Back to Life', 1));

        $actions = $ability->getActions($enemyCommand, $command);

        foreach ($actions as $action) {
            self::assertTrue($action->canByUsed());
            $action->handle();
            $scenario->addAnimation($action, $statistic);
        }

        $expectedData = [
            'step'    => $statistic->getRoundNumber(),
            'attack'  => $statistic->getStrokeNumber(),
            'effects' => [
                [
                    'user_id'        => $unit->getId(),
                    'class'          => 'd_buff',
                    'unit_cons_bar2' => 0,
                    'unit_rage_bar2' => 0,
                    'targets'        => [
                        [
                            'type'              => 'change',
                            'user_id'           => $deadUnit->getId(),
                            'ava'               => 'unit_ava_green',
                            'recdam'            => '+30',
                            'hp'                => 30,
                            'thp'               => 100,
                            'hp_bar_class'      => 'unit_hp_bar',
                            'hp_bar_class2'     => 'unit_hp_bar2',
                            'unit_hp_bar_width' => 30,
                            'avas'              => 'unit_ava_blank',
                            'unit_effects'      => [],
                        ],
                    ],
                ],
            ],
        ];

        self::assertEquals($expectedData, $scenario->getArray()[0]);
    }

    /**
     * @throws Exception
     */
    public function testScenarioGetJson(): void
    {
        $scenario = new Scenario();
        self::assertEquals('[]', $scenario->getJson());
    }

    /**
     * @throws Exception
     */
    public function testScenarioGetSummonRowLeftCommand(): void
    {
        $actionUnit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(1);

        $actionCommand = CommandFactory::create([$actionUnit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $scenario = new Scenario();

        $action = $this->getSummonImpAction($actionUnit, $enemyCommand, $actionCommand);

        self::assertEquals('left_command_melee', $scenario->getSummonRow($action));

        $action = $this->getSummonSkeletonMageAction($actionUnit, $enemyCommand, $actionCommand);

        self::assertEquals('left_command_range', $scenario->getSummonRow($action));
    }

    /**
     * @throws Exception
     */
    public function testScenarioGetSummonRowRightCommand(): void
    {
        $actionUnit = UnitFactory::createByTemplate(12);
        $enemyUnit = UnitFactory::createByTemplate(1);

        $actionCommand = CommandFactory::create([$actionUnit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = $this->getSummonImpAction($actionUnit, $enemyCommand, $actionCommand);

        $scenario = new Scenario();

        self::assertEquals('right_command_melee', $scenario->getSummonRow($action));

        $action = $this->getSummonSkeletonMageAction($actionUnit, $enemyCommand, $actionCommand);

        self::assertEquals('right_command_range', $scenario->getSummonRow($action));
    }

    /**
     * Тест на ситуацию, когда Action имеет неизвестный метод анимации в Scenario
     *
     * @throws Exception
     */
    public function testScenarioUndefinedAnimationName(): void
    {
        $scenario = new Scenario();
        [$unit, $command, $enemyCommand] = BaseFactory::create(1, 2);

        $animationMethod = 'undefinedAnimationMethod';

        $action = new DamageAction(
           $this->container,
            $unit,
            $enemyCommand,
            $command,
            DamageAction::TARGET_SELF,
            true,
            DamageAction::DEFAULT_NAME,
            $animationMethod,
            DamageAction::DEFAULT_MESSAGE_METHOD,
            $unit->getOffense()
        );

        $this->expectException(ScenarioException::class);
        $this->expectExceptionMessage(ScenarioException::UNDEFINED_ANIMATION_METHOD . ': ' . $animationMethod);
        $scenario->addAnimation($action, new Statistic());
    }

    /**
     * Создает Action, который призовет Imp
     *
     * @param UnitInterface $actionUnit
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $actionCommand
     * @return SummonAction
     * @throws Exception
     */
    private function getSummonImpAction(
        UnitInterface $actionUnit,
        CommandInterface $enemyCommand,
        CommandInterface $actionCommand
    ): SummonAction
    {
        return new SummonAction(
           $this->container,
            $actionUnit,
            $enemyCommand,
            $actionCommand,
            'Summon Imp',
            UnitFactory::createByTemplate(18)
        );
    }

    /**
     * Создает Action, который призовет Skeleton Mage
     *
     * @param UnitInterface $actionUnit
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $actionCommand
     * @return SummonAction
     * @throws Exception
     */
    private function getSummonSkeletonMageAction(
        UnitInterface $actionUnit,
        CommandInterface $enemyCommand,
        CommandInterface $actionCommand
    ): SummonAction
    {
        return new SummonAction(
           $this->container,
            $actionUnit,
            $enemyCommand,
            $actionCommand,
            'Summon Skeleton Mage',
            UnitFactory::createByTemplate(19)
        );
    }

    /**
     * Создает и возвращает EffectAction
     *
     * @param UnitInterface $unit
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $command
     * @param int $target
     * @return ActionInterface
     * @throws Exception
     */
    private function getReserveForcesAction(
        UnitInterface $unit,
        CommandInterface $enemyCommand,
        CommandInterface $command,
        int $target
    ): ActionInterface
    {
        $data = [
            'type'           => ActionInterface::EFFECT,
            'action_unit'    => $unit,
            'enemy_command'  => $enemyCommand,
            'allies_command' => $command,
            'type_target'    => $target,
            'name'           => 'use Reserve Forces',
            'effect'         => [
                'name'                  => 'Effect#123',
                'icon'                  => '/images/icons/ability/156.png',
                'duration'              => 8,
                'on_apply_actions'      => [
                    [
                        'type'           => ActionInterface::BUFF,
                        'action_unit'    => $unit,
                        'enemy_command'  => $enemyCommand,
                        'allies_command' => $command,
                        'type_target'    => ActionInterface::TARGET_SELF,
                        'name'           => 'use Reserve Forces',
                        'modify_method'  => BuffAction::MAX_LIFE,
                        'power'          => 30,
                    ],
                ],
                'on_next_round_actions' => [],
                'on_disable_actions'    => [],
            ],
        ];

        return $this->container->getActionFactory()->create($data);
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
           $this->container,
            $unit,
            $enemyCommand,
            $command,
            $typeTarget,
            true,
            DamageAction::DEFAULT_NAME,
            DamageAction::UNIT_ANIMATION_METHOD,
            DamageAction::DEFAULT_MESSAGE_METHOD,
            $unit->getOffense()
        );
    }
}
