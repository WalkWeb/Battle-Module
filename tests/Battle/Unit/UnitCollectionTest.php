<?php

declare(strict_types=1);

namespace Tests\Battle\Unit;

use Battle\Unit\UnitCollection;
use Battle\Unit\UnitException;
use Exception;
use Tests\AbstractUnitTest;
use Tests\Factory\UnitFactory;
use Tests\Factory\UnitFactoryException;

class UnitCollectionTest extends AbstractUnitTest
{
    /**
     * @throws UnitException
     * @throws Exception
     */
    public function testCreateUnitCollectionSuccess(): void
    {
        $collection = new UnitCollection();

        $collection->add(UnitFactory::createByTemplate(1));
        $collection->add(UnitFactory::createByTemplate(2));

        self::assertCount(2, $collection);

        foreach ($collection as $user) {
            self::assertTrue($user->isAlive());
            self::assertFalse($user->isAction());
        }
    }

    /**
     * @throws UnitException
     * @throws UnitFactoryException
     * @throws Exception
     */
    public function testUnitCollectionExistUnit(): void
    {
        $collection = new UnitCollection();

        $template = 1;
        $collection->add(UnitFactory::createByTemplate($template));
        $data = UnitFactory::getData($template);

        self::assertTrue($collection->exist($data['id']));
        self::assertFalse($collection->exist('undefined_id'));
    }

    /**
     * @throws UnitException
     * @throws Exception
     */
    public function testUnitCollectionAddDoubleIdUnit(): void
    {
        $collection = new UnitCollection();

        // success
        $collection->add(UnitFactory::createByTemplate(1));

        $this->expectException(UnitException::class);

        // double id - exception
        $collection->add(UnitFactory::createByTemplate(1));
    }

    /**
     * @throws UnitException
     * @throws UnitFactoryException
     * @throws Exception
     */
    public function testUnitCollectionKey(): void
    {
        $collection = new UnitCollection();
        $template = 1;
        $collection->add(UnitFactory::createByTemplate($template));
        $data = UnitFactory::getData($template);

        self::assertEquals($data['id'], $collection->key());
    }
}
