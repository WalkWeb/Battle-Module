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

    /**
     * Тест на обновление параметров Defense
     */
    public function testDefenseUpdate(): void
    {
        $defense = new Defense(10, 10);

        $defense->setDefense($defenseValue = 1000);
        $defense->setBlock($block = 5);

        self::assertEquals($defenseValue, $defense->getDefense());
        self::assertEquals($block, $defense->getBlock());
    }
}
