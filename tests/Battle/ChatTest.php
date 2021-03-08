<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use Battle\Chat\Chat;

class ChatTest extends TestCase
{
    public function testAdd(): void
    {
        $chat = new Chat();
        $messages = ['message 1', 'message 2','message 3'];
        $chat->add($messages[0]);
        $chat->add($messages[1]);
        $chat->add($messages[2]);
        $this->assertEquals($messages, $chat->getAll());
    }
}
