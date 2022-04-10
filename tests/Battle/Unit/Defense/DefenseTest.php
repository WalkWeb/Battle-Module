<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Defense;

use Battle\Unit\Defense\Defense;
use Tests\AbstractUnitTest;

class DefenseTest extends AbstractUnitTest
{
    /**
     * Тест на создание Defense
     */
    public function testDefenseCreate(): void
    {
        $defenseValue = 100;
        $block = 0;

        $defense = new Defense($defenseValue, $block);

        self::assertEquals($defenseValue, $defense->getDefense());
        self::assertEquals($block, $defense->getBlock());
    }
}
