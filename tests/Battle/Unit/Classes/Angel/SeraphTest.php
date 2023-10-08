<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Classes\Angel;

use Exception;
use Tests\AbstractUnitTest;
use Tests\Factory\UnitFactory;

class SeraphTest extends AbstractUnitTest
{
    /**
     * @throws Exception
     */
    public function testSeraphCreate(): void
    {
        $unit = UnitFactory::createByTemplate(52, $this->container);

        $succubus = $unit->getClass();

        self::assertEquals(9, $succubus->getId());
        self::assertEquals('Seraphim', $succubus->getName());
        self::assertEquals('/images/icons/small/seraphim.png', $succubus->getSmallIcon());

        self::assertCount(0, $succubus->getAbilities($unit));
    }
}
