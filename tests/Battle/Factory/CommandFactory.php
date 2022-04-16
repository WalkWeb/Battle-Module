<?php

declare(strict_types=1);

namespace Tests\Battle\Factory;

use Battle\Command\Command;
use Battle\Command\CommandInterface;
use Battle\Traits\IdTrait;
use Battle\Unit\UnitCollection;
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
            'name'         => 'unit_1',
            'level'        => 1,
            'avatar'       => '/images/avas/humans/human001.jpg',
            'life'         => 100,
            'total_life'   => 100,
            'melee'        => true,
            'class'        => 1,
            'race'         => 1,
            'command'      => 1,
            'offense'    => [
                'damage'       => 15,
                'attack_speed' => 1.2,
                'accuracy'     => 200,
                'block_ignore' => 0,
            ],
            'defense'    => [
                'defense' => 100,
                'block'   => 0,
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
