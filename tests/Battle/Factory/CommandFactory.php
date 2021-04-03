<?php

declare(strict_types=1);

namespace Tests\Battle\Factory;

use Battle\Classes\ClassFactoryException;
use Battle\Command\Command;
use Battle\Command\CommandInterface;
use Battle\Command\CommandException;
use Battle\Unit\UnitCollection;

class CommandFactory
{
    /**
     * @return CommandInterface
     * @throws CommandException
     * @throws UnitFactoryException
     * @throws ClassFactoryException
     */
    public static function createLeftCommand(): CommandInterface
    {
        $unitCollection = new UnitCollection();
        $unitCollection->add(UnitFactory::createByTemplate(1));
        return new Command($unitCollection);
    }

    /**
     * @return CommandInterface
     * @throws CommandException
     * @throws UnitFactoryException
     * @throws ClassFactoryException
     */
    public static function createRightCommand(): CommandInterface
    {
        $unitCollection = new UnitCollection();
        $unitCollection->add(UnitFactory::createByTemplate(2));
        return new Command($unitCollection);
    }
}
