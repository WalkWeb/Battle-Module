<?php

declare(strict_types=1);

namespace Tests\Battle\Factory;

use Battle\Classes\ClassFactoryException;
use Battle\Command;
use Battle\Exception\CommandException;

class CommandFactory
{
    /**
     * @return Command
     * @throws CommandException
     * @throws UnitFactoryException
     * @throws ClassFactoryException
     */
    public static function createLeftCommand(): Command
    {
        return new Command([UnitFactory::create(1)]);
    }

    /**
     * @return Command
     * @throws CommandException
     * @throws UnitFactoryException
     * @throws ClassFactoryException
     */
    public static function createRightCommand(): Command
    {
        return new Command([UnitFactory::create(2)]);
    }
}
