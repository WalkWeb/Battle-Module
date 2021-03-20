<?php

declare(strict_types=1);

namespace Tests\Battle;

use Battle\Tools;
use PHPUnit\Framework\TestCase;
use Throwable;

class ToolsTest extends TestCase
{
    public function testRandomIntSuccess(): void
    {
        $min = 5;
        $max = 10;

        $int = Tools::rand($min, $max);

        self::assertTrue($int >= $min && $int <= $max);
    }
}
