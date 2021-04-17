<?php

declare(strict_types=1);

namespace Tests\Battle\Command;

use Battle\Classes\ClassFactoryException;
use Battle\Command\Command;
use Battle\Command\CommandException;
use Battle\Unit\UnitCollection;
use Battle\Unit\UnitException;
use PHPUnit\Framework\TestCase;
use Tests\Battle\Factory\UnitFactory;
use Tests\Battle\Factory\UnitFactoryException;

class CommandHealTest extends TestCase
{
    /**
     * @throws ClassFactoryException
     * @throws CommandException
     * @throws UnitFactoryException
     * @throws UnitException
     */
    public function testGetUnitForHeal(): void
    {
        $unit = UnitFactory::createByTemplate(10);

        $unitCollection = new UnitCollection();
        $unitCollection->add($unit);
        $command = new Command($unitCollection);

        self::assertNull($command->getUnitForHeal());
    }
}
