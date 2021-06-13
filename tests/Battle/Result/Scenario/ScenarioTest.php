<?php

declare(strict_types=1);

namespace Tests\Battle\Result\Scenario;

use Battle\Action\ActionException;
use Battle\Action\Damage\DamageAction;
use Battle\Action\Other\WaitAction;
use Battle\Command\CommandException;
use Battle\Command\CommandFactory;
use Battle\Result\Chat\Message;
use Battle\Result\Scenario\Scenario;
use Battle\Unit\UnitException;
use Exception;
use JsonException;
use PHPUnit\Framework\TestCase;
use Tests\Battle\Factory\UnitFactory;

class ScenarioTest extends TestCase
{
    /**
     * За основу берется DamageActionTest::testApplyDamageAction()
     *
     * И дополняется созданием и проверкой сценария
     *
     * @throws ActionException
     * @throws CommandException
     * @throws UnitException
     * @throws Exception
     */
    public function testScenarioAddDamage(): void
    {
        $attackUnit = UnitFactory::createByTemplate(1);
        $defendUnit = UnitFactory::createByTemplate(2);
        $attackCommand = CommandFactory::create([$attackUnit]);
        $defendCommand = CommandFactory::create([$defendUnit]);
        $action = new DamageAction($attackUnit, $defendCommand, $attackCommand, new Message());

        $action->handle();

        $scenario = new Scenario();
        $scenario->addAction($action);

        $expectedData = [
            'step'    => 1,
            'attack'  => 1,
            'effects' => [
                [
                    'user_id'        => $attackUnit->getId(),
                    'class'          => 'd_attack',
                    'unit_cons_bar2' => 0,
                    'unit_rage_bar2' => 0,
                    'unit_effects'   => '',
                    'targets'        => [
                        [
                            'user_id'           => $defendUnit->getId(),
                            'class'             => 'd_red',
                            'hp'                => $defendUnit->getTotalLife() - $attackUnit->getDamage(),
                            'thp'               => $defendUnit->getTotalLife(),
                            'hp_bar_class'      => 'unit_hp_bar',
                            'hp_bar_class2'     => 'unit_hp_bar2',
                            'recdam'            => '-20',
                            'unit_hp_bar_width' => 86,
                            'unit_cons_bar2'    => 10,
                            'unit_rage_bar2'    => 0,
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
     * @throws CommandException
     * @throws UnitException
     * @throws Exception
     */
    public function testScenarioAddHeal(): void
    {
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
            $scenario->addAction($action);
        }

        // Проверяем лечение
        self::assertEquals(1 + $actionUnit->getDamage() * 3, $woundedUnit->getLife());

        $expectedData = [
            'step'    => 1,
            'attack'  => 1,
            'effects' => [
                [
                    'user_id'        => $actionUnit->getId(),
                    'class'          => 'd_buff',
                    'unit_cons_bar2' => 0,
                    'unit_rage_bar2' => 0,
                    'targets'        => [
                        [
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
     * @throws CommandException
     * @throws UnitException
     * @throws Exception
     */
    public function testScenarioAddWait(): void
    {
        $actionUnit = UnitFactory::createByTemplate(14);
        $enemyUnit = UnitFactory::createByTemplate(3);

        $actionCommand = CommandFactory::create([$actionUnit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = new WaitAction($actionUnit, $enemyCommand, $actionCommand, new Message());

        $scenario = new Scenario();

        $scenario->addAction($action);

        $expectedData = [
            'step'    => 1,
            'attack'  => 1,
            'effects' => [],
        ];

        self::assertEquals($expectedData, $scenario->getArray()[0]);
    }

    /**
     * @throws JsonException
     */
    public function testScenarioGetJson(): void
    {
        $scenario = new Scenario();
        self::assertEquals('[]', $scenario->getJson());
    }
}
