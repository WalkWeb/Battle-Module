<?php

declare(strict_types=1);

namespace Tests\Battle\Effect\Change;

use Battle\Effect\Change\Change;
use Battle\Effect\EffectFactory;
use PHPUnit\Framework\TestCase;

class ChangeTest extends TestCase
{
    public function testCreate(): void
    {
        $data = EffectFactory::getAll()[2]['change_apply'][0];

        $change = new Change($data['type'], $data['increased'], $data['multiplier'], $data['power']);

        $this->assertEquals($data['type'], $change->getType());
        $this->assertEquals($data['increased'], $change->isIncreased());
        $this->assertEquals($data['multiplier'], $change->isMultiplier());
        $this->assertEquals($data['power'], $change->getPower());
    }
}
