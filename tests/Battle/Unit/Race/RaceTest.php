<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Race;

use Battle\Unit\Race\Race;
use Tests\AbstractUnitTest;

class RaceTest extends AbstractUnitTest
{
    public function testRaceCreate(): void
    {
        $id = 10;
        $name = 'Race Name';
        $singleName = 'Race Single Name';
        $color = '#008800';
        $icon = 'icon.png';

        $race = new Race($id, $name, $singleName, $color, $icon);

        self::assertEquals($id, $race->getId());
        self::assertEquals($name, $race->getName());
        self::assertEquals($singleName, $race->getSingleName());
        self::assertEquals($color, $race->getColor());
        self::assertEquals($icon, $race->getIcon());
    }
}
