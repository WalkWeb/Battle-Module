<?php

declare(strict_types=1);

namespace Tests\Battle\Result\Scenario;

use Battle\Action\Summon\SummonImpAction;
use Battle\Action\Summon\SummonSkeletonMage;
use Exception;
use Battle\Action\Damage\DamageAction;
use Battle\Action\Other\WaitAction;
use Battle\Command\CommandFactory;
use Battle\Result\Chat\Message;
use Battle\Result\Scenario\Scenario;
use Battle\Result\Statistic\Statistic;
use PHPUnit\Framework\TestCase;
use Tests\Battle\Factory\UnitFactory;

class ScenarioTest extends TestCase
{
    /**
     * За основу берется DamageActionTest::testApplyDamageAction()
     *
     * И дополняется созданием и проверкой сценария
     *
     * @throws Exception
     */
    public function testScenarioAddDamage(): void
    {
        $statistic = new Statistic();
        $attackUnit = UnitFactory::createByTemplate(1);
        $defendUnit = UnitFactory::createByTemplate(2);
        $attackCommand = CommandFactory::create([$attackUnit]);
        $defendCommand = CommandFactory::create([$defendUnit]);
        $action = new DamageAction($attackUnit, $defendCommand, $attackCommand, new Message());

        $action->handle();

        $scenario = new Scenario();
        $scenario->addAction($action, $statistic);

        $expectedData = [
            'step'    => $statistic->getRoundNumber(),
            'attack'  => $statistic->getStrokeNumber(),
            'effects' => [
                [
                    'user_id'        => $attackUnit->getId(),
                    'class'          => 'd_attack',
                    'unit_cons_bar2' => 0,
                    'unit_rage_bar2' => 0,
                    'unit_effects'   => '',
                    'targets'        => [
                        [
                            'type'              => 'change',
                            'user_id'           => $defendUnit->getId(),
                            'class'             => 'd_red',
                            'hp'                => $defendUnit->getTotalLife() - $attackUnit->getDamage(),
                            'thp'               => $defendUnit->getTotalLife(),
                            'hp_bar_class'      => 'unit_hp_bar',
                            'hp_bar_class2'     => 'unit_hp_bar2',
                            'recdam'            => '-20',
                            'unit_hp_bar_width' => 92,
                            'unit_cons_bar2'    => 10,
                            'unit_rage_bar2'    => 7,
                            'ava'               => 'unit_ava_red',
                            'avas'              => 'unit_ava_blank',
                            'unit_effects'      => '',
                        ],
                    ],
                ],
            ],
        ];

        self::assertEquals($expectedData, $scenario->getArray()[0]);
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
        $actions = $actionUnit->getAction($enemyCommand, $actionCommand);

        foreach ($actions as $action) {
            $action->handle();
            $scenario->addAction($action, $statistic);
        }

        // Проверяем лечение
        self::assertEquals(1 + $actionUnit->getDamage() * 3, $woundedUnit->getLife());

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
                            'recdam'            => '+' . $actionUnit->getDamage() * 3,
                            'hp'                => 1 + $actionUnit->getDamage() * 3,
                            'thp'               => $woundedUnit->getTotalLife(),
                            'hp_bar_class'      => 'unit_hp_bar',
                            'hp_bar_class2'     => 'unit_hp_bar2',
                            'unit_hp_bar_width' => 46,
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
    public function testScenarioAddSummon(): void
    {
        $statistic = new Statistic();

        $actionUnit = UnitFactory::createByTemplate(7);
        $enemyUnit = UnitFactory::createByTemplate(1);

        $actionCommand = CommandFactory::create([$actionUnit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = new SummonImpAction($actionUnit, $enemyCommand, $actionCommand, new Message());

        $scenario = new Scenario();

        $scenario->addAction($action, $statistic);

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
                            'unit_box2_class' => 'unit_box2',
                            'hp'              => 30,
                            'thp'             => 30,
                            'cons_bar_width'  => 0,
                            'rage_bar_width'  => 0,
                            'avatar'          => '/images/avas/monsters/004.png',
                            'name'            => 'Imp',
                            'name_color'      => '#ba4829',
                            'icon'            => '/images/icons/small/base-inferno.png',
                            'level'           => 1,
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

        $action = new WaitAction($actionUnit, $enemyCommand, $actionCommand, new Message());

        $scenario = new Scenario();

        $scenario->addAction($action, $statistic);

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

        $action = new SummonImpAction($actionUnit, $enemyCommand, $actionCommand, new Message());

        self::assertEquals('left_command_melee', $scenario->getSummonRow($action));

        $action = new SummonSkeletonMage($actionUnit, $enemyCommand, $actionCommand, new Message());

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

        $action = new SummonImpAction($actionUnit, $enemyCommand, $actionCommand, new Message());

        $scenario = new Scenario();

        self::assertEquals('right_command_melee', $scenario->getSummonRow($action));

        $action = new SummonSkeletonMage($actionUnit, $enemyCommand, $actionCommand, new Message());

        self::assertEquals('right_command_range', $scenario->getSummonRow($action));
    }


}
