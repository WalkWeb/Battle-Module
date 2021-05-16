<?php

declare(strict_types=1);

namespace Tests\Battle\Result\FullLog;

use PHPUnit\Framework\TestCase;
use Battle\Result\FullLog\FullLog;

class FullLogTest extends TestCase
{
    public function testAdd(): void
    {
        $fullLog = new FullLog();
        $messages = ['message 1', 'message 2','message 3'];
        $fullLog->add($messages[0]);
        $fullLog->add($messages[1]);
        $fullLog->add($messages[2]);
        self::assertEquals($messages, $fullLog->getLog());
    }
}
