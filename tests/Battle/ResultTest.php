<?php

declare(strict_types=1);

namespace Tests\Battle;

use Battle\Classes\ClassFactoryException;
use PHPUnit\Framework\TestCase;
use Battle\Result;
use Tests\Battle\Factory\CommandFactory;
use Battle\Exception\ResultException;
use Battle\Command\CommandException;
use Tests\Battle\Factory\UnitFactoryException;

class ResultTest extends TestCase
{
    /**
     * @throws ClassFactoryException
     * @throws CommandException
     * @throws ResultException
     * @throws UnitFactoryException
     */
    public function testCreate(): void
    {
        $leftCommand = CommandFactory::createLeftCommand();
        $rightCommand = CommandFactory::createRightCommand();

        $result = new Result($leftCommand, $rightCommand, $winner = 2);

        self::assertInstanceOf(Result::class, $result);
        self::assertEquals($leftCommand, $result->getLeftCommand());
        self::assertEquals($rightCommand, $result->getRightCommand());
        self::assertEquals($winner, $result->getWinner());
    }
}
