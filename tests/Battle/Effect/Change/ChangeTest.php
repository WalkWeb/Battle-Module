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

        self::assertEquals($data['type'], $change->getType());
        self::assertEquals($data['increased'], $change->isIncreased());
        self::assertEquals($data['multiplier'], $change->isMultiplier());
        self::assertEquals($data['power'], $change->getPower());
    }
}
