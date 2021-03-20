<?php

declare(strict_types=1);

namespace Tests\Battle\Factory;

use Battle\Classes\ClassFactoryException;
use Battle\Command\Command;
use Battle\Command\CommandInterface;
use Battle\Command\CommandException;

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
        return new Command([UnitFactory::create(1)]);
    }

    /**
     * @return CommandInterface
     * @throws CommandException
     * @throws UnitFactoryException
     * @throws ClassFactoryException
     */
    public static function createRightCommand(): CommandInterface
    {
        return new Command([UnitFactory::create(2)]);
    }
}
