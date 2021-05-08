<?php

declare(strict_types=1);

namespace Tests\Battle\Factory;

use Battle\Classes\ClassFactoryException;
use Battle\Command\Command;
use Battle\Command\CommandInterface;
use Battle\Command\CommandException;
use Battle\Traits\IdTrait;
use Battle\Unit\UnitCollection;
use Battle\Unit\UnitException;
use Exception;
use Battle\Unit\UnitFactory as BaseUnitFactory;

class CommandFactory
{
    use IdTrait;

    /**
     * @return CommandInterface
     * @throws ClassFactoryException
     * @throws CommandException
     * @throws UnitFactoryException
     * @throws UnitException
     */
    public static function createLeftCommand(): CommandInterface
    {
        $unitCollection = new UnitCollection();
        $unitCollection->add(UnitFactory::createByTemplate(1));
        return new Command($unitCollection);
    }

    /**
     * @return CommandInterface
     * @throws ClassFactoryException
     * @throws CommandException
     * @throws UnitException
     * @throws UnitFactoryException
     */
    public static function createRightCommand(): CommandInterface
    {
        $unitCollection = new UnitCollection();
        $unitCollection->add(UnitFactory::createByTemplate(2));
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
            'damage'       => 20,
            'attack_speed' => 1.00,
            'life'         => 100,
            'total_life'   => 100,
            'melee'        => true,
            'class'        => 1,
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
