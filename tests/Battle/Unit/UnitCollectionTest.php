<?php

declare(strict_types=1);

namespace Tests\Battle\Unit;

use Battle\Classes\ClassFactory;
use Battle\Classes\ClassFactoryException;
use Battle\Unit\Unit;
use Battle\Unit\UnitCollection;
use PHPUnit\Framework\TestCase;

class UnitCollectionTest extends TestCase
{
    /**
     * @throws ClassFactoryException
     */
    public function testCreateUnitCollectionSuccess(): void
    {
        $collection = new UnitCollection();

        $collection->add(new Unit('User 1', 15, 1, 110, true, ClassFactory::create(1)));
        $collection->add(new Unit('User 2', 12, 1, 95, false, ClassFactory::create(2)));


        self::assertCount(2, $collection);

        foreach ($collection as $user) {
            self::assertTrue($user->isAlive());
            self::assertFalse($user->isAction());
        }
    }
}
