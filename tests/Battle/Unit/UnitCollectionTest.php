<?php

declare(strict_types=1);

namespace Tests\Battle\Unit;

use Battle\Classes\UnitClassFactory;
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

        $collection->add(
            new Unit(
                '5aa0d764-e92d-4137-beed-f7f590b08165',
                'User 1',
                'avatar 1',
                15,
                1,
                110,
                true,
                UnitClassFactory::create(1)
            )
        );

        $collection->add(
            new Unit(
                '648a3bc6-13e3-4a24-8fbf-10c196251cc2',
                'User 2',
                'avatar 2',
                12,
                1,
                95,
                false,
                UnitClassFactory::create(2)
            )
        );

        self::assertCount(2, $collection);

        foreach ($collection as $user) {
            self::assertTrue($user->isAlive());
            self::assertFalse($user->isAction());
        }
    }
}
