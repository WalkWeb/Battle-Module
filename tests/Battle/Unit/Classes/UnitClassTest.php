<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Classes;

use Battle\Action\ActionInterface;
use Battle\Command\CommandFactory;
use Battle\Unit\Ability\AbilityInterface;
use Battle\Unit\Classes\UnitClass;
use Battle\Unit\Classes\UnitClassException;
use Exception;
use Tests\AbstractUnitTest;
use Tests\Battle\Factory\UnitFactory;

class UnitClassTest extends AbstractUnitTest
{
    /**
     * Тест на успешное создание класса юнита через универсальный класс UnitClass
     *
     * @dataProvider classDataProvider
     * @param array $data
     * @throws Exception
     */
    public function testUnitClassCreate(array $data): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $class = new UnitClass(
            $data['id'],
            $data['name'],
            $data['small_icon'],
            $data['abilities'],
        );

        // Проверяем базовые параметры
        self::assertEquals($data['id'], $class->getId());
        self::assertEquals($data['name'], $class->getName());
        self::assertEquals($data['small_icon'], $class->getSmallIcon());

        // Проверяем, что actions-способностей созданные через массив параметров соответствуют аналогам из класса Warrior
        self::assertSameSize(
            $unit->getClass()->getAbilities($unit),
            $class->getAbilities($unit)
        );

        $expectedAbilities = [];

        foreach ($unit->getClass()->getAbilities($unit) as $i => $ability) {
            $expectedAbilities[] = $ability;
        }

        foreach ($class->getAbilities($unit) as $i => $ability) {
            self::assertEquals(
                $expectedAbilities[$i]->getAction($enemyCommand, $command),
                $ability->getAction($enemyCommand, $command)
            );
        }
    }

    /**
     * Тест на ситуацию, когда переданный массив $abilitiesData не содержит внутри себя массивы
     *
     * @throws Exception
     */
    public function testUnitClassInvalidAbilitiesData(): void
    {
        $this->expectException(UnitClassException::class);
        $this->expectExceptionMessage(UnitClassException::INVALID_ABILITY_DATA);

        new UnitClass(
            15,
            'Demo Class',
            'icon.png',
            ['invalid_data'],
        );
    }

    /**
     * @return array
     */
    public function classDataProvider(): array
    {
        return [
            [
                [
                    'id'         => 1,
                    'name'       => 'Warrior',
                    'small_icon' => '/images/icons/small/warrior.png',
                    'abilities'  => [
                        [
                            'name'          => 'Heavy Strike',
                            'icon'          => '/images/icons/ability/335.png',
                            'disposable'    => false,
                            'type_activate' => AbilityInterface::ACTIVATE_CONCENTRATION,
                            'actions'       => [
                                [
                                    'type'             => ActionInterface::DAMAGE,
                                    'type_target'      => ActionInterface::TARGET_RANDOM_ENEMY,
                                    'damage'           => 50,
                                    'can_be_avoided'   => true,
                                    'name'             => 'Heavy Strike',
                                    'animation_method' => 'damage',
                                    'message_method'   => 'damageAbility',
                                    'icon'             => '/images/icons/ability/335.png',
                                ],
                            ],
                        ],
                        [
                            'name'          => 'Heavy Strike',
                            'icon'          => '/images/icons/ability/335.png',
                            'disposable'    => false,
                            'type_activate' => AbilityInterface::ACTIVATE_CONCENTRATION,
                            'actions'       => [
                                [
                                    'type'           => ActionInterface::EFFECT,
                                    'type_target'    => ActionInterface::TARGET_SELF,
                                    'name'           => 'Blessed Shield',
                                    'icon'           => '/images/icons/ability/271.png',
                                    'message_method' => 'applyEffect',
                                    'effect'         => [
                                        'name'                  => 'Blessed Shield',
                                        'icon'                  => '/images/icons/ability/271.png',
                                        'duration'              => 6,
                                        'on_apply_actions'      => [
                                            [
                                                'type'           => ActionInterface::BUFF,
                                                'type_target'    => ActionInterface::TARGET_SELF,
                                                'name'           => 'Blessed Shield',
                                                'modify_method'  => 'addBlock',
                                                'power'          => 15,
                                                'message_method' => ActionInterface::SKIP_MESSAGE_METHOD,
                                            ],
                                        ],
                                        'on_next_round_actions' => [],
                                        'on_disable_actions'    => [],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
