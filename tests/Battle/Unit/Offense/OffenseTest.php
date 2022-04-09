<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Offense;

use Battle\Unit\Offense\Offense;
use Tests\AbstractUnitTest;

class OffenseTest extends AbstractUnitTest
{
    /**
     * Тест на создание Offense
     */
    public function testOffenseCreate(): void
    {
        $damage = 100;
        $attackSpeed = 1.2;
        $accuracy = 200;
        $blockIgnore = 0;

        $offense = new Offense($damage, $attackSpeed, $accuracy, $blockIgnore);

        self::assertEquals($damage, $offense->getDamage());
        self::assertEquals($attackSpeed, $offense->getAttackSpeed());
        self::assertEquals($accuracy, $offense->getAccuracy());
        self::assertEquals($blockIgnore, $offense->getBlockIgnore());
    }
}
