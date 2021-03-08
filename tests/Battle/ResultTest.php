<?php

declare(strict_types=1);

namespace Tests;

use Battle\Classes\ClassFactoryException;
use PHPUnit\Framework\TestCase;
use Battle\Result;
use Tests\Battle\Factory\CommandFactory;
use Battle\Exception\ResultException;
use Battle\Exception\CommandException;
use Tests\Battle\Factory\UnitFactoryException;

class ResultTest extends TestCase
{
    /**
     * @throws ClassFactoryException
     */
    public function testCreate(): void
    {
        try {
            $leftCommand = CommandFactory::createLeftCommand();
            $rightCommand = CommandFactory::createRightCommand();

            $result = new Result($leftCommand, $rightCommand, $winner = 2);

            $this->assertInstanceOf(Result::class, $result);
            $this->assertEquals($leftCommand, $result->getLeftCommand());
            $this->assertEquals($rightCommand, $result->getRightCommand());
            $this->assertEquals($winner, $result->getWinner());

        } catch (ResultException | CommandException | UnitFactoryException $e) {
            $this->fail($e->getMessage());
        }
    }
}
