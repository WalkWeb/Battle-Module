<?php

declare(strict_types=1);

namespace Tests\Battle\Weapon\Type;

use Battle\Action\ActionCollection;
use Battle\Action\ActionInterface;
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

        if ($id === WeaponTypeInterface::MACE ) {
            self::assertEquals($this->createStunAction($unit, $enemyCommand, $command), $weaponType->getOnCriticalAction($unit, $enemyCommand, $command));
        } else {
            self::assertEquals(new ActionCollection(), $weaponType->getOnCriticalAction($unit, $enemyCommand, $command));
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

    /**
     * @param UnitInterface $unit
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $command
     * @return ActionCollection
     * @throws Exception
     */
    private function createStunAction(UnitInterface $unit, CommandInterface $enemyCommand, CommandInterface $command): ActionCollection
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
                    'duration'              => 1,
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
}
