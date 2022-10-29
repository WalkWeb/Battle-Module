<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Classes\Human;

use Battle\Container\Container;
use Exception;
use Tests\AbstractUnitTest;
use Tests\Battle\Factory\UnitFactory;

class PaladinTest extends AbstractUnitTest
{
    /**
     * Тест на создание класса Paladin
     *
     * @throws Exception
     */
    public function testPaladinCreateClass(): void
    {
        $container = new Container();
        $unit = UnitFactory::createByTemplate(44, $container);

        $paladin = $unit->getClass();

        self::assertEquals(8, $paladin->getId());
        self::assertEquals('Paladin', $paladin->getName());
        self::assertEquals('/images/icons/small/paladin.png', $paladin->getSmallIcon());

        $abilities = $paladin->getAbilities($unit);

        self::assertCount(0, $abilities);
    }
}
