<?php

declare(strict_types=1);

namespace Tests\Battle\Weapon\Type;

use Battle\Action\ActionCollection;
use Battle\Action\ActionInterface;
use Battle\Action\DamageAction;
use Battle\Command\CommandFactory;
use Battle\Command\CommandInterface;
use Battle\Traits\AbilityDataTrait;
use Battle\Unit\UnitInterface;
use Battle\Weapon\Type\WeaponType;
use Battle\Weapon\Type\WeaponTypeException;
use Battle\Weapon\Type\WeaponTypeInterface;
use Exception;
use Tests\AbstractUnitTest;
use Tests\Factory\UnitFactory;

class WeaponTypeTest extends AbstractUnitTest
{
    use AbilityDataTrait;

    /**
     * Тест на создание типа оружия
     *
     * @dataProvider createDataProvider
     * @param int $id
     * @param string $expectedName
     * @throws Exception
     */
    public function testWeaponTypeCreate(int $id, string $expectedName): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $weaponType = new WeaponType($id, $this->getContainer());

        self::assertEquals($id, $weaponType->getId());
        self::assertEquals($expectedName, $weaponType->getName());

        if ($id === WeaponTypeInterface::MACE) {
            self::assertEquals($this->createStunAction($unit, $enemyCommand, $command, 1), $weaponType->getOnCriticalAction($unit, $enemyCommand, $command));
        } elseif ($id === WeaponTypeInterface::TWO_HAND_MACE) {
            self::assertEquals($this->createStunAction($unit, $enemyCommand, $command, 2), $weaponType->getOnCriticalAction($unit, $enemyCommand, $command));
        } elseif ($id === WeaponTypeInterface::HEAVY_TWO_HAND_MACE) {
            self::assertEquals($this->createStunAction($unit, $enemyCommand, $command, 3), $weaponType->getOnCriticalAction($unit, $enemyCommand, $command));
        } elseif ($id === WeaponTypeInterface::DAGGER) {
            self::assertEquals($this->createBleedingAction($unit, $enemyCommand, $command, 3), $weaponType->getOnCriticalAction($unit, $enemyCommand, $command));
        } else {
            self::assertEquals(new ActionCollection(), $weaponType->getOnCriticalAction($unit, $enemyCommand, $command));
        }
    }

    /**
     * Тест на применение эффекта на от разного вида дробящего оружия и длительность оглушения
     *
     * одноручные булавы - оглушение на 1 ход
     * двуручные булавы - оглушение на 2 хода
     * тяжелые двуручные булавы - оглушение на 3 хода
     *
     * @dataProvider maceDataProvider
     * @param int $unitId
     * @param int $stunDuration
     * @throws Exception
     */
    public function testWeaponTypeMaceEffect(int $unitId, int $stunDuration): void
    {
        $unit = UnitFactory::createByTemplate($unitId);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = new DamageAction(
            $this->getContainer(),
            $unit,
            $enemyCommand,
            $command,
            DamageAction::TARGET_RANDOM_ENEMY,
            true,
            DamageAction::DEFAULT_NAME,
            DamageAction::UNIT_ANIMATION_METHOD,
            DamageAction::DEFAULT_MESSAGE_METHOD,
            $unit->getOffense()
        );

        self::assertTrue($action->canByUsed());

        $callbackActions = $action->handle();

        // Проверяем наличие события от удара
        self::assertCount(1 , $callbackActions);

        // Проверяем, что это оглушение от оружия
        foreach ($callbackActions as $callbackAction) {
            self::assertEquals('Stun Weapon Effect', $callbackAction->getNameAction());

            // Проверяем длительность эффекта
            self::assertEquals($stunDuration, $callbackAction->getEffect()->getBaseDuration());
        }
    }

    /**
     * Тест на применение эффекта на от критического удара с кинжала
     *
     * @throws Exception
     */
    public function testWeaponTypeDaggerEffect(): void
    {
        $unit = UnitFactory::createByTemplate(50);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $action = new DamageAction(
            $this->getContainer(),
            $unit,
            $enemyCommand,
            $command,
            DamageAction::TARGET_RANDOM_ENEMY,
            true,
            DamageAction::DEFAULT_NAME,
            DamageAction::UNIT_ANIMATION_METHOD,
            DamageAction::DEFAULT_MESSAGE_METHOD,
            $unit->getOffense()
        );

        self::assertTrue($action->canByUsed());

        $callbackActions = $action->handle();

        // Проверяем наличие события от удара
        self::assertCount(1 , $callbackActions);

        // Проверяем, что это кровотечение от оружия
        foreach ($callbackActions as $callbackAction) {
            self::assertEquals('Bleeding Weapon Effect', $callbackAction->getNameAction());

            // Проверяем длительность эффекта
            self::assertEquals(3, $callbackAction->getEffect()->getBaseDuration());
        }
    }

    /**
     * Тест на ситуацию, когда передан неизвестный id типа оружия
     *
     * @throws WeaponTypeException
     */
    public function testWeaponTypeUnknownWeaponType(): void
    {
        $this->expectException(WeaponTypeException::class);
        $this->expectExceptionMessage(WeaponTypeException::UNKNOWN_WEAPON_TYPE_ID . ': 55');
        new WeaponType(55, $this->getContainer());
    }

    /**
     * @return array
     */
    public function createDataProvider(): array
    {
        return [
            [
                0,
                'None',
            ],
            [
                1,
                'Sword',
            ],
            [
                2,
                'Axe',
            ],
            [
                3,
                'Mace',
            ],
            [
                4,
                'Dagger',
            ],
            [
                5,
                'Spear',
            ],
            [
                6,
                'Bow',
            ],
            [
                7,
                'Staff',
            ],
            [
                8,
                'Wand',
            ],
            [
                9,
                'Two hand sword',
            ],
            [
                10,
                'Two hand axe',
            ],
            [
                11,
                'Two hand mace',
            ],
            [
                12,
                'Heavy two hand sword',
            ],
            [
                13,
                'Heavy two hand axe',
            ],
            [
                14,
                'Heavy two hand mace',
            ],
            [
                15,
                'Lance',
            ],
            [
                16,
                'Crossbow',
            ],
            [
                17,
                'Unarmed',
            ],
        ];
    }

    public function maceDataProvider(): array
    {
        return [
            [
                // id юнита с одноручной булавой и 100% шансом крита
                47,
                // длительность оглушения
                1,
            ],
            [
                // id юнита с двуручной булавой и 100% шансом крита
                48,
                // длительность оглушения
                2,
            ],
            [
                // id юнита с тяжелой двуручной булавой и 100% шансом крита
                49,
                // длительность оглушения
                3,
            ],
        ];
    }

    /**
     * @param UnitInterface $unit
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $command
     * @param int $stunDuration
     * @return ActionCollection
     * @throws Exception
     */
    private function createStunAction(
        UnitInterface $unit,
        CommandInterface $enemyCommand,
        CommandInterface $command,
        int $stunDuration
    ): ActionCollection
    {
        $data = [
            [
                'type'           => ActionInterface::EFFECT,
                'type_target'    => ActionInterface::TARGET_SELF,
                'name'           => 'Stun Weapon Effect',
                'icon'           => '/images/icons/ability/435.png',
                'message_method' => 'applyEffect',
                'effect'         => [
                    'name'                  => 'Stun',
                    'icon'                  => '/images/icons/ability/435.png',
                    'duration'              => $stunDuration,
                    'on_apply_actions'      => [],
                    'on_next_round_actions' => [
                        [
                            'type'             => ActionInterface::PARALYSIS,
                            'type_target'      => ActionInterface::TARGET_SELF,
                            'name'             => 'Stun',
                            'can_be_avoided'   => false,
                            'animation_method' => ActionInterface::SKIP_ANIMATION_METHOD,
                            'message_method'   => 'stunned',
                            'icon'             => '/images/icons/ability/435.png',
                        ],
                    ],
                    'on_disable_actions'    => [],
                ],
            ],
        ];

        $actions = new ActionCollection();
        foreach ($data as &$actionData) {
            $this->addParameters($actionData, $unit, $enemyCommand, $command);
            $actions->add($this->getContainer()->getActionFactory()->create($actionData));
        }

        return $actions;
    }

    /**
     * @param UnitInterface $unit
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $command
     * @param int $stunDuration
     * @return ActionCollection
     * @throws Exception
     */
    private function createBleedingAction(
        UnitInterface $unit,
        CommandInterface $enemyCommand,
        CommandInterface $command,
        int $stunDuration
    ): ActionCollection
    {
        $data = [
            [
                'type'           => ActionInterface::EFFECT,
                'type_target'    => ActionInterface::TARGET_SELF,
                'name'           => 'Bleeding Weapon Effect',
                'icon'           => '/images/icons/ability/438.png',
                'message_method' => 'applyEffect',
                'effect'         => [
                    'name'                  => 'Bleeding',
                    'icon'                  => '/images/icons/ability/438.png',
                    'duration'              => 3,
                    'on_apply_actions'      => [],
                    'on_next_round_actions' => [
                        [
                            'type'             => ActionInterface::DAMAGE,
                            'type_target'      => ActionInterface::TARGET_SELF,
                            'name'             => 'Bleeding',
                            'multiple_offense' => [
                                'damage'              => 0.25,
                                'speed'               => 1.0,
                                'accuracy'            => 1.0,
                                'critical_chance'     => 0.0,
                                'critical_multiplier' => 1.0,
                            ],
                            'can_be_avoided'   => false,
                            'animation_method' => DamageAction::EFFECT_ANIMATION_METHOD,
                            'message_method'   => DamageAction::EFFECT_MESSAGE_METHOD,
                            'icon'             => '/images/icons/ability/438.png',
                        ],
                    ],
                    'on_disable_actions'    => [],
                ],
            ],
        ];

        $actions = new ActionCollection();
        foreach ($data as &$actionData) {
            $this->addParameters($actionData, $unit, $enemyCommand, $command);
            $actions->add($this->getContainer()->getActionFactory()->create($actionData));
        }

        return $actions;
    }
}
