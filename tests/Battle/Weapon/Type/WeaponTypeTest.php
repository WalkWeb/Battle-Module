<?php

declare(strict_types=1);

namespace Tests\Battle\Weapon\Type;

use Battle\Action\ActionCollection;
use Battle\Command\CommandFactory;
use Battle\Weapon\Type\WeaponType;
use Battle\Weapon\Type\WeaponTypeException;
use Exception;
use Tests\AbstractUnitTest;
use Tests\Factory\UnitFactory;

class WeaponTypeTest extends AbstractUnitTest
{
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

        $weaponType = new WeaponType($id);

        self::assertEquals($id, $weaponType->getId());
        self::assertEquals($expectedName, $weaponType->getName());
        self::assertEquals(new ActionCollection(), $weaponType->getActions($enemyCommand, $command));
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
        new WeaponType(55);
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
}
