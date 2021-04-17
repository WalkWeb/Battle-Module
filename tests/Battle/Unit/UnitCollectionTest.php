<?php

declare(strict_types=1);

namespace Tests\Battle\Unit;

use Battle\Classes\UnitClassFactory;
use Battle\Classes\ClassFactoryException;
use Battle\Unit\Unit;
use Battle\Unit\UnitCollection;
use Battle\Unit\UnitException;
use PHPUnit\Framework\TestCase;

class UnitCollectionTest extends TestCase
{
    /**
     * @throws ClassFactoryException
     * @throws UnitException
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

    /**
     * @throws ClassFactoryException
     * @throws UnitException
     */
    public function testUnitCollectionExistUnit(): void
    {
        $collection = new UnitCollection();

        $id = '5aa0d764-e92d-4137-beed-f7f590b08165';

        $collection->add(
            new Unit(
                $id,
                'User 1',
                'avatar 1',
                15,
                1,
                110,
                110,
                true,
                UnitClassFactory::create(1)
            )
        );

        self::assertTrue($collection->exist($id));
        self::assertFalse($collection->exist('undefined_id'));
    }

    /**
     * @throws ClassFactoryException
     * @throws UnitException
     */
    public function testUnitCollectionAddDoubleIdUnit(): void
    {
        $collection = new UnitCollection();

        // success
        $collection->add(
            new Unit(
                '5aa0d764-e92d-4137-beed-f7f590b08165',
                'User 1',
                'avatar 1',
                15,
                1,
                110,
                110,
                true,
                UnitClassFactory::create(1)
            )
        );

        $this->expectException(UnitException::class);

        // double id - exception
        $collection->add(
            new Unit(
                '5aa0d764-e92d-4137-beed-f7f590b08165',
                'User 2',
                'avatar 2',
                12,
                1,
                95,
                95,
                false,
                UnitClassFactory::create(2)
            )
        );
    }
}
