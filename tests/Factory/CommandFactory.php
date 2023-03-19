<?php

declare(strict_types=1);

namespace Tests\Factory;

use Battle\Command\Command;
use Battle\Command\CommandInterface;
use Battle\Traits\IdTrait;
use Battle\Unit\UnitCollection;
use Battle\Weapon\Type\WeaponTypeInterface;
use Exception;
use Battle\Unit\UnitFactory as BaseUnitFactory;

class CommandFactory
{
    use IdTrait;

    /**
     * @param int $unitTemplate
     * @return CommandInterface
     * @throws Exception
     */
    public static function createLeftCommand(int $unitTemplate = 1): CommandInterface
    {
        $unitCollection = new UnitCollection();
        $unitCollection->add(UnitFactory::createByTemplate($unitTemplate));
        return new Command($unitCollection);
    }

    /**
     * @param int $unitTemplate
     * @return CommandInterface
     * @throws Exception
     */
    public static function createRightCommand(int $unitTemplate = 2): CommandInterface
    {
        $unitCollection = new UnitCollection();
        $unitCollection->add(UnitFactory::createByTemplate($unitTemplate));
        return new Command($unitCollection);
    }

    /**
     * @param int $countUnits
     * @return CommandInterface
     * @throws Exception
     */
    public static function createVeryBigCommand(int $countUnits = 20): CommandInterface
    {
        $unitCollection = new UnitCollection();
        $data = [
            'name'                         => 'unit_1',
            'level'                        => 1,
            'avatar'                       => '/images/avas/humans/human001.jpg',
            'life'                         => 100,
            'total_life'                   => 100,
            'mana'                         => 50,
            'total_mana'                   => 50,
            'melee'                        => true,
            'class'                        => 1,
            'race'                         => 1,
            'command'                      => 1,
            'add_concentration_multiplier' => 0,
            'add_rage_multiplier'          => 0,
            'offense'                      => [
                'damage_type'         => 1,
                'weapon_type'         => WeaponTypeInterface::SWORD,
                'physical_damage'     => 15,
                'fire_damage'         => 0,
                'water_damage'        => 0,
                'air_damage'          => 0,
                'earth_damage'        => 0,
                'life_damage'         => 0,
                'death_damage'        => 0,
                'attack_speed'        => 1.2,
                'cast_speed'          => 0,
                'accuracy'            => 200,
                'magic_accuracy'      => 100,
                'block_ignoring'      => 0,
                'critical_chance'     => 5,
                'critical_multiplier' => 200,
                'damage_multiplier'   => 100,
                'vampirism'           => 0,
                'magic_vampirism'     => 0,
            ],
            'defense'                      => [
                'physical_resist'     => 0,
                'fire_resist'         => 0,
                'water_resist'        => 0,
                'air_resist'          => 0,
                'earth_resist'        => 0,
                'life_resist'         => 0,
                'death_resist'        => 0,
                'defense'             => 100,
                'magic_defense'       => 50,
                'block'               => 0,
                'magic_block'         => 0,
                'mental_barrier'      => 0,
                'max_physical_resist' => 75,
                'max_fire_resist'     => 75,
                'max_water_resist'    => 75,
                'max_air_resist'      => 75,
                'max_earth_resist'    => 75,
                'max_life_resist'     => 75,
                'max_death_resist'    => 75,
                'global_resist'       => 0,
                'dodge'               => 0,
            ],
        ];

        $i = 0;
        while ($i < $countUnits) {
            $data['id'] = self::generateId();
            $unitCollection->add(BaseUnitFactory::create($data));
            $i++;
        }

        return new Command($unitCollection);
    }
}
