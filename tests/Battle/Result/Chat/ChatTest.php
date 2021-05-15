<?php

declare(strict_types=1);

namespace Tests\Battle\Result\Chat;

use PHPUnit\Framework\TestCase;
use Battle\Result\Chat\FullLog;

class ChatTest extends TestCase
{
    public function testAdd(): void
    {
        $chat = new FullLog();
        $messages = ['message 1', 'message 2','message 3'];
        $chat->add($messages[0]);
        $chat->add($messages[1]);
        $chat->add($messages[2]);
        self::assertEquals($messages, $chat->getLog());
    }
}
